<?php

namespace Arkye\Repository\Concerns;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use ReflectionClass;

trait EntityConvertible
{

  public function fromEntity(object $entity): self
  {
    $props = $this->entityToArray($entity);

    foreach ($props as $propertyName => $value)
    {
      $this->{Str::snake($propertyName)} = $value;
    }

    return $this;
  }

  /**
   * @param object $entity
   * @return object
   */
  public function toEntity(object $entity): object
  {
    $publicProps = $this->getEntityPublicProperties($entity);
    $nonPublicProps = $this->getEntityNonPublicProperties($entity);

    foreach ($publicProps as $propertyName)
    {
      $entity->{$propertyName} = $this->{Str::snake($propertyName)} ?? null;
    }

    foreach ($nonPublicProps as $propertyName)
    {
      $setter = 'set' . ucfirst($propertyName);

      if (!method_exists($entity, $setter)) {
        continue;
      }

      $entity->{$setter}($this->{Str::snake($propertyName)} ?? null);
    }

    return $entity;
  }

  private function getEntityProperties(object $entity): array
  {
    $ref = new ReflectionClass($entity);

    return array_filter($ref->getProperties(), fn($prop) => !$prop->isStatic());
  }

  private function getEntityPublicProperties(object $entity): array
  {
    $props = $this->getEntityProperties($entity);

    return array_map(fn($prop) => $prop->getName(), array_filter($props, fn($prop) => $prop->isPublic()));
  }

  private function getEntityNonPublicProperties(object $entity): array
  {
    $props = $this->getEntityProperties($entity);

    return array_map(fn($prop) => $prop->getName(), array_filter($props, fn($prop) => !$prop->isPublic()));
  }

  private function entityToArray(object $entity): array
  {
    if ($entity instanceof Arrayable) {
      return $entity->toArray();
    }

    $publicProps = array_flip($this->getEntityPublicProperties($entity));
    $nonPublicProps = array_flip($this->getEntityNonPublicProperties($entity));

    array_walk($publicProps, fn(&$value, $propertyName) => $value = $entity->{$propertyName});
    array_walk($nonPublicProps, function (&$value, $propertyName) use ($entity) {
      $getter = 'get' . ucfirst($propertyName);

      $value = method_exists($entity, $getter)
        ? $entity->$getter()
        : null;
    });

    return array_merge($nonPublicProps, $publicProps);
  }

}
