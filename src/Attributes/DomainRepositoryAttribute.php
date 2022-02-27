<?php

namespace Arkye\Repository\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class DomainRepositoryAttribute extends RepositoryAttribute
{
  public function __construct(private string $classname)
  {
  }

  public function getClassName(): string
  {
    return $this->classname;
  }
}
