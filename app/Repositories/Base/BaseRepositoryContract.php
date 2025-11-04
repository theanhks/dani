<?php

namespace App\Repositories\Base;

interface BaseRepositoryContract
{
    /**
     * Append sort by id desc to query.
     *
     * @param bool $bool
     *
     * @return $this
     */
    public function appendIdSort($bool = true);

    /**
     * Sync relations.
     *
     * @param bool $detaching
     * @param mixed $id
     * @param mixed $relation
     * @param mixed $attributes
     */
    public function sync($id, $relation, $attributes, $detaching = true);

    /**
     * SyncWithoutDetaching.
     * @param mixed $id
     * @param mixed $relation
     * @param mixed $attributes
     */
    public function syncWithoutDetaching($id, $relation, $attributes);

    /**
     * Retrieve all data of repository with conditions, pagination|collection.
     *
     * @param array    $where          ['columnName' => 'equalValue'] or [['columnName', '>', 'gtValue']]
     * @param int|null $limit
     * @param array    $columns
     * @param bool     $paginate
     * @param ?string  $paginationType
     */
    public function search($where = [], $limit = null, $columns = ['*'], $paginate = true, $paginationType = null);

    /**
     * Set the columns to be selected.
     *
     * @param array|mixed $columns
     *
     * @return $this
     */
    public function selectColumns($columns = ['*']);

    /**
     * Add a "group by" clause to the query.
     *
     * @param array|string ...$groups
     *
     * @return $this
     */
    public function groupBy(...$groups);

    public function useIndex($indexes = []);

    public function havingRaw($raw, $bindings = []);

    /**
     * Apply query LIKE for specific columns.
     *
     * @param string|null $value
     * @param array|null  $columns
     * @param string      $operation
     *
     * @return $this
     */
    public function whereColumnsLike($value = null, array $columns = [], $operation = 'or');

    /**
     * Apply directly query LIKE for specific columns.
     *
     * @param string|null $value
     * @param string      $operation
     *
     * @return $this
     */
    public function applyWhereColumnsLike($value, array $columns = [], $operation = 'or');

    /**
     * Retrieve all data of repository.
     *
     * @param array $columns
     */
    public function all($columns = ['*']);

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
    public function lazyById($chunkSize = 1000, $column = null, $alias = null);

    /**
     * Retrieve all data of repository, paginated.
     *
     * @param null  $limit
     * @param array $columns
     */
    public function paginate($limit = null, $columns = ['*']);

    /**
     * Set the "limit" value of the query.
     *
     * @param int $limit
     *
     * @return $this
     */
    public function take($limit);

    /**
     * Retrieve data of repository with limit applied.
     *
     * @param int   $limit
     * @param array $columns
     */
    public function limit($limit, $columns = ['*']);

    /**
     * Retrieve all data of repository, simple paginated.
     *
     * @param null  $limit
     * @param array $columns
     */
    public function simplePaginate($limit = null, $columns = ['*']);

    /**
     * Find data by id.
     *
     * @param array $columns
     * @param mixed $id
     */
    public function find($id, $columns = ['*']);

    /**
     * Find data by id or throw exception.
     *
     * @param array $columns
     * @param mixed $id
     */
    public function findOrFail($id, $columns = ['*']);

    /**
     * Retrieve first data of repository.
     *
     * @param array $columns
     */
    public function first($columns = ['*']);

    /**
     * Receive first data by field and value.
     *
     * @param array $columns
     * @param mixed $field
     * @param null|mixed $value
     */
    public function firstByField($field, $value = null, $columns = ['*']);

    /**
     * Receive first data by multiple fields.
     *
     * @param array $columns
     */
    public function firstWhere(array $where, $columns = ['*']);

    /**
     * Find data by field and value.
     *
     * @param string $field
     * @param array  $columns
     * @param mixed $value
     */
    public function findByField($field, $value, $columns = ['*']);

    /**
     * Find data by multiple fields.
     *
     * @param array $columns
     */
    public function findWhere(array $where, $columns = ['*']);

    /**
     * Find data by multiple values in one field.
     *
     * @param array $columns
     * @param mixed $field
     */
    public function findWhereIn($field, array $values, $columns = ['*']);

    /**
     * Find data by excluding multiple values in one field.
     *
     * @param array $columns
     * @param mixed $field
     */
    public function findWhereNotIn($field, array $values, $columns = ['*']);

    /**
     * Find data by between values in one field.
     *
     * @param array $columns
     * @param mixed $field
     */
    public function findWhereBetween($field, array $values, $columns = ['*']);

    /**
     * Model will action without firing any model events for any model type.
     *
     * @return void
     */
    public function withoutDispatch();

    /**
     * Save a new entity in repository.
     */
    public function create(array $attributes);

    /**
     * Update a entity in repository by id.
     * @param mixed $id
     */
    public function update($id, array $attributes);

    /**
     * Update a entity in repository by id  quietly.
     * @param mixed $id
     */
    public function updateQuietly(array $attributes, $id);

    /**
     * Update or Create an entity in repository.
     */
    public function updateOrCreate(array $attributes, array $values = []);

    /**
     * Delete a entity in repository by id.
     *
     * @return int
     * @param mixed $id
     */
    public function delete($id);

    /**
     * Delete multiple entities by given condition.
     *
     * @return int
     */
    public function deleteWhere(array $where);

    /**
     * Restore a entity in repository by id.
     *
     * @param Model|mixed $id
     */
    public function restore($id);

    /**
     * Order collection by a given column.
     *
     * @param string $column
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy($column, $direction = 'asc');

    /**
     * Load relations.
     *
     * @return $this
     * @param mixed $relations
     */
    public function with($relations);

    /**
     * Load with trashed.
     *
     * @param bool $withTrashed
     *
     * @return $this
     */
    public function withTrashed($withTrashed = true);

    /**
     * Load relation with closure.
     *
     * @param string  $relation
     * @param closure $closure
     *
     * @return $this
     */
    public function whereHas($relation, $closure);

    /**
     * Count results of repository.
     *
     * @param string $columns
     *
     * @return int
     */
    public function count(array $where = [], $columns = '*');

    /**
     * Check if exists results of repository.
     *
     * @return bool
     */
    public function exists(array $where = []);

    /**
     * Add subselect queries to count the relations.
     *
     * @return $this
     * @param mixed $relations
     */
    public function withCount($relations);

    /**
     * Set hidden fields.
     *
     * @return $this
     */
    public function hidden(array $fields);

    /**
     * Set visible fields.
     *
     * @return $this
     */
    public function visible(array $fields);

    /**
     * Add Query Scope.
     *
     * @return $this
     */
    public function scopeQuery(\Closure $scope);

    /**
     * Reset Query Scope.
     *
     * @return $this
     */
    public function resetScopeQuery();

    /**
     * Reset Query Scope.
     *
     * @return $this
     */
    public function resetScope();

    /**
     * Reset The Repository.
     *
     * @return $this
     */
    public function resetRepository();

    /**
     * add sort to the repository.
     *
     * @param string $sortBy
     * @param mixed $orderby
     *
     * @return $this
     */
    public function addSort($orderby, $sortBy = 'desc');

    /**
     * Apply eloquent model scope in current Query.
     *
     * @return $this
     * @param mixed $scopes
     */
    public function modelScopes($scopes);

    /**
     * Passing params to model scopes in current Query
     * Only accept scope with no params passing.
     *
     * @return $this
     *
     * @example : $scopes = ['scopeName' => 'paramsOfScope']
     * @param mixed $scopeParams
     */
    public function modelScopeParams($scopeParams);

    /**
     * Get Searchable Fields.
     *
     * @return array
     */
    public function getFieldsSearchable();

    /**
     * Returns the current Model instance.
     *
     * @return Model
     */
    public function getModel();

    /**
     * Retrieve first data of repository, or return new Entity.
     */
    public function firstOrNew(array $attributes = [], array $values = []);

    /**
     * Retrieve first data of repository, or create new Entity.
     */
    public function firstOrCreate(array $attributes = [], array $values = []);

    /**
     * Lock the table where conditions.
     *
     * @param array $where
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function lockForUpdateByCondition($where);

    public function dd();

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function getQuery();

    public function distinct($columns = []);

    /**
     * Trigger static method calls to the model.
     * @param mixed $method
     * @param mixed $arguments
     */
    public static function __callStatic($method, $arguments);

    /**
     * Trigger method calls to the model.
     *
     * @param string $method
     * @param array  $arguments
     */
    public function __call($method, $arguments);

    /**
 * Update multiple records with condition.
 *
 * @param array $where
 * @param array $attributes
 * @return int
 */
    public function updateWhere(array $where, array $attributes);

    /**
     * @param mixed $limit
     * @param mixed $columns
     * @return void
     */
    public function getPaginate($limit = null, $columns = ['*']);
}
