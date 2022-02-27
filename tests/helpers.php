<?php

if (!function_exists('app')) {
  function app(string $interface)
  {
    return new (str_replace(['\\Contracts', 'Repository'], ['\\Repositories', 'EloquentRepository'], $interface))();
  }
}
