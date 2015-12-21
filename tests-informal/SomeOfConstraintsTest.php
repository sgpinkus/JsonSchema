<?php
require_once '../loader.php';
use JsonSchema\Constraint\EmptyConstraint;
use JsonSchema\Constraint\ValidationError;

$schemaDocs = [
  ['{"anyOf": [{},{}], "fooey": {"minimum": 99}}', '{}'],
  ['{"allOf": [{},{}]}', '{}'],
  ['{"oneOf": [{},{}]}', '{}'],
  ['{"anyOf": [{"minimum": 0}, {"maximum": 5}]}', -1],
  ['{"allOf": [{"minimum": 0}, {"maximum": 5}]}', -1],
  ['{"oneOf": [{"minimum": 0}, {"maximum": 5}]}', -1],
  ['{"anyOf": [{"minimum": 0}, {"maximum": 5}]}', 1],
  ['{"allOf": [{"minimum": 0}, {"maximum": 5}]}', 1],
  ['{"oneOf": [{"minimum": 0}, {"maximum": 5}, {"fooey": {}}]}', 1],
];
for($i = 0; $i < count($schemaDocs); $i++) {
  $doc = $schemaDocs[$i];
  $schemaDoc = json_decode($doc[0]);
  $targetDoc = json_decode($doc[1]);
  $constraint = EmptyConstraint::build($schemaDoc);
  $valid = $constraint->validate($doc[1], "");
  print "$i: {$doc[0]} {$doc[1]}:\n";
  if($valid === true) {
    print "OK\n";
  }
  else {
    print $valid;
  }
  print "\n";
}
