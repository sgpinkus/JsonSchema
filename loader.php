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

  /**
   * @input $name FQ classname, without a leading "\".
   */
  function load($name) {
    foreach($this->bases as $prefix => $basePath) {
      if(strpos($name, $prefix) === 0) {
        $name = substr($name, strlen($prefix));
        $path = $basePath . "/" . str_replace("\\", "/", $name) . ".php";
        include_once $path;
      }
    }
  }
}

$myPath = dirname(realpath(__FILE__));
$myLoader = new MyLoader([
  "JsonSchema" => $myPath,
  "JsonDoc" => "$myPath/JsonDoc"]
);
spl_autoload_register([$myLoader, "load"]);
