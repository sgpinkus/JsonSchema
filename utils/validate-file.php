#!/usr/bin/env php
<?php
require_once 'vendor/autoload.php';
use JsonDoc\JsonDocs;
use JsonDoc\JsonLoader;
use JsonDoc\Uri;
use JsonSchema\JsonSchema;
use JsonSchema\Constraint\ValidationError;

function main($argc, $argv) {
  (sizeof($argv) == 3 or sizeof($argv) == 4) or die(usage());
  $jsonDocs = new JsonDocs(new JsonLoader());
  list($schemaFile, $schemaPointer) = makePath($argv[1]) or die("Invalid schema file\n");
  $schemaDoc = $jsonDocs->loadUri(new Uri($schemaFile));
  $schema = new JsonSchema($schemaDoc);
  print "Schema created from $schemaFile\n";
  $target = json_decode(file_get_contents($argv[2])) or die("Invalid JSON file\n");
  print "Target loaded\n";
  if(isset($argv[3])) {
    $target = $jsonDocs::getPointer($target, $argv[3]);
  }
  print "Validate [at $schemaPointer]:\n";
  $valid = $schema->validate($target, $schemaPointer);
  if($valid === true) {
    print "OK\n";
  }
  else {
    print $valid;
  }
}

function makePath($file) {
  @list($file, $frag) = explode("#", $file);
  $file = realpath($file);
  $frag = $frag ? "$frag" : "/";
  if(!$file) {
    return false;
  }
  return ["file://$file", $frag];
}

function usage() {
  return "Usage: " . basename(__FILE__) . " <schemafile> <targetjsonfile> [<pointer>]\n";
}

main($argc, $argv);
