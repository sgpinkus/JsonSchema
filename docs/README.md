# Overview
Draft v4 compliant JSON Schema validator for PHP.

  * Simple design. In particular, the separation of code concerned with loading JSON, and JSON Reference, and code concerned with validation.
  * Simple interface for validation - doesn't expose the user to more than a couple of classes for the main use case - validation.
  * Support for `$refs`, and the `id` keyword, following [this amendment](https://github.com/json-schema/json-schema/wiki/The-%22id%22-conundrum#how-to-fix-that) to the unimplementable spec.
  * Easily extensible with custom constraints.
  * Draft 4 compatible only.
  * No support for the hypermedia validation / semantic validation or whatever it is (the 3rd part). Should be easy to add support for this later.

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

# UnImplemented Constriaints

  * dependencies
  * date-time
  * email
  * hostname
  * ipv4
  * ipv6
  * uri

# Usage

    <?php
    require_once 'loader.php';
    use JsonDocs\JsonDocs;
    use JsonDocs\JsonLoader;
    use JsonDocs\Uri;
    use JsonSchema\JsonSchema;
    use JsonSchema\Constraint\ValidationError;
    assert($argc == 3) or die();
    // JsonDocs does the dereferencing, and acts as a cache of JSON docs.
    $jsonDocs = new JsonDocs(new JsonLoader());
    // Build validator, and dereference JSON Doc. JsonDocs would attempt to if 2nd arg not passed.
    $schema = new JsonSchema($jsonDocs->get(new Uri("file:///tmp/target.json"), json_decode(file_get_contents($argv[1]))));
    $valid = $schema->validate(json_decode(file_get_contents($argv[2])));
    if($valid === true) {
      print "OK\n";
    }
    else {
      print $valid;
    }


Also see [ValidateFile.php](tests-informal/ValidateFile.php), [JsonSchemaTest.php](tests-informal/JsonSchemaTest.php)

# TODO
See [TODO](docs/TODO.md)
