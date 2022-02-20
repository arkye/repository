<?php

namespace Arkye\Repository\Attributes;

class RepositoryAttribute
{

  public function __construct(private string $classname)
  {
  }

  public function newInstance(): ?object
  {
    return new $this->classname(...func_get_args());
  }

}
