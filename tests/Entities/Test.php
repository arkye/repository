<?php

namespace Tests\Entities;

use Arkye\Repository\Attributes\DomainRepositoryAttribute;
use Tests\Contracts\TestRepository;

#[DomainRepositoryAttribute(TestRepository::class)]
class Test
{

  public ?string $id;

}
