<?php
require_once '../loader.php';
use JsonSchema\JsonSchema;
use JsonSchema\Constraint\EmptyConstraint;
use JsonDocs\JsonDocs;
use JsonDocs\Uri;
use JsonDocs\JsonDocs;

$code = '$code';
$JsonDocs = new JsonDocs(new JsonLoader());
$doc = JsonDocs->get(new Uri("file:///" . dirname(__FILE__) . "/test-data/simple-schema.json"));
$schema = new JsonSchema($doc);
$doc = $doc->getDoc();
var_dump($doc->$code);
var_dump($doc->definitions->$code);
var_dump($doc->definitions->schemaArray->$code);
var_dump($doc->definitions->positiveInteger->$code);
var_dump(JsonDocs::getPointer($doc, "/definitions/positiveInteger/$code"));
print "#########\n";
var_dump($schema->validate(json_decode("{}")));
var_dump($schema->validate(json_decode("{}"), "/definitions"));
var_dump($schema->validate(json_decode("{}"), "/definitions/positiveInteger"));
var_dump($schema->validate(-1, "/definitions/positiveInteger"));
var_dump($schema->validate(1, "/definitions/positiveInteger"));
$direct = JsonDocs::getPointer($doc, "/definitions/positiveInteger/$code");
var_dump($direct->validate(-1));
