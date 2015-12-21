<?php
require_once '../loader.php';
use JsonSchema\Constraint\EmptyConstraint;
use JsonSchema\Constraint\ValidationError;

$schemaDocs = [
  //['{"items": {}}', '[]'], // pass
  //['{"items": []}', '[]'], // pass
  //['{"items": [], "additionalItems": false}', '[1]'], //fail
  ['{"items": {"minimum": 0}}', '[-1,0,1]'], // fail
  //['{"minimum": 0}', -1]
];
for($i = 0; $i < count($schemaDocs); $i++) {
  $doc = $schemaDocs[$i];
  $schemaDoc = json_decode($doc[0]);
  $targetDoc = json_decode($doc[1]);
  $constraint = EmptyConstraint::build($schemaDoc);
  $valid = $constraint->validate($targetDoc, "");
  print "$i: {$doc[0]} {$doc[1]}:\n";
  if($valid === true) {
    print "OK\n";
  }
  else {
    print $valid;
  }
  print "\n";
}
