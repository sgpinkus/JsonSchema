<?php
require_once '../loader.php';
use JsonSchema\Constraint\EmptyConstraint;
$schemaDoc = '{
  "oneOf": [{},{},{}]
}';
$targetDoc = '{}';
$schemaDoc = json_decode($schemaDoc);
$constraint = EmptyConstraint::build($schemaDoc);
var_dump($constraint->validate($targetDoc));
