<?php

namespace Tests\Repositories;

use Arkye\Repository\Attributes\ModelAttribute;
use Arkye\Repository\EntityRepository;
use Tests\Models\TestModel;

#[ModelAttribute(TestModel::class)]
class TestRepositoryWithoutEntityAttribute extends EntityRepository
{

}
