<?php

namespace Arkye\Repository;

use Arkye\Repository\Concerns\EntityConvertible;
use Arkye\Repository\Interfaces\IEntityConvertible as EntityConvertibleContract;
use Arkye\Repository\Interfaces\IEntityRepository;

class Model extends \Illuminate\Database\Eloquent\Model implements EntityConvertibleContract
{
  use EntityConvertible;

  public function getRepository(): IEntityRepository
  {
    $entityClass = str_replace('Infrastructure\\Models', 'Domain\\Entities', $this::class);

    if (str_ends_with($entityClass, 'Model')) {
      $entityClass = substr($entityClass, 0, strlen($entityClass) - 5);

    }
    return EntityManager::getRepository($entityClass);
  }

}
