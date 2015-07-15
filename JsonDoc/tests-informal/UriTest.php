<?php
use JsonDoc\JsonCache;
use JsonDoc\JsonLoader;
use JsonDoc\Uri;
use JsonDoc\Exception\JsonDecodeException;
use JsonDoc\JsonRefPriorityQueue;

require_once '../loader.php';
$uri = new Uri('file://' . realpath('../tests/test-data/basic-refs.json') . "#/sa");
$uri->fragment = "WEEWEW";
$uri->fragment = $uri->fragment ? "ADSFDA" : "NN";
var_dump($uri);
