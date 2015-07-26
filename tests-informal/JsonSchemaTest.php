<?php
require_once '../loader.php';
use JsonSchema\JsonSchema;
use JsonSchema\Constraint\EmptyConstraint;
use JsonDoc\JsonDoc;
use JsonDoc\Uri;
use JsonDoc\JsonPointer;

$code = '$code';
$doc = new JsonDoc(new Uri("file:///" . dirname(__FILE__) . "/test-data/simple-schema.json"));
$schema = new JsonSchema($doc->getDoc());
$doc = $doc->getDoc();
var_dump($doc->$code);
var_dump($doc->definitions->$code);
var_dump($doc->definitions->schemaArray->$code);
var_dump($doc->definitions->positiveInteger->$code);
var_dump(JsonPointer::getPointer($doc, "/definitions/positiveInteger/$code"));
print "#########\n";
var_dump($schema->validate(json_decode("{}")));
var_dump($schema->validate(json_decode("{}"), "/definitions"));
var_dump($schema->validate(json_decode("{}"), "/definitions/positiveInteger"));
var_dump($schema->validate(-1, "/definitions/positiveInteger"));
var_dump($schema->validate(1, "/definitions/positiveInteger"));
$direct = JsonPointer::getPointer($doc, "/definitions/positiveInteger/$code");
var_dump($direct->validate(-1));
