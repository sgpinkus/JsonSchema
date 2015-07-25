<?php
use JsonDoc\JsonCache;
use JsonDoc\JsonLoader;
use JsonDoc\Uri;
use JsonDoc\Exception\JsonDecodeException;
use JsonDoc\JsonRefPriorityQueue;
require_once '../loader.php';
$cache = new JsonCache(new JsonLoader());
$doc = $cache->get(new Uri('file://' . realpath('../tests/test-data/basic-ref-to-ref.json')));
var_dump($doc);
