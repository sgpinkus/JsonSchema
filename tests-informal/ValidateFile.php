<?php
require_once '../loader.php';
use JsonDocs\JsonDocs;
use JsonDocs\JsonLoader;
use JsonDocs\Uri;
use JsonSchema\JsonSchema;
use JsonSchema\Constraint\ValidationError;

function main($argc, $argv) {
  sizeof($argv) == 3 or die(usage());
  $jsonDocs = new JsonDocs(new JsonLoader());
  $schema_file = make_path($argv[1]) or die("Invalid schema file\n");
  $schemaDoc = $jsonDocs->get(new Uri($schema_file));
  $schema = new JsonSchema($schemaDoc);
  print "Schema created\n";
  $target = json_decode(file_get_contents($argv[2])) or die("Invalid JSON file\n");
  print "Target loaded:\n";
  var_dump($target);
  $target = $jsonDocs->get(new Uri("file:///tmp/target.json"), $target);
  $valid = $schema->validate($target);
  if($valid instanceof ValidationError) {
    print "Validation Failed:\n";
    print $valid;
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
