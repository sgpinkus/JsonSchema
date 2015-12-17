<?php
require_once '../loader.php';
use JsonDocs\JsonDocs;
use JsonDocs\JsonLoader;
use JsonDocs\Uri;
use JsonDocs\JsonRefPriorityQueue;

$basicRefsJson = 'file:///' . dirname(__FILE__) . '/test-data/basic-refs.json';
$jsonDoc = new JsonDocs(new JsonLoader());
$doc = $jsonDoc->get(new Uri($basicRefsJson));
var_dump($doc);
print "---\n";
$basicRefsJson = file_get_contents($basicRefsJson);
$doc = json_decode($basicRefsJson);
$refQueue = new JsonRefPriorityQueue();
$refUris = [];
$ids = [];
$uri = new Uri('file://' . getenv('DATADIR') . '/basic-refs.json');
JsonDocs::parseDoc($doc, $refQueue, $refUris, $ids, $uri);
foreach($refQueue as $k => $ref) {
  print "$k {$ref->getUri()} {$ref->getPointer()}\n";
}
