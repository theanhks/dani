<?php

namespace App\Repositories\Base;

use App\Exceptions\EntityNotFoundException;
use App\Exceptions\RepositoryException;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\NullDispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class BaseRepository implements BaseRepositoryContract
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $fieldSearchable = [];

    /**
     * @var \Closure
     */
    protected $scopeQuery;

    protected $modelScopes = [];

    protected $modelScopeParams = [];

    protected $queryingColumnsLike = [];

    protected $sorts = [];

    protected $scopeQueries = [];

    protected $appendIdSort = false;

    public function __construct()
    {
        $this->makeModel();
        $this->boot();
    }

    public function boot()
    {
    }

    /**
     * Returns the current Model instance.
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return Model
     */
    public function getNewModel()
    {
        return app($this->model());
    }

    /**
     * @throws RepositoryException
     */
    public function resetModel()
    {
        $this->makeModel();
    }

    /**
     * Specify Model class name.
     *
     * @return string
     */
    abstract public function model();

    /**
     * @return Model
     *
     * @throws RepositoryException
     */
    public function makeModel()
    {
        $model = app($this->model());

        if (! $model instanceof Model) {
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }
        //All queries called from the repository will be called directly without going through the cache
        if (method_exists($model, 'scopeDisableCache')) {
            $model = $model->disableCache();
        }

        return $this->model = $model;
    }

    /**
     * Get Searchable Fields.
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Add Query Scope.
     *
     * @return $this
     */
    public function scopeQuery(\Closure $scope)
    {
        $this->scopeQueries = array_merge($this->scopeQueries, Arr::wrap($scope));

        return $this;
    }

    public function resetScopeQuery()
    {
        $this->scopeQueries = [];

        return $this;
    }

    public function appendIdSort($bool = true)
    {
        $this->appendIdSort = $bool;

        return $this;
    }

    /**
     * Sync relations.
     *
     * @param bool $detaching
     * @param mixed $id
     * @param mixed $relation
     * @param mixed $attributes
     */
    public function sync($id, $relation, $attributes, $detaching = true)
    {
        return $this->findOrFail($id)->{$relation}()->sync($attributes, $detaching);
    }

    /**
     * SyncWithoutDetaching.
     * @param mixed $id
     * @param mixed $relation
     * @param mixed $attributes
     */
    public function syncWithoutDetaching($id, $relation, $attributes)
    {
        return $this->sync($id, $relation, $attributes, false);
    }

    /**
     * Retrieve all data of repository.
     *
     * @param array    $where          ['columnName' => 'equalValue'] or [['columnName', '>', 'gtValue']]
     * @param int|null $limit
     * @param array    $columns
     * @param bool     $paginate
     * @param ?string  $paginationType
     */
    public function search($where = [], $limit = null, $columns = ['*'], $paginate = true, $paginationType = null)
    {
        $this->applyScope();

        $this->applyConditions($where);

        $this->applySorting();

        $model = $this->model;
        $limit = $this->getPaginateLimit($limit);

        if (! in_array($paginationType, ['paginate', 'simplePaginate', 'cursorPaginate'])) {
            $paginationType = 'paginate';
        }

        $result = $paginate ? $model->{$paginationType ?? 'paginate'}($limit, $columns) : $model->get($columns);

        return $this->parserResult($result);
    }

    /**
     * Set the columns to be selected.
     *
     * @param array|mixed $columns
     *
     * @return $this
     */
    public function selectColumns($columns = ['*'])
    {
        $this->model = $this->model->select($columns);

        return $this;
    }

    /**
     * Add a "group by" clause to the query.
     *
     * @param array|string ...$groups
     *
     * @return $this
     */
    public function groupBy(...$groups)
    {
        $this->model = $this->model->groupBy(...$groups);

        return $this;
    }

    public function useIndex($indexes = [])
    {
        $indexes = implode(',', $indexes);

        if ($this->model instanceof \Illuminate\Database\Eloquent\Builder) {
            $this->model = $this->model->from(DB::raw("`{$this->getNewModel()->getTable()}` USE INDEX($indexes)"));
        } else {
            $this->model = $this->model->setTable(DB::raw("`{$this->getNewModel()->getTable()}` USE INDEX($indexes)"));
        }

        return $this;
    }

    public function havingRaw($raw, $bindings = [])
    {
        $this->model = $this->model->havingRaw($raw, $bindings);

        return $this;
    }

    private function guessPaginateLimitFromRequest()
    {
        return request()->get('per_page', config('core.paginate.limit', 20));
    }

    private function guessSortingFromRequest()
    {
        return ['order_by' => request()->get('order_by', 'id'), 'sort_by' => request()->get('sort_by', 'desc')];
    }

    /**
     * Apply query LIKE for specific columns.
     *
     * @param string|null $value
     * @param string      $operation
     *
     * @return $this
     */
    public function whereColumnsLike($value = null, array $columns = [], $operation = 'or')
    {
        if (! $value || empty($columns)) {
            return $this;
        }

        if (! Str::contains($value, '%')) {
            $value = "%$value%";
        }

        $this->queryingColumnsLike[] = compact('value', 'columns', 'operation');

        return $this;
    }

    /**
     * Retrieve all data of repository.
     *
     * @param array $columns
     */
    public function all($columns = ['*'])
    {
        $this->applyScope();

        $this->applySorting();

        if (empty($columns)) {
            $columns = ['*'];
        }

        if ($this->model instanceof Builder) {
            $results = $this->model->get($columns);
        } else {
            $results = $this->model->all($columns);
        }

        return $this->parserResult($results);
    }

    /**
     * Query lazily, by chunking the results of a query by comparing IDs.
     *
     * @param int         $chunkSize
     * @param string|null $column
     * @param string|null $alias
     *
     * @return \Illuminate\Support\LazyCollection
     *
     * @throws \InvalidArgumentException
     */
    public function lazyById($chunkSize = 1000, $column = null, $alias = null)
    {
        $this->applyScope();

        $this->applySorting();

        $results = $this->model->lazyById($chunkSize, $column, $alias);

        return $this->parserResult($results);
    }

    /**
     * Count results of repository.
     *
     * @param string $columns
     *
     * @return int
     */
    public function count(array $where = [], $columns = '*')
    {
        $this->applyScope();

        if ($where) {
            $this->applyConditions($where);
        }

        $result = $this->model->count($columns);

        $this->resetRepository();

        return $result;
    }

    /**
     * Check if exists results of repository.
     *
     * @return bool
     */
    public function exists(array $where = [])
    {
        $this->applyScope();

        if ($where) {
            $this->applyConditions($where);
        }

        $result = $this->model->exists();

        $this->resetRepository();

        return $result;
    }

    /**
     * Alias of All method.
     *
     * @param array $columns
     */
    public function get($columns = ['*'])
    {
        return $this->all($columns);
    }

    /**
     * Retrieve first data of repository.
     *
     * @param array $columns
     */
    public function first($columns = ['*'])
    {
        $this->applyScope();

        $this->applySorting();

        $results = $this->model->first($columns);

        return $this->parserResult($results);
    }

    /**
     * Retrieve first data of repository, or return new Entity.
     */
    public function firstOrNew(array $attributes = [], array $values = [])
    {
        $this->applyScope();

        $this->applySorting();

        $model = $this->model->firstOrNew($attributes, $values);

        return $this->parserResult($model);
    }

    /**
     * Retrieve first data of repository, or create new Entity.
     */
    public function firstOrCreate(array $attributes = [], array $values = [])
    {
        $this->applyScope();

        $this->applySorting();

        $model = $this->model->firstOrCreate($attributes, $values);

        return $this->parserResult($model);
    }

    /**
     * Retrieve data of repository with limit applied.
     *
     * @param int   $limit
     * @param array $columns
     */
    public function limit($limit, $columns = ['*'])
    {
        $limit = $this->getPaginateLimit($limit);

        // Shortcut to all with `limit` applied on query via `take`
        $this->take($limit);

        return $this->all($columns);
    }

    /**
     * Retrieve all data of repository, paginated.
     *
     * @param int|null $limit
     * @param array    $columns
     */
    public function paginate($limit = null, $columns = ['*'])
    {
        $this->applyScope();
        $limit = $this->getPaginateLimit($limit);

        $results = $this->model->paginate($limit, $columns);
        $results->appends(app('request')->query());

        $this->resetRepository();

        return $this->parserResult($results);
    }

    /**
     * Retrieve all data of repository, simple paginated.
     *
     * @param int|null $limit
     * @param array    $columns
     */
    public function simplePaginate($limit = null, $columns = ['*'])
    {
        $limit = $this->getPaginateLimit($limit);

        return $this->paginate($limit, $columns, 'simplePaginate');
    }

    /**
     * Find data by id.
     *
     * @param array $columns
     * @param mixed $id
     */
    public function find($id, $columns = ['*'])
    {
        if ($id === null) {
            return null;
        }

        $model = $id;

        if (! $id instanceof Model) {
            $model = $this->model->find($id, $columns);
        }

        return $model;
    }

    /**
     * Find data by id or throw exception.
     *
     * @param array $columns
     * @param mixed $id
     */
    public function findOrFail($id, $columns = ['*'])
    {
        $this->applyScope();

        $model = $id;

        if (! $id instanceof Model) {
            $model = $this->model->find($id, $columns);
        }

        if (! $model) {
            throw new EntityNotFoundException('Invalid ' . class_basename($this->model()));
        }

        $this->resetRepository();

        return $model;
    }

    /**
     * Receive first data by field and value.
     *
     * @param array $columns
     * @param mixed $field
     * @param null|mixed $value
     */
    public function firstByField($field, $value = null, $columns = ['*'])
    {
        $this->applyScope();

        $model = $this->model->where($field, '=', $value)->first($columns);

        return $this->parserResult($model);
    }

    /**
     * Receive first data by multiple fields.
     *
     * @param array $columns
     */
    public function firstWhere(array $where, $columns = ['*'])
    {
        $this->applyScope();

        $this->applyConditions($where);

        $model = $this->model->first($columns);

        return $this->parserResult($model);
    }

    /**
     * Find data by field and value.
     *
     * @param array $columns
     * @param mixed $field
     * @param null|mixed $value
     */
    public function findByField($field, $value = null, $columns = ['*'])
    {
        $this->applyScope();

        $model = $this->model->where($field, '=', $value)->get($columns);

        $this->resetRepository();

        return $this->parserResult($model);
    }

    /**
     * Find data by multiple fields.
     *
     * @param array $columns
     */
    public function findWhere(array $where, $columns = ['*'])
    {
        $this->applyScope();

        $this->applyConditions($where);

        $model = $this->model->get($columns);

        $this->resetRepository();

        return $this->parserResult($model);
    }

    /**
     * Find data by multiple values in one field.
     *
     * @param array $columns
     * @param mixed $field
     */
    public function findWhereIn($field, array $values, $columns = ['*'])
    {
        $this->applyScope();

        $model = $this->model->whereIn($field, $values)->get($columns);

        $this->resetRepository();

        return $this->parserResult($model);
    }

    /**
     * Find data by excluding multiple values in one field.
     *
     * @param array $columns
     * @param mixed $field
     */
    public function findWhereNotIn($field, array $values, $columns = ['*'])
    {
        $this->applyScope();

        $model = $this->model->whereNotIn($field, $values)->get($columns);

        $this->resetRepository();

        return $this->parserResult($model);
    }

    /**
     * Find data by between values in one field.
     *
     * @param array $columns
     * @param mixed $field
     */
    public function findWhereBetween($field, array $values, $columns = ['*'])
    {
        $this->applyScope();

        $model = $this->model->whereBetween($field, $values)->get($columns);

        $this->resetRepository();

        return $this->parserResult($model);
    }

    /**
     * Model will action without firing any model events for any model type.
     *
     * @return void
     */
    public function withoutDispatch()
    {
        $dispatcher = $this->model::getEventDispatcher();

        if ($dispatcher) {
            $this->model::setEventDispatcher(new NullDispatcher($dispatcher));
        }

        return;
    }

    /**
     * Save a new entity in repository.
     */
    public function create(array $attributes)
    {
        $model = $this->model->newInstance($attributes);

        $model->save();

        $this->resetRepository();


        return $this->parserResult($model);
    }

    /**
     * Update a entity in repository by id.
     *
     * @param Model|mixed $id
     */
    public function update($id, array $attributes)
    {
        $this->applyScope();

        $model = $this->findOrFail($id);

        $model->fill($attributes);
        $model->save();

        $this->resetRepository();

        return $this->parserResult($model);
    }

    /**
     * Update a entity in repository by id quietly.
     *
     * @param Model|mixed $id
     */
    public function updateQuietly(array $attributes, $id)
    {
        $this->applyScope();

        $model = $this->findOrFail($id);

        $model->fill($attributes);
        $model->saveQuietly();

        $this->resetRepository();

        return $this->parserResult($model);
    }

    /**
     * Update or Create an entity in repository.
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        $this->applyScope();

        $model = $this->model->updateOrCreate($attributes, $values);

        $this->resetRepository();


        return $this->parserResult($model);
    }

    /**
     * Delete a entity in repository by id.
     *
     * @return int
     * @param mixed $id
     */
    public function delete($id)
    {
        $this->applyScope();

        $model = $this->findOrFail($id);

        $this->resetRepository();

        $deleted = $model->delete();

        return $deleted;
    }

    /**
     * Restore a entity in repository by id.
     *
     * @param Model|mixed $id
     */
    public function restore($id)
    {
        $this->applyScope();

        $model = $this->withTrashed()->findOrFail($id);

        $model->restore();

        $this->resetRepository();

        return $this->parserResult($model);
    }

    /**
     * Delete multiple entities by given condition.
     *
     * @return int
     */
    public function deleteWhere(array $where)
    {
        $this->applyScope();

        $this->applyConditions($where);

        $deleted = $this->model->delete();

        $this->resetRepository();

        return $deleted;
    }

    /**
     * Check if entity has relation.
     *
     * @param string $relation
     *
     * @return $this
     */
    public function has($relation)
    {
        $this->model = $this->model->has($relation);

        return $this;
    }

    /**
     * Load relations.
     *
     * @param array|string $relations
     *
     * @return $this
     */
    public function with($relations)
    {
        $this->model = $this->model->with($relations);

        return $this;
    }

    /**
     * Load with trashed.
     *
     * @param bool $withTrashed
     *
     * @return $this
     */
    public function withTrashed($withTrashed = true)
    {
        $this->model = $this->model->withTrashed($withTrashed);

        return $this;
    }

    /**
     * Add subselect queries to count the relations.
     *
     * @return $this
     * @param mixed $relations
     */
    public function withCount($relations)
    {
        $this->model = $this->model->withCount($relations);

        return $this;
    }

    /**
     * Load relation with closure.
     *
     * @param string   $relation
     * @param \closure $closure
     *
     * @return $this
     */
    public function whereHas($relation, $closure)
    {
        $this->model = $this->model->whereHas($relation, $closure);

        return $this;
    }

    /**
     * Set hidden fields.
     *
     * @return $this
     */
    public function hidden(array $fields)
    {
        $this->model->setHidden($fields);

        return $this;
    }

    /**
     * Set the "orderBy" value of the query.
     *
     * @param string $direction
     * @param mixed $column
     *
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->model = $this->model->orderBy($column, $direction);

        return $this;
    }

    /**
     * Set the "limit" value of the query.
     *
     * @param int $limit
     *
     * @return $this
     */
    public function take($limit)
    {
        // Internally `take` is an alias to `limit`
        $this->model = $this->model->limit($limit);

        return $this;
    }

    /**
     * Set visible fields.
     *
     * @return $this
     */
    public function visible(array $fields)
    {
        $this->model->setVisible($fields);

        return $this;
    }

    /**
     * Reset Query Scope.
     *
     * @return $this
     */
    public function resetScope()
    {
        $this->scopeQuery = null;
        $this->modelScopes = [];
        $this->modelScopeParams = [];

        return $this;
    }

    /**
     * Apply scope in current Query.
     *
     * @return $this
     */
    protected function applyScope()
    {
        if (method_exists($this, 'addWorkspaceScope')) {
            $this->addWorkspaceScope();
        }

        if (! empty($this->scopeQueries)) {
            foreach ($this->scopeQueries as $scope) {
                $this->applyScopeQueryClosure($scope);
            }
        }

        if (! empty($this->queryingColumnsLike)) {
            foreach ($this->queryingColumnsLike as $querying) {
                $this->applyWhereColumnsLike(
                    data_get($querying, 'value'),
                    data_get($querying, 'columns'),
                    data_get($querying, 'operation')
                );
            }
            $this->queryingColumnsLike = [];
        }

        foreach ($this->modelScopes as $modelScope) {
            if ($this->modelScopeParams && isset($this->modelScopeParams[$modelScope])) {
                $this->model = $this->model->{$modelScope}($this->modelScopeParams[$modelScope]);
            } else {
                $this->model = $this->model->{$modelScope}();
            }
        }

        $this->resetScope();

        return $this;
    }

    protected function applyScopeQueryClosure(\Closure $scope)
    {
        $callback = $scope;
        $callback($query = $this->getNewModel()->newQueryWithoutRelationships());
        $this->model = $this->model->addNestedWhereQuery($query->getQuery());
    }

    public function applyWhereColumnsLike($value, array $columns = [], $operation = 'or')
    {
        $this->model = $this->model->where(
            function ($query) use ($columns, $value, $operation) {
                foreach ($columns as $column) {
                    if (Str::contains($column, '.')) {
                        $querying = $operation == 'or' ? 'orWhereHas' : 'whereHas';

                        $relation = Str::beforeLast($column, '.');

                        $relationColumn = Str::afterLast($column, '.');

                        $query->{$querying}(
                            $relation,
                            function ($q) use ($relationColumn, $value) {
                                $q->where($relationColumn, 'LIKE', $value);
                            }
                        );
                    } else {
                        $query->where($column, 'LIKE', $value, $operation);
                    }
                }
            }
        );
    }

    /**
     * Apply eloquent model scopes in current Query
     * Only accept scope with no params passing.
     *
     * @return $this
     * @param mixed $scopes
     */
    public function modelScopes($scopes)
    {
        $this->modelScopes = array_merge($this->modelScopes ?? [], is_array($scopes) ? $scopes : func_get_args());

        return $this;
    }

    /**
     * Passing params to model scopes in current Query
     * Only accept scope with no params passing.
     *
     * @return $this
     *
     * @example : $scopes = ['scopeName' => 'paramsOfScope']
     * @param mixed $scopeParams
     */
    public function modelScopeParams($scopeParams)
    {
        $this->modelScopeParams = array_merge($this->modelScopeParams ?? [], $scopeParams);

        return $this;
    }

    /**
     * Applies the given where conditions to the model.
     *
     * @return void
     */
    protected function applyConditions(array $where)
    {
        foreach ($where as $field => $value) {
            if (is_array($value)) {
                list($field, $condition, $val) = $value;
                $this->model = $this->model->where($field, $condition, $val);
            } else {
                $this->model = $this->model->where($field, '=', $value);
            }
        }
    }

    /**
     * Wrapper result data.
     * @param mixed $result
     */
    public function parserResult($result)
    {
        $this->resetRepository();

        return $result;
    }

    /**
     * Lock the table where conditions.
     *
     * @param array $where
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function lockForUpdateByCondition($where)
    {
        $this->applyScope();

        $this->applyConditions($where);

        $this->applySorting();

        $result = $this->model->lockForUpdate()->get();

        $this->resetRepository();

        return $result;
    }

    public function resetRepository()
    {
        $this->beforeResetRepository();

        $this->resetModel();
        $this->resetScope();
        $this->resetScopeQuery();

        $this->queryingColumnsLike = [];
        $this->sorts = [];
    }

    public function beforeResetRepository()
    {
        // code
    }

    /**
     * add sort to the repository.
     *
     * @param string $sortBy
     * @param mixed $orderby
     *
     * @return $this
     */
    public function addSort($orderby, $sortBy = 'desc')
    {
        $this->sorts[] = ['order_by' => $orderby, 'sort_by' => $sortBy];

        return $this;
    }

    protected function applySorting()
    {
        if (empty($this->sorts)) {
            $guessedSorting = $this->guessSortingFromRequest();

            $this->sorts[] = ['order_by' => $guessedSorting['order_by'], 'sort_by' => $guessedSorting['sort_by']];
        }

        if ($this->appendIdSort && ! in_array('id', Arr::pluck($this->sorts, 'order_by'))) {
            $this->sorts[] = ['order_by' => 'id', 'sort_by' => 'desc'];
        }

        foreach ($this->sorts as $sort) {
            $sortBy = $sort['sort_by'];
            $this->sorting($sort['order_by'], ! in_array($sortBy, ['asc', 'desc']) ? 'asc' : $sortBy);
        }

        return;
    }

    protected function sorting($orderBy, $sortBy)
    {
        if (Str::contains($orderBy, '.')) {
            list($relation, $orderBy) = explode('.', $orderBy);

            $relation = Str::camel($relation);

            if ($this->model instanceof \Illuminate\Database\Eloquent\Builder) {
                $relationQuery = $this->model->getModel()->$relation();
            } else {
                $relationQuery = $this->model->$relation();
            }

            $relationTable = $relationQuery->getRelated()->getTable();
            $relationTableKeyName = $relationQuery->getRelated()->getKeyName();
            $newRelationQuery = $relationQuery->getRelated()->newQuery();

            $this->model = $this->model
                ->orderBy(
                    $newRelationQuery->select($orderBy)
                        ->whereColumn(
                            "$relationTable.$relationTableKeyName",
                            "{$relationQuery->getChild()->getTable()}.{$relationQuery->getForeignKeyName()}"
                        ),
                    $sortBy
                );

            return;
        }

        $this->model = $this->model->orderBy($orderBy, $sortBy);
    }

    protected function getPaginateLimit($limit)
    {
        $limit = $limit ?? $this->guessPaginateLimitFromRequest();
        $maxLimit = config('app.paginate.max', 100);

        return $limit > $maxLimit ? $maxLimit : $limit;
    }

    public function dd()
    {
        $this->applyScope();

        $this->applySorting();

        return $this->model->dd();
    }

    public function getQuery()
    {
        $this->applyScope();

        $this->applySorting();

        $query = $this->model->getQuery();

        $this->resetRepository();

        return $query;
    }

    public function distinct($columns = [])
    {
        // Internally `take` is an alias to `limit`
        $this->model = $this->model->distinct($columns);

        return $this;
    }

    /**
     * Trigger static method calls to the repository.
     * @param mixed $method
     * @param mixed $arguments
     */
    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array([new static(), $method], $arguments);
    }

    /**
     * Trigger method calls to the model.
     *
     * @param string $method
     * @param array  $arguments
     */
    public function __call($method, $arguments)
    {
        $this->applyScope();
        return call_user_func_array([$this->model, $method], $arguments);
    }
    /**
     * @param array $where
     * @param array $attributes
     * @return bool
     */
    public function updateWhere(array $where, array $attributes): int
    {
        $this->applyScope();
        $this->applyConditions($where);

        $updated = $this->model->update($attributes);

        $this->resetRepository();

        return $updated;
    }

    public function getPaginate($limit = null, $columns = ['*'])
    {
        $this->applyScope();
        $this->applySorting();
        $limit = $this->getPaginateLimit($limit);
        $results = $this->model->paginate($limit, $columns);
        $results->appends(request()->query());
        $this->resetRepository();
        return $this->parserResult($results);
    }
}
