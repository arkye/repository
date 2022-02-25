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
      if ($this->isRelation($propertyName)) {
        $this->handleEntityRelation($propertyName, $prop);
        continue;
      }

      $this->{Str::snake($propertyName)} = $prop['value'];
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
      if ($this->isRelation($propertyName)) {
        $this->handleModelRelation($entity, $propertyName, $prop);
        continue;
      }

      if ($prop['public']) {
        $entity->{$propertyName} = $this->{Str::snake($propertyName)} ?? null;
      } else {
        $setter = 'set' . ucfirst($propertyName);

        if (!method_exists($entity, $setter)) {
          continue;
        }

        $entity->{$setter}($this->{Str::snake($propertyName)} ?? null);
      }

    }

    return $entity;
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
