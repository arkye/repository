<?php

namespace Tests\Repositories;

use Arkye\Repository\Attributes\ModelAttribute;
use Arkye\Repository\EloquentEntityRepository;
use Tests\Models\TestModel;

#[ModelAttribute(TestModel::class)]
class TestEloquentRepositoryWithoutEntityAttribute extends EloquentEntityRepository
{

}
