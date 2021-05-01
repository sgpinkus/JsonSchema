#!/usr/bin/env php
<?php
require_once './vendor/autoload.php';
use JsonRef\JsonDocs;
use JsonRef\JsonLoader;
use JsonRef\Uri;
use JsonSchema\JsonSchema;

if($argc != 3) {
  echo "Usage: ${argv[0]} <schema-filename> <doc-filename>\n";
  exit(1);
}

$jsonDocs = new JsonDocs(new JsonLoader());
$doc = json_decode(file_get_contents($argv[2]));
$schema = new JsonSchema($jsonDocs->loadUri('file://' . realpath($argv[1])));

$valid = $schema->validate($doc);
if($valid === true)
  print "OK\n";
else
  print $valid;
