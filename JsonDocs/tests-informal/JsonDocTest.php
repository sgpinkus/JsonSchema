<?php
require_once '../loader.php';
use JsonDocs\JsonDocs;
use JsonDocs\JsonLoader;
use JsonDocs\Uri;
use JsonDocs\JsonRefPriorityQueue;
use JsonDocs\Exception\ResourceNotFoundException;
$basicRefsJsonUri = 'file:///' . dirname(__FILE__) . '/test-data/basic-refs.json';
$basicRefsJson = json_decode(file_get_contents($basicRefsJsonUri));
assert($basicRefsJson !== null);
$jsonDoc = new JsonDocs();
$doc = $jsonDoc->get(new Uri('file:///fooey'), $basicRefsJson);
var_dump($doc);
print "---\n";
$refQueue = new JsonRefPriorityQueue();
$refUris = [];
$ids = [];
$uri = new Uri('file://' . getenv('DATADIR') . '/basic-refs.json');
$basicRefsJson = json_decode(file_get_contents($basicRefsJsonUri));
JsonDocs::parseDoc($basicRefsJson, $refQueue, $refUris, $ids, $uri);
foreach($refQueue as $k => $ref) {
  print "$k {$ref->getUri()} {$ref->getPointer()}\n";
}
print "---\n";
$jsonDocs = new JsonDocs();
$doc = $jsonDocs->get(new Uri("file:///user-schema.json"), json_decode(file_get_contents('file://' . dirname(__FILE__) . '/test-data/user-schema.json')));
var_dump($doc);
$doc = $jsonDocs->get(new Uri("file:///astring.json"), json_decode("\"astring\""));
var_dump($doc);
print "---\n";
$jsonDocs = new JsonDocs();
$docUri = 'file://' . dirname(__FILE__) . '/test-data//schema.json';
$doc = json_decode(file_get_contents($docUri));
var_dump($doc);
$schemaDoc = $jsonDocs->get(new Uri($docUri), $doc);
print "---\n";
$jsonDocs = new JsonDocs();
$docUri = 'file://' . dirname(__FILE__) . '/test-data//no-keyword-id.json';
$doc = json_decode(file_get_contents($docUri));
$schemaDoc = $jsonDocs->get(new Uri($docUri), $doc);
try {
  $p = $jsonDocs->pointer(new Uri("$docUri#fooey"));
}
catch(ResourceNotFoundException $e) {}
