<?php

namespace Arkye\Repository\Exceptions;

use InvalidArgumentException;

class EntityAttributeNotSetException extends InvalidArgumentException
{
  /**
   * Create a new exception instance.
   *
   * @param string $repositoryClass
   * @return void
   */
  public function __construct(string $repositoryClass)
  {
    parent::__construct("Entity attribute not set for Repository [{$repositoryClass}].");
  }
}
