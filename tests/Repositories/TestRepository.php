<?php

namespace Tests\Repositories;

use Arkye\Repository\Attributes\EntityAttribute;
use Arkye\Repository\Attributes\ModelAttribute;
use Arkye\Repository\EntityRepository;
use Tests\Entities\Test;
use Tests\Models\TestModel;

#[EntityAttribute(Test::class)]
#[ModelAttribute(TestModel::class)]
class TestRepository extends EntityRepository
{

}
