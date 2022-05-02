<?php

namespace Arkye\Repository\Concerns;

use Arkye\Repository\EntityManager;
use Arkye\Repository\Model;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;

trait EntityConvertible
{

  /**
   * @param object $entity
   * @return Model
   * @throws ReflectionException
   */
  public function fromEntity(object $entity): Model
  {
    $map = $this->getEntityMap($entity);

    foreach ($map as $propertyName => $prop)
    {
      $key = Str::snake($propertyName);

      if ($this->isModelAttribute($key)) {
        $this->{$key} = $prop['value'];
        continue;
      }

      // Here we will determine if the model base class itself contains this given key
      // since we don't want to treat any of those methods as relationships because
      // they are all intended as helper methods and none of these are relations.
      if (method_exists(self::class, $key)) {
        continue;
      }

      if (!$this->isRelation($propertyName)) {
        continue;
      }

      $this->handleEntityRelation($propertyName, $prop);
    }

    return $this;
  }

  /**
   * @param object|null $entity
   * @return object
   * @throws ReflectionException
   */
  public function toEntity(object $entity = null): object
  {
    $entity ??= $this->getRepository()->newEntity();

    $map = $this->getEntityMap($entity);

    foreach ($map as $propertyName => $prop)
    {
      $key = Str::snake($propertyName);

      // If the attribute exists in the attribute array or has a "get" mutator we will
      // get the attribute's value. Otherwise, we will proceed as if the developers
      // are asking for a relationship's value. This covers both types of values.
      if ($this->isModelAttribute($key)) {
        $this->handleModelAttribute($entity, $propertyName, $prop);
        continue;
      }

      // Here we will determine if the model base class itself contains this given key
      // since we don't want to treat any of those methods as relationships because
      // they are all intended as helper methods and none of these are relations.
      if (method_exists(self::class, $key)) {
        continue;
      }

      if (!$this->isRelation($propertyName)) {
        continue;
      }

      $this->handleModelRelation($entity, $propertyName, $prop);
    }

    return $entity;
  }

  private function isModelAttribute(string $key): bool
  {
    if (array_key_exists($key, $this->attributes) ||
      array_key_exists($key, $this->casts) ||
      $this->hasGetMutator($key) ||
      $this->hasAttributeMutator($key) ||
      $this->hasSetMutator($key) ||
      $this->hasAttributeSetMutator($key) ||
      $this->isClassCastable($key)) {
      return true;
    }

    return false;
  }

  /**
   * @param object $entity
   * @param string $propertyName
   * @param array $prop
   * @return void
   * @throws ReflectionException
   */
  private function handleModelAttribute(object &$entity, string $propertyName, array $prop): void
  {
    if ($prop['public']) {
      $entity->{$propertyName} = $this->{Str::snake($propertyName)} ?? null;
      return;
    }

    $setter = 'set' . ucfirst($propertyName);

    if (!method_exists($entity, $setter)) {
      return;
    }

    $entity->{$setter}($this->{Str::snake($propertyName)} ?? null);
  }

  /**
   * @param object $entity
   * @param string $propertyName
   * @param array $prop
   * @return void
   * @throws ReflectionException
   */
  private function handleModelRelation(object &$entity, string $propertyName, array $prop): void
  {
    if (!$this->relationLoaded($propertyName)) {
      return;
    }

    $relationModel = $this->{$propertyName};

    if (null === $relationModel && $prop['nullable']) {
      $entity->{$propertyName} = null;
      return;
    }

    $entity->{$propertyName} = $relationModel instanceof Collection
      ? $relationModel->map(fn($item) => $item?->toEntity() ?? $item->getRepository()->newEntity())
      : $relationModel?->toEntity() ?? $relationModel->getRepository()->newEntity();
  }

  /**
   * @param string $propertyName
   * @param array $prop
   * @return void
   * @throws ReflectionException
   */
  private function handleEntityRelation(string $propertyName, array $prop): void
  {
    if (blank($prop['value'])) {
      $this->setRelation($propertyName, null);
      return;
    }

    $model = EntityManager::getRepository($prop['type'])->newModel($prop['value'] ?? (object) []);

    $this->setRelation($propertyName, $model);
  }

  /**
   * @param object $entity
   * @return array
   */
  private function getEntityMap(object $entity): array
  {
    $convertedEntity = ($entity instanceof Arrayable)
      ? $entity->toArray()
      : [];

    $map = [];
    $ref = new ReflectionClass($entity);

    foreach ($ref->getProperties() as $prop)
    {
      if ($prop->isStatic()) {
        continue;
      }

      $propMap = [
        'public' => $prop->isPublic(),
        'nullable' => $prop->getType()->allowsNull(),
        'type' => $prop->getType()->getName(),
        'value' => null,
      ];

      if ($prop->isPublic()) {
        $propMap['value'] = $convertedEntity[$prop->getName()] ?? $entity->{$prop->getName()} ?? null;
      } else {
        $getter = 'get' . ucfirst($prop->getName());
        $propMap['value'] = $convertedEntity[$prop->getName()] ?? (
          method_exists($entity, $getter)
            ? $entity->$getter()
            : null
          );
      }

      $map[$prop->getName()] = $propMap;
    }

    return $map;
  }

}
