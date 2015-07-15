<?php
/**
 * Convenience loader. Use very optional.
 */

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

$myPath = dirname(dirname(realpath(__FILE__)));
$myLoader = new MyLoader(["JsonDoc" => $myPath]);
spl_autoload_register([$myLoader, "load"]);
