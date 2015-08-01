<?php
require_once '../loader.php';
use JsonSchema\Constraint\EmptyConstraint;
use JsonSchema\Constraint\ValidationError;

$schemaDocs = [
  '{"oneOf": [{},{}]}',
  '{"oneOf": [{"type": "number"},{"type": "string"}]}',
  '{"anyOf": [{"type": "number"},{"type": "string"}]}',
  '{"anyOf": [{"type": "number"},{"type": "object"}]}',
  '{"allOf": [{"type": "number"},{"type": "string"}]}'
];
$targetDoc = '{}';
foreach($schemaDocs as $schemaDoc) {
  $schemaDoc = json_decode($schemaDoc);
  $constraint = EmptyConstraint::build($schemaDoc);
  $validation = $constraint->validate($targetDoc);
  if($validation instanceof ValidationError) {
    print "Error: \n";
    print "  {$validation->getConstraint()->getName()}: {$validation->getMessage()}\n";
    foreach($validation as $v) {
      print "    {$v->getConstraint()->getName()}: {$v->getMessage()}\n";
    }
  }
}
