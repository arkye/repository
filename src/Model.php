<?php

namespace Arkye\Repository;

use Arkye\Repository\Concerns\EntityConvertible;
use Arkye\Repository\Interfaces\IEntityConvertible as EntityConvertibleContract;

class Model extends \Illuminate\Database\Eloquent\Model implements EntityConvertibleContract
{
  use EntityConvertible;
}
