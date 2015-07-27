<?php
require_once '../loader.php';
use JsonSchema\JsonSchema;
use JsonSchema\Constraint\EmptyConstraint;
use JsonDocs\JsonDocs;
use JsonDocs\Uri;
use JsonDocs\JsonDocs;

$JsonDocs = new JsonDocs(new JsonLoader());
$doc = JsonDocs->get(new Uri("file:///" . dirname(__FILE__) . "/test-data/simple-schema.json"));
$schema = new JsonSchema($doc);
var_dump($schema->validate(json_decode("{}")));
var_dump($schema->validate(json_decode("{}"), "/definitions"));
var_dump($schema->validate(json_decode("{}"), "/definitions/positiveInteger"));
var_dump($schema->validate(-1, "/definitions/positiveInteger"));
var_dump($schema->validate(1, "/definitions/positiveInteger"));
$direct = JsonDocs::getPointer($doc, "/definitions/positiveInteger/$code");
var_dump($direct->validate(-1));
