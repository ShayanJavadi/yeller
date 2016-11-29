<?php

namespace SMA\Middleware;
//base for all middleware
class Middleware
{
  protected $container;

  public function __construct($container)
  {
    //grab container
    $this->container = $container;
  }
}

 ?>
