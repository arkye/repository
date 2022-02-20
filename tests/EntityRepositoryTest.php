<?php

namespace Tests;

use Arkye\Repository\Exceptions\EntityAttributeNotSetException;
use Arkye\Repository\Exceptions\ModelAttributeNotSetException;
use PHPUnit\Framework\TestCase;
use Tests\Entities\Test;
use Tests\Models\TestModel;
use Tests\Repositories\TestRepository;
use Tests\Repositories\TestRepositoryWithoutEntityAttribute;
use Tests\Repositories\TestRepositoryWithoutModelAttribute;

class EntityRepositoryTest extends TestCase
{

  public function testEntityAttributeNotSet()
  {
    $this->expectException(EntityAttributeNotSetException::class);

    new TestRepositoryWithoutEntityAttribute();
  }

  public function testModelAttributeNotSet()
  {
    $this->expectException(ModelAttributeNotSetException::class);

    new TestRepositoryWithoutModelAttribute();
  }

  public function testNewEntity()
  {
    $repository = new TestRepository();

    $this->assertInstanceOf(Test::class, $repository->newEntity());
  }

  public function testNewModel()
  {
    $repository = new TestRepository();

    $this->assertInstanceOf(TestModel::class, $repository->newModel());
  }

}
