<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class BaseService
{
    public $repository;

    public ?array $columns = ['*'];

    public $enableTransactionLock = false;

    public $enableCreateIfNotFoundOption = false;

    public $enableThrowErrorIfNotFound = false;

    public $onlyActiveItems = false;

    public $shouldReturnQueryBuilder = false;

    public $requestId;

    public function enableTransactionLock($state = true)
    {
        $this->enableTransactionLock = $state;
        return $this;
    }

    public function enableCreateIfNotFound($state = true)
    {
        $this->enableCreateIfNotFoundOption = $state;
        return $this;
    }

    public function throwErrorIfNotFound($state = true)
    {
        $this->enableThrowErrorIfNotFound = $state;
        return $this;
    }

    public function shouldReturnQueryBuilder($state = true)
    {
        $this->shouldReturnQueryBuilder = $state;
        return $this;
    }

    public function onlyActiveItems($state = true)
    {
        $this->onlyActiveItems = $state;
        return $this;
    }

    public function resetVariables()
    {
        $this->enableTransactionLock = false;
        $this->enableCreateIfNotFoundOption = false;
        $this->enableThrowErrorIfNotFound = false;
        $this->onlyActiveItems = false;
        $this->shouldReturnQueryBuilder = false;
    }

    public function lock(\Closure $callback, $key, $seconds = 30, $maximumWait = 15, $owner = null)
    {
        $lock = Cache::lock($key, $seconds, $owner);
        try {
            return $lock->block($maximumWait, $callback);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            optional($lock)->release();
        }
    }

    public function setRequestId($id)
    {
        $this->requestId = $id;
        return $this;
    }

    public function needColumns(...$columns)
    {
        if (empty($columns)) {
            $columns = ['*'];
        }

        $this->columns = $columns;

        return $this;
    }

    public function generateLockKey($key = null)
    {
        return is_array($key) ? implode('-', $key) : $key;
    }

    public function findByField($field, $value)
    {
        return $this->repository->firstByField($field, $value);
    }

    public function all($with = [], $scopes = [])
    {
        return $this->repository
            ->with($with)
            ->modelScopes(array_filter($scopes))
            ->all($this->columns);
    }

    public function show($id, $withRelations = [])
    {
        return $this->repository->with($withRelations)->findOrFail($id);
    }

    public function create($attributes = [])
    {
        return $this->repository->create($attributes);
    }

    public function update($id, $attributes = [])
    {
        return $this->repository->update($id, $attributes);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }

    public function firstOrCreate($attributes = [], $values = [])
    {
        return $this->repository->firstOrCreate($attributes, $values);
    }

    public function firstWhere($conditions)
    {
        return $this->repository->firstWhere($conditions, $this->columns);
    }

    public function find($id, $withRelations = [])
    {
        return $this->repository->with($withRelations)->find($id);
    }

    public function modelOrParam($modelOrParam, $model)
    {
        //check model or Id or Uuid
        if ($modelOrParam instanceof Model or $modelOrParam instanceof $model) {
            return $modelOrParam;
        } elseif (\is_int($modelOrParam)) {
            return $model->find($modelOrParam);
        } else {
            return $model->firstByField('uuid', $modelOrParam);
        }
    }

    public function firstWhereAndLockForUpdate($data)
    {
        return $this->repository
            ->lockForUpdate()
            ->firstWhere($data);
    }

    /**
     * Trigger method calls to the model.
     *
     * @param string $method
     * @param array  $arguments
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->repository, $method], $arguments);
    }

    /**
     * @param array $parameters
     * @return static
     * @throws BindingResolutionException
     */
    public static function make($parameters = [])
    {
        return app(static::class, $parameters);
    }

    public function getPaginate(): LengthAwarePaginator
    {
        return $this->repository->getPaginate();
    }

    protected function extractSortParams(array $data, array $allowedSorts, string $defaultSortBy = 'id', string $defaultOrderBy = 'desc'): array
    {
        $sortBy = data_get($data, 'sort_by', $defaultSortBy);
        $orderBy = strtolower(data_get($data, 'order_by', $defaultOrderBy));

        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : $defaultSortBy;
        $orderBy = in_array($orderBy, ['asc', 'desc']) ? $orderBy : $defaultOrderBy;

        return [$sortBy, $orderBy];
    }
}
