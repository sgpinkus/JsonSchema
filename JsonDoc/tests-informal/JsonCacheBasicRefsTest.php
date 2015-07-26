<?php
use JsonDoc\JsonCache;
use JsonDoc\JsonLoader;
use JsonDoc\Uri;
use JsonDoc\Exception\JsonDecodeException;
use JsonDoc\JsonRefPriorityQueue;
use JsonDoc\JsonPointer;
require_once '../loader.php';

$uri = new Uri('file://' . realpath('../tests/test-data/basic-refs.json'));
$doc = json_decode(file_get_contents($uri));
if($doc == null) {
  throw new JsonDecodeException(json_last_error());
}

print "\n########## SOURCE DOC\n";
var_dump($doc);
print "\n";

print "\n########## FOUND REFS\n";
$queue = new JsonRefPriorityQueue();
JsonCache::queueAllRefs($doc,$queue,$uri);
$i = 0;
while(!$queue->isEmpty()) {
  $item = $queue->extract();
  print $item->getUri() . "\t" . $item->getPointer() . "\n";
  $r =& $item->getRef();
  $r = "XXX$i";
  $i++;
}
print "\n##########\n";
var_dump($doc);
