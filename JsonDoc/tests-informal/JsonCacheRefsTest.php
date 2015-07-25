<?php
use JsonDoc\JsonCache;
use JsonDoc\JsonLoader;
use JsonDoc\Uri;
use JsonDoc\Exception\JsonDecodeException;
use JsonDoc\JsonRefPriorityQueue;

require_once '../loader.php';
$x = new JsonCache(new JsonLoader());

$uri = new Uri('file://' . realpath('../tests/test-data/basic-refs.json'));
$doc = json_decode(file_get_contents($uri));
if($doc == null) {
  throw new JsonDecodeException(json_last_error());
}
print "\n##########\n";
$queue = new JsonRefPriorityQueue();
JsonCache::queueAllRefs($doc,$queue,$uri);
var_dump($doc);

print "\n##########\n";
foreach($queue as $item) {
  print $item->getPointer() . " " . $item->getUri() . "\n";
  $r =& $item->getRef();
  $r = "XXXXXXX";
}
var_dump($doc);
