<?php

namespace Arkye\Repository\Exceptions;

use InvalidArgumentException;

class ModelAttributeNotSetException extends InvalidArgumentException
{
  /**
   * Create a new exception instance.
   *
   * @param string $repositoryClass
   * @return void
   */
  public function __construct(string $repositoryClass)
  {
    parent::__construct("Model attribute not set for Repository [{$repositoryClass}].");
  }
}
