# Overview
Draft v4 compliant JSON Schema validator for PHP.

  * Simple design. In particular, the separation of code concerned with loading JSON, and JSON Reference, and code concerned with validation.
  * Simple interface for validation - doesn't expose the user to more than a couple of classes for the main use case - validation.
  * Sane support for `$refs`, and the `id` keyword, following [this amendment](https://github.com/json-schema/json-schema/wiki/The-%22id%22-conundrum#how-to-fix-that) to the spec.
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
  * properties+additionalProperties
  * enum
  * type
  * allOf
  * anyOf
  * oneOf
  * not

# UnImplemented Constriaints

  * dependencies
  * patternProperties
  * date-time
  * email
  * hostname
  * ipv4
  * ipv6
  * uri

# Usage
See [JsonSchemaTest.php](tests-informal/JsonSchemaTest.php)

# TODO
See [TODO](docs/TODO.md)
