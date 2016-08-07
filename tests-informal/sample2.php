<?php
require_once './vendor/autoload.php';
use JsonDocs\JsonDocs;
use JsonDocs\JsonLoader;
use JsonDocs\Uri;
use JsonSchema\JsonSchema;
$json = '{}';
$schema = '{}';
// JsonDocs does the dereferencing, and acts as a cache of JSON docs.
$jsonDocs = new JsonDocs(new JsonLoader());
// Build validator, and dereference JSON Doc. JsonDocs would attempt to load from URI if 2nd arg not passed.
$schema = new JsonSchema($jsonDocs->get(new Uri("file:///tmp/fake.json#/users/0"), $schema));
$valid = $schema->validate(json_decode($json));
if($valid === true) {
  print "OK\n";
}
else {
  print $valid;
}
