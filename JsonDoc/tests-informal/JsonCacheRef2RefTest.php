<?php
use JsonDoc\JsonCache;
use JsonDoc\JsonLoader;
use JsonDoc\Uri;
use JsonDoc\Exception\JsonDecodeException;
use JsonDoc\JsonRefPriorityQueue;
use JsonDoc\JsonPointer;
require_once '../loader.php';
$cache = new JsonCache(new JsonLoader());
$doc = $cache->get(new Uri('file://' . realpath('../tests/test-data/basic-ref-to-ref.json')));
var_dump($doc);
$doc->C->Value = 33;
var_dump($doc);
