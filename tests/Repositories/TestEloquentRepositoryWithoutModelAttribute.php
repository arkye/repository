<?php

namespace Tests\Repositories;

use Arkye\Repository\Attributes\EntityAttribute;
use Arkye\Repository\EloquentEntityRepository;
use Tests\Entities\Test;

#[EntityAttribute(Test::class)]
class TestEloquentRepositoryWithoutModelAttribute extends EloquentEntityRepository
{

}
