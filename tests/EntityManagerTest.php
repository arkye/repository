<?php

namespace Tests;

use Arkye\Repository\EntityManager;
use PHPUnit\Framework\TestCase;
use Tests\Entities\Test;
use Tests\Repositories\TestRepository;

class EntityManagerTest extends TestCase
{

  public function testGetRepository()
  {
    $repository = EntityManager::getRepository(Test::class);

    $this->assertInstanceOf(TestRepository::class, $repository);
  }

}
