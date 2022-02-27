<?php

namespace Arkye\Repository;

use Arkye\Repository\Exceptions\EntityAttributeNotSetException;
use Arkye\Repository\Exceptions\InvalidModelException;
use Arkye\Repository\Attributes\EntityAttribute;
use Arkye\Repository\Attributes\ModelAttribute;
use Arkye\Repository\Exceptions\ModelAttributeNotSetException;
use Arkye\Repository\Interfaces\IEntityConvertible;
use Arkye\Repository\Interfaces\IEntityRepository;
use BadMethodCallException;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;

class EloquentEntityRepository implements IEntityRepository
{

  private string $entityClass;
  private string $modelClass;

  public function __construct()
  {
    $this->boot();
  }

  protected function boot()
  {
    $reflectionClass = new ReflectionClass($this);

    $entityAttribute = $reflectionClass
        ->getAttributes(EntityAttribute::class)[0] ?? null;

    if (null === $entityAttribute) {
      throw new EntityAttributeNotSetException(static::class);
    }

    $modelAttribute = $reflectionClass
        ->getAttributes(ModelAttribute::class)[0] ?? null;

    if (null === $modelAttribute) {
      throw new ModelAttributeNotSetException(static::class);
    }

    $this->entityClass = $entityAttribute->newInstance()->getClassName();
    $this->modelClass = $modelAttribute->newInstance()->getClassName();
  }

  /**
   * @param IEntityConvertible|null $model
   * @return object
   */
  public function newEntity(IEntityConvertible $model = null): object
  {
    $entity = new $this->entityClass;

    return ($model !== null)
      ? $model->toEntity($entity)
      : $entity;
  }

  /**
   * @param object|null $entity
   * @return Model
   */
  public function newModel(object $entity = null): Model
  {
    $model = new $this->modelClass;

    if ($entity !== null) {
      return $model->fromEntity($entity);
    }

    return $model;
  }

  /**
   * @param array|string $relations
   * @return Builder
   */
  public function newQuery(array|string $relations = []): Builder
  {
    return $this
      ->modelClass::query()
      ->with(is_array($relations) ? $relations : array_map('trim', explode(',', $relations)));
  }

  /**
   * @inheritDoc
   */
  public function find($id, array|string $relations = []): ?object
  {
    $model = $this
      ->newQuery($relations)
      ->find($id);

    if (null === $model) {
      return null;
    }

    if ($model instanceof Collection) {
      return $model->map(function($model) {
        if (!$model instanceof IEntityConvertible) {
          throw new InvalidModelException($model::class);
        }

        return $model->toEntity();
      });
    }

    if (!$model instanceof IEntityConvertible) {
      throw new InvalidModelException($model::class);
    }

    return $model->toEntity();
  }

  /**
   * @inheritDoc
   */
  public function findAll(array|string $relations = []): Collection
  {
    return $this
      ->newQuery($relations)
      ->get()
      ->map(fn($model) => $model->toEntity());
  }

  /**
   * @inheritDoc
   */
  public function findBy(array $criteria, array|string $relations = [], ?array $orderBy = [], int $limit = null, int $offset = null): Collection
  {
    $qb = $this->newQuery($relations);

    foreach ($criteria as $attribute => $value)
    {
      $qb = $qb->where($attribute, $value);
    }

    foreach ($orderBy as $attribute => $direction)
    {
      $qb = $qb->orderBy($attribute, $direction);
    }

    if ($limit !== null) {
      $qb = $qb->limit($limit);
    }

    if ($offset !== null) {
      $qb = $qb->offset($offset);
    }

    return $qb
      ->get()
      ->map(fn($model) => $model->toEntity(null));
  }

  /**
   * @inheritDoc
   */
  public function findOneBy(array $criteria, array|string $relations = [], array $orderBy = []): ?object
  {
    return $this
      ->findBy($criteria, $relations, $orderBy, 1)
      ->first();
  }

  /**
   * Adds support for magic method calls.
   *
   * @param string $method
   * @param array $arguments
   *
   * @return mixed The returned value from the resolved method.
   *
   * @throws BadMethodCallException|Exception If the method called is invalid
   */
  public function __call(string $method, array $arguments)
  {
    if (str_starts_with($method, 'findBy')) {
      return $this->resolveMagicCall('findBy', substr($method, 6), $arguments);
    }

    if (str_starts_with($method, 'findOneBy')) {
      return $this->resolveMagicCall('findOneBy', substr($method, 9), $arguments);
    }

    if (str_starts_with($method, 'countBy')) {
      return $this->resolveMagicCall('count', substr($method, 7), $arguments);
    }

    throw new BadMethodCallException("Undefined method '$method'. The method name must start with either findBy, findOneBy or countBy!");
  }

  /**
   * Resolves a magic method call to the proper existent method at `EntityRepository`.
   *
   * @param string $method    The method to call
   * @param string $by        The property name used as condition
   * @param array  $arguments The arguments to pass at method call
   *
   * @return mixed
   * @throws Exception If the method called is invalid
   *
   */
  private function resolveMagicCall(string $method, string $by, array $arguments): mixed
  {
    if (! $arguments) {
      throw new Exception('Missing parameter for ' . $method . $by);
    }

    $fieldName = Str::snake($by);

    return $this->$method([$fieldName => $arguments[0]], ...array_slice($arguments, 1));
  }

  /**
   * @inheritDoc
   */
  public function count(array $criteria = []): int
  {
    $qb = $this->newQuery();

    foreach ($criteria as $attribute => $value)
    {
      $qb = $qb->where($attribute, $value);
    }

    return $qb->count();
  }

  /**
   * @inheritDoc
   */
  public function paginate(int $perPage, int $pageNumber, array $columns = ['*'], string $pageName = 'page'): LengthAwarePaginatorContract
  {
    $paginated = $this
      ->newQuery()
      ->paginate($perPage, $columns, 'page', $pageNumber);

    $items = Collection::make($paginated->items())
      ->map(fn($model) => $model->toEntity(null));

    return new LengthAwarePaginator($items, $paginated->total(), $perPage, $pageNumber, []);
  }

  /**
   * Save a new model and return the corresponding entinty instance.
   *
   * @param  array  $values
   * @return object
   */
  public function create(array $values = []): object
  {
    return $this->modelClass::create($values)->toEntity();
  }

  /**
   * Update records in the database.
   *
   * @param  array  $values
   * @return object
   */
  public function update(mixed $id, array $values): object
  {
    $model = $this->modelClass::query()
      ->whereKey($id)
      ->firstOrFail()
      ->fill($values);

    $model
      ->save();

    return $model
      ->toEntity();
  }

  /**
   *
   * @param object $entity
   * @return bool
   */
  public function save(object $entity): bool
  {
    $model = $this->newModel();

    $model = $model
      ->newQuery()
      ->findOrNew($entity->{$model->getKeyName()});

    if (!$model instanceof IEntityConvertible) {
      throw new InvalidModelException($model::class);
    }

    return $model
      ->fromEntity($entity)
      ->save();


  }

  /**
   * @alias save
   * @param object $entity
   * @return bool
   */
  public function persist(object $entity): bool
  {
    return $this->save($entity);
  }

}
