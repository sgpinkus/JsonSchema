<?php
require_once '../loader.php';
use JsonSchema\Constraint\EmptyConstraint;
$schemaDoc = '{
  "anyOf": [{},{},{}]
}';
$targetDoc = '{}';
$schemaDoc = json_decode($schemaDoc);
$constraint = EmptyConstraint::build($schemaDoc);
$valid = $constraint->validate($targetDoc, "");
if($valid === true) {
  print "OK\n";
}
else {
  print $valid;
}
