<?php

namespace Arkye\Repository\Exceptions;

use InvalidArgumentException;

class InvalidModelException extends InvalidArgumentException
{
  /**
   * Create a new exception instance.
   *
   * @param string $modelClass
   * @return void
   */
  public function __construct(string $modelClass)
  {
    parent::__construct("Model [{$modelClass}] must be EntityConvertible.");
  }
}
