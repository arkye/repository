<?php

namespace Arkye\Repository\Interfaces;

use Arkye\Repository\Model;

interface IEntityConvertible
{

  /**
   * @param object $entity
   * @return Model
   */
  public function fromEntity(object $entity): Model;

  /**
   * @param object|null $entity
   * @return object
   */
  public function toEntity(object $entity = null): object;

}
