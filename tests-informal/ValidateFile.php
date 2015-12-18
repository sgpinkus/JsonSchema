<?php
require_once '../loader.php';
use JsonDocs\JsonDocs;
use JsonDocs\JsonLoader;
use JsonDocs\Uri;
use JsonSchema\JsonSchema;
use JsonSchema\Constraint\ValidationError;

function main($argc, $argv) {
  sizeof($argv) == 3 or die(usage());
  $schema_file = make_path($argv[1]) or die("Invalid schema file\n");
  $target = file_get_contents($argv[2]) or die("Invalid JSON file\n");
  print "$schema_file $target\n";
  $jsonDocs = new JsonDocs(new JsonLoader());
  $doc = $jsonDocs->get(new Uri($schema_file));
  $schema = new JsonSchema($doc);
  $valid = $schema->validate($target);
  if($valid instanceof ValidationError) {
    print "Validation Failed:\n";
    print $valid->getName() . ": " .$valid->getMessage() ."\n";
  }
  else {
    print "OK.\n";
  }
}

function make_path($file) {
  $file = realpath($file);
  if(!$file) {
    return false;
  }
  return "file://$file";
}

function usage() {
  return "Usage: " . basename(__FILE__) . " <schemafile> <targetjsonfile>\n";
}

main($argc, $argv);
