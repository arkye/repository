<?php

namespace Tests;

use Arkye\Repository\Exceptions\EntityAttributeNotSetException;
use Arkye\Repository\Exceptions\ModelAttributeNotSetException;
use PHPUnit\Framework\TestCase;
use Tests\Entities\Test;
use Tests\Models\TestModel;
use Tests\Repositories\TestEloquentRepository;
use Tests\Repositories\TestEloquentRepositoryWithoutEntityAttribute;
use Tests\Repositories\TestEloquentRepositoryWithoutModelAttribute;

class EloquentEntityRepositoryTest extends TestCase
{

  public function testEntityAttributeNotSet()
  {
    $this->expectException(EntityAttributeNotSetException::class);

    new TestEloquentRepositoryWithoutEntityAttribute();
  }

  public function testModelAttributeNotSet()
  {
    $this->expectException(ModelAttributeNotSetException::class);

    new TestEloquentRepositoryWithoutModelAttribute();
  }

  public function testNewEntity()
  {
    $repository = new TestEloquentRepository();

    $this->assertInstanceOf(Test::class, $repository->newEntity());
  }

  public function testNewModel()
  {
    $repository = new TestEloquentRepository();

    $this->assertInstanceOf(TestModel::class, $repository->newModel());
  }

}
