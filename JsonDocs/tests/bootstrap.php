<?php

/**
 * Simple class loader.
 */
class MyLoader
{
  protected $bases = [];

  public function __construct(array $bases) {
    $this->bases = $bases;
  }

  function load($name) {
    foreach($this->bases as $prefix => $basePath) {
      $prefixLoc = strpos($name, $prefix);
      if($prefixLoc === 0) {
        $path = $basePath . "/" . str_replace("\\", "/", $name) . ".php";
        include_once $path;
      }
    }
  }
}

$myLoader = new MyLoader(["JsonDoc" => "../"]);
spl_autoload_register([$myLoader, "load"]);

// Test data files.
putenv("DATADIR=".dirname(__FILE__) . "/test-data");
