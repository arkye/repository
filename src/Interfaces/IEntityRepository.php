<?php

namespace Arkye\Repository\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use UnexpectedValueException;

interface IEntityRepository
{

  /**
   * Returns a new instance of entity.
   *
   * @param object|null $model
   * @return object
   */
  public function newEntity(?object $model = null): object;

  /**
   * Returns a new instance of model.
   *
   * @param object|null $entity
   * @return object|null
   */
  public function newModel(?object $entity = null): ?object;

  /**
   * Get a new query builder for the model's table.
   *
   * @return Builder
   */
  public function newQuery(): Builder;

  /**
   * Finds an object by its primary key / identifier.
   *
   * @param mixed $id The identifier.
   *
   * @return object|null The object.
   */
  public function find($id): ?object;

  /**
   * Finds all objects in the repository.
   *
   * @return Collection The objects.
   */
  public function findAll(): Collection;

  /**
   * Finds objects by a set of criteria.
   *
   * Optionally sorting and limiting details can be passed. An implementation may throw
   * an UnexpectedValueException if certain values of the sorting or limiting details are
   * not supported.
   *
   * @param array $criteria
   * @param string[]|null $orderBy
   * @param int|null $limit
   * @param int|null $offset
   *
   * @return Collection The objects.
   *
   * @throws UnexpectedValueException
   */
  public function findBy(array $criteria, ?array $orderBy = [], int $limit = null, int $offset = null): Collection;

  /**
   * Finds a single object by a set of criteria.
   *
   * @param array $criteria The criteria.
   * @param string[] $orderBy
   *
   * @return object|null The object.
   */
  public function findOneBy(array $criteria, array $orderBy = []): ?object;

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
   * @param object $entity
   * @return bool
   */
  public function persist(object $entity): bool;

}
