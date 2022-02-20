<?php

namespace Arkye\Repository\Exceptions;

use InvalidArgumentException;

class EntityRepositoryNotFoundException extends InvalidArgumentException
{
  /**
   * Create a new exception instance.
   *
   * @param string $entityClass
   * @return void
   */
  public function __construct(string $entityClass)
  {
    parent::__construct("Repository not found for Entity [{$entityClass}].");
  }
}
