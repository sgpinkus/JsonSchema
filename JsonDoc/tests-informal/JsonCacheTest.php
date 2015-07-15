<?php
use JsonDoc\JsonCache;
use JsonDoc\JsonLoader;
use JsonDoc\Uri;
use JsonDoc\Exception\JsonDecodeException;
use JsonDoc\JsonRefPriorityQueue;

require_once '../loader.php';
$x = new JsonCache(new JsonLoader());
define("DEBUG", 1);

$uri = new Uri('file://' . realpath('../tests/test-data/simple-refs.json'));
$doc = json_decode(file_get_contents($uri));
if($doc == null) {
  throw new JsonDecodeException(json_last_error());
}

$queue = new JsonRefPriorityQueue();
JsonCache::queueAllRefs($doc,$queue,$uri);
print "\n##########\n";
var_dump($doc);
print "\n##########\n";

foreach($queue as $item) {
  print $item->getPointer() . " " . $item->getUri() . "\n";
  $r =& $item->getRef();
  $r = "XXXXXXX";
}
var_dump($doc);
