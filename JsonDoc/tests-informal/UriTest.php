<?php
use JsonDoc\Uri;
require_once '../loader.php';
$uri = new Uri('file://' . realpath('../tests/test-data/basic-refs.json') . "#/sa");
$uri->fragment = "Fraggy";
$uri->fragment = $uri->fragment ? "YY" : "NN";
var_dump($uri);
