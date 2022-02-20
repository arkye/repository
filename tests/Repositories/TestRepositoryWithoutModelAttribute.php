<?php

namespace Tests\Repositories;

use Arkye\Repository\Attributes\EntityAttribute;
use Arkye\Repository\EntityRepository;
use Tests\Entities\Test;

#[EntityAttribute(Test::class)]
class TestRepositoryWithoutModelAttribute extends EntityRepository
{

}
