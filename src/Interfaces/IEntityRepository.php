<?php

namespace Arkye\Repository\Interfaces;

use Arkye\Repository\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use UnexpectedValueException;

interface IEntityRepository
{

  /**
   * Returns a new instance of entity.
   *
   * @param IEntityConvertible|null $model
   * @return object
   */
  public function newEntity(IEntityConvertible $model = null): object;

  /**
   * Returns a new instance of model.
   *
   * @param object|null $entity
   * @return Model
   */
  public function newModel(object $entity = null): Model;

  /**
   * Get a new query builder for the model's table.
   *
   * @param array|string $relations Optional relationships.
   *
   * @return Builder
   */
  public function newQuery(array|string $relations = []): Builder;

  /**
   * Finds an object by its primary key / identifier.
   *
   * @param mixed $id The identifier.
   * @param array|string $relations Optional relationships.
   *
   * @return object|null The object.
   */
  public function find($id, array|string $relations = []): ?object;

  /**
   * Finds all objects in the repository.
   *
   * @param array|string $relations Optional relationships.
   *
   * @return Collection The objects.
   */
  public function findAll(array|string $relations = []): Collection;

  /**
   * Finds objects by a set of criteria.
   *
   * Optionally sorting and limiting details can be passed. An implementation may throw
   * an UnexpectedValueException if certain values of the sorting or limiting details are
   * not supported.
   *
   * @param array $criteria
   * @param array|string $relations Optional relationships.
   * @param string[]|null $orderBy
   * @param int|null $limit
   * @param int|null $offset
   *
   * @return Collection The objects.
   *
   * @throws UnexpectedValueException
   */
  public function findBy(array $criteria, array|string $relations = [], ?array $orderBy = [], int $limit = null, int $offset = null): Collection;

  /**
   * Finds a single object by a set of criteria.
   *
   * @param array $criteria The criteria.
   * @param array|string $relations Optional relationships.
   * @param string[] $orderBy
   *
   * @return object|null The object.
   */
  public function findOneBy(array $criteria, array|string $relations = [], array $orderBy = []): ?object;

  /**
   * @param array $criteria
   * @return int
   */
  public function count(array $criteria = []): int;

  /**
   * @param int $perPage
   * @param int $pageNumber
   * @param array $columns
   * @param string $pageName
   * @return LengthAwarePaginator
   */
  public function paginate(int $perPage, int $pageNumber, array $columns = ['*'], string $pageName = 'page'): LengthAwarePaginator;

  /**
   * Save a new model and return the corresponding entity instance.
   *
   * @param  array  $values
   * @return object
   */
  public function create(array $values = []): object;

  /**
   * Update records in the database.
   *
   * @param  mixed  $id
   * @param  array  $values
   * @return object
   */
  public function update(mixed $id, array $values): object;

  /**
   * @param object $entity
   * @return bool
   */
  public function save(object $entity): bool;

}
