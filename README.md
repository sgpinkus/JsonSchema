# Overview [![Build Status](https://api.travis-ci.org/sgpinkus/JsonSchema.png)](https://travis-ci.org/sgpinkus/JsonSchema)
Draft v6 compliant JSON Schema validator for PHP:

  * Modular design.
  * Simple interface for validation.
  * JsonRef dereferencing is handled by an external PHP library [JsonRef](http://jsonref.org). You can easily replace it with a different one.
  * Easily extensible with custom constraints.
  * Draft v4 compatible.

# Installation

    composer install sgpinkus/jsonschema

# Test

    git clone ... && cd JsonSchema
    git submodule update --init
    composer test

# Usage
In the simplest case, where you have a standalone JSON schema with no `$refs`:

```
<?php
require_once './vendor/autoload.php';
use JsonSchema\JsonSchema;
use JsonRef\JsonDocs;

$json = '{
  "users": [
    {
     "comment": "valid",
     "firstName": "John",
     "lastName": "Doe",
     "email": "john.doe@nowhere.com",
     "_id": 1
    },
    {
     "comment": "invalid",
     "firstName": "John",
     "lastName": "Doe",
     "email": "john.doe.nowhere.com",
     "_id": 2
    }
  ]
}';
$schema = '{
  "type": "object",
  "properties": {
    "firstName": { "type": "string", "minLength": 2 },
    "lastName": { "type": "string", "minLength": 2 },
    "email": { "type": "string", "format": "email" },
    "_id": { "type": "integer" }
  },
  "required": ["firstName", "lastName", "email", "_id"]
}';

$schema = new JsonSchema($schema);
$doc = json_decode($json);

// Validate whole doc.
$valid = $schema->validate($doc);
if($valid === true)
  print "OK\n";
else
  print $valid;

// Addendum: use JsonDocs::getPointer() to get a sub doc then validate it.
$valid = $schema->validate(JsonDocs::getPointer($doc, '/users/1'));
if($valid === true)
  print "OK\n";
else
  print "$valid";
```

If you have any `$refs` in your JSON schema, you need to use the `JsonRef` wrapper class to load and dereference the JSON schema documents:

```
<?php
require_once './vendor/autoload.php';
use JsonRef\JsonDocs;
use JsonSchema\JsonSchema;

$json = '{
  "comment": "valid",
  "firstName": "John",
  "lastName": "Doe",
  "email": "john.doe@nowhere.com",
  "_id": 1
}';
$schema = '{
  "id": "file:///tmp/jsonschema/user",
  "type": "object",
  "definitions" : {
    "_id" : { "type": "integer", "minimum": 0, "exclusiveMinimum": true },
    "commonName" : { "type": "string", "minLength": 2 }
  },
  "properties": {
    "firstName": { "$ref": "#/definitions/commonName" },
    "lastName": { "$ref": "#/definitions/commonName" },
    "email": { "type": "string", "format": "email" },
    "_id": { "$ref": "#/definitions/_id" }
  },
  "required": ["firstName", "lastName", "email", "_id"]
}';

// JsonDocs does the dereferencing, and also caches any loaded JSON docs. Without a loader it wont
// try to loader external resources though.
$jsonDocs = new JsonDocs();
$schema = new JsonSchema($jsonDocs->loadDocStr($schema, 'file:///tmp/some-unique-fake-uri'));
$valid = $schema->validate(json_decode($json));
if($valid === true)
  print "OK\n";
else
  print $valid;
```

To implement custom constraints extend the `Constraint` class and implement abstract methods, then
register the constraint when creating the `JsonSchema` instance:

```
<?php
require_once './vendor/autoload.php';
use JsonSchema\JsonSchema;
use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;
use JsonSchema\Constraint\ValidationError;

class ModuloConstraint extends Constraint
{
  private $modulo;

  private function __construct(int $modulo) {
    $this->modulo = $modulo;
  }

  public static function getName() {
    return 'modulo';
  }

  public function validate($doc, $context) {
    if(is_int($doc) && $doc % $this->modulo !== 0) {
      return new ValidationError($this, "$doc is not modulo {$this->modulo}", $context);
    }
    return true;
  }

  public static function build($context) {
    if(!is_int($context->modulo)) {
      throw new ConstraintParseException("The value of 'modulo' MUST be an integer.");
    }

    return new static($context->modulo);
  }
}

$doc = 7;
$schema = '{
  "type": "integer",
  "modulo": 2
}';
$schema = new JsonSchema($schema, ['ModuloConstraint']);
$valid = $schema->validate($doc);
if($valid === true)
  print "OK\n";
else
  print $valid;
```

Also see [cli-validator.php](cli-validator.php) for example code.

# TODO
See [TODO](TODO.md).

# CONFORMANCE
See [conformance notes](CONFORMANCE.md).
