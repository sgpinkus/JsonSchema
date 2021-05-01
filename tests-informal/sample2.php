<?php
require_once './vendor/autoload.php';
use JsonRef\JsonDocs;
use JsonRef\JsonLoader;
use JsonRef\Uri;
use JsonSchema\JsonSchema;
$json = '{}';
$schema = '{}';
// JsonDocs does the dereferencing, and acts as a cache of JSON docs.
$jsonDocs = new JsonDocs(new JsonLoader());
// Build validator, and dereference JSON Doc. JsonDocs would attempt to load from URI if 2nd arg not passed.
$schema = new JsonSchema($jsonDocs->loadDocStr($schema, new Uri("users/0")));
$valid = $schema->validate(json_decode($json));
if($valid === true) {
  print "OK\n";
}
else {
  print $valid;
}
