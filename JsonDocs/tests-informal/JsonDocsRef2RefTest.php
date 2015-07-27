<?php
use JsonDocs\JsonDocs;
use JsonDocs\JsonLoader;
use JsonDocs\Uri;
use JsonDocs\Exception\JsonDecodeException;
use JsonDocs\JsonRefPriorityQueue;

require_once '../loader.php';
$cache = new JsonDocs(new JsonLoader());
$doc = $cache->get(new Uri('file://' . realpath('../tests/test-data/basic-ref-to-ref-succeeds.json')));
var_dump($doc);
$doc = $cache->get(new Uri('file://' . realpath('../tests/test-data/basic-ref-to-ref.json')));
var_dump($doc);
