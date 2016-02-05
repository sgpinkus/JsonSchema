# Overview
Draft v4 compliant JSON Schema validator for PHP.

  * Simple design. In particular, the separation of code concerned with loading JSON, and JSON Reference, and code concerned with validation.
  * Simple interface for validation - doesn't expose the user to more than a couple of classes for the main use case - validation.
  * Support for `$refs`, and the `id` keyword, following [this amendment](https://github.com/json-schema/json-schema/wiki/The-%22id%22-conundrum#how-to-fix-that) to the unimplementable spec.
  * Easily extensible with custom constraints.
  * Draft 4 compatible only.
  * No support for the hypermedia validation / semantic validation (the 3rd part of the v4 spec). Should be easy to add support for this later.

# Implemented Constraints.

  * multipleOf
  * maximum+exclusiveMaximum
  * minimum+exclusiveMinimum
  * maxLength
  * minLength
  * pattern
  * items+additionalItems
  * maxItems
  * minItems
  * uniqueItems
  * maxProperties
  * minProperties
  * required
  * properties+additionalProperties+patternProperties
  * enum
  * type
  * allOf
  * anyOf
  * oneOf
  * not
  * format

# UnImplemented Constraints

  * dependencies

# Usage
In the simplest case, where you have a standalone JSON schema with no `$refs`:

```php
<?php
require_once 'loader.php'; // Or any PSR-4 loader.
assert($argc == 3) or die("Usage " . basename(__file__) . " <schema-file> <json-file>\n");
$schema = new JsonSchema\JsonSchema(json_decode(file_get_contents($argv[1])));
$valid = $schema->validate(json_decode(file_get_contents($argv[2])));
if($valid === true) {
  print "OK\n";
}
else {
  print $valid;
}
```

If you have any `$refs` in your JSON schema, you need to use the `JsonDocs` wrapper class to load and deref the JSON schema documents:

```php
<?php
require_once 'loader.php'; // Or any PSR-4 loader.
assert($argc == 3) or die("Usage " . basename(__file__) . " <schema-file> <json-file>\n");
// JsonDocs does the dereferencing, and acts as a cache of JSON docs.
$jsonDocs = new JsonDocs\JsonDocs(new JsonDocs\JsonLoader());
// Build validator, and dereference JSON Doc. JsonDocs would attempt to load from URI if 2nd arg not passed.
$schema = new JsonSchema\JsonSchema($jsonDocs->get(new JsonDocs\Uri("file:///tmp/fake.json"), file_get_contents($argv[1])));
$valid = $schema->validate(json_decode(file_get_contents($argv[2])));
if($valid === true) {
  print "OK\n";
}
else {
  print $valid;
}
```

Also see [ValidateFile.php](tests-informal/ValidateFile.php), [JsonSchemaTest.php](tests-informal/JsonSchemaTest.php)

# TODO
See [TODO](docs/TODO.md)
