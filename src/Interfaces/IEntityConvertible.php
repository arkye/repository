<?php

namespace Arkye\Repository\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface IEntityConvertible
{

  /**
   * @param object $entity
   * @return Model
   */
  public function fromEntity(object $entity): Model;

  /**
   * @param object $entity
   * @return object
   */
  public function toEntity(object $entity): object;

}
