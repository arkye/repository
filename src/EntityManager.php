<?php

namespace Arkye\Repository;

use Arkye\Repository\Attributes\DomainRepositoryAttribute;
use Arkye\Repository\Exceptions\EntityRepositoryNotFoundException;
use Arkye\Repository\Interfaces\IEntityRepository;
use Exception;
use ReflectionClass;
use ReflectionException;

class EntityManager
{

  private static array $aliases = [];
  private static array $instances = [];

  /**
   * @param string $entityClass
   * @return IEntityRepository
   * @throws ReflectionException
   * @throws Exception
   */
  public static function getRepository(string $entityClass): IEntityRepository
  {
    if (isset(self::$instances[$entityClass])) {
      return self::$instances[$entityClass];
    }

    if (isset(self::$aliases[$entityClass])) {
      self::$instances[$entityClass] = app(self::$aliases[$entityClass]);
      return self::$instances[$entityClass];
    }

    $repository = self::getRepositoryFromEntityClass($entityClass);

    if ($repository !== null) {
      return $repository;
    }

    $repository = self::getRepositoryFromEntityAttribute($entityClass);

    if ($repository === null) {
      throw new EntityRepositoryNotFoundException($entityClass);
    }

    return $repository;
  }

  /**
   * @throws Exception
   */
  public static function persist(object $entity)
  {
    self::getRepository($entity::class)->persist($entity);
  }

  protected static function getRepositoryFromEntityClass(string $entityClass): ?object
  {
    $interfaceClass = str_replace('Entities\\', 'Repositories\\', $entityClass) . 'Repository';

    return self::createRepository($entityClass, $interfaceClass);
  }

  /**
   * @throws ReflectionException
   */
  protected static function getRepositoryFromEntityAttribute(string $entityClass): ?object
  {
    $ref = new ReflectionClass($entityClass);
    $attribute = $ref->getAttributes(DomainRepositoryAttribute::class)[0] ?? null;

    if ($attribute === null) {
      return null;
    }

    $interfaceClass = $attribute->getArguments()[0] ?? null;

    return self::createRepository($entityClass, $interfaceClass);
  }

  protected static function createRepository(string $entityClass, string $interfaceClass): ?object
  {
    if (!interface_exists($interfaceClass)) {
      return null;
    }

    self::$aliases[$entityClass] = $interfaceClass;
    self::$instances[$entityClass] = app($interfaceClass);

    return self::$instances[$entityClass];
  }

}
