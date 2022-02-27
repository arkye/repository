<?php

namespace Arkye\Repository;

use Illuminate\Contracts\Routing\UrlRoutable;
use ReflectionException;

class UrlRoutableEntity implements UrlRoutable
{

  public function getRouteKey()
  {
    return 'id';
  }

  public function getRouteKeyName()
  {
    return 'id';
  }

  /**
   * Retrieve the model for a bound value.
   *
   * @param mixed $value
   * @param string|null $field
   * @return self|null
   * @throws ReflectionException
   */
  public function resolveRouteBinding($value, $field = null): ?self
  {
    $model = EntityManager::getRepository($this::class)
      ->newModel();

    return $model
      ->resolveRouteBindingQuery($model->newQuery(), $value, $field)
      ->get()
      ->first()
      ?->toEntity(null);
  }

  /**
   * Retrieve the child model for a bound value.
   *
   * @param string $childType
   * @param mixed $value
   * @param string|null $field
   * @return self|null
   * @throws ReflectionException
   */
  public function resolveChildRouteBinding($childType, $value, $field): ?self
  {
    return EntityManager::getRepository($this::class)
      ->newModel()
      ->resolveChildRouteBindingQuery($childType, $value, $field)
      ->get()
      ->first()
      ?->toEntity(null);
  }

}
