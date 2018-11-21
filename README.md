# Overview [![Build Status](https://api.travis-ci.org/sam-at-github/PhpJsonSchema.png)](https://travis-ci.org/sam-at-github/PhpJsonSchema)
Draft v4 compliant JSON Schema validator for PHP.

  * Modular design. In particular, separation of code concerned with loading JSON, JSON (de)referencing, and validation.
  * Simple interface for validation - doesn't expose the user to more than a couple of classes for the main use case -- validation.
  * JsonRef dereferencing is handled by an external PHP library [JsonDoc](https://github.com/sam-at-github/JsonDoc). You can easily replace it with a different one.
  * Easily extensible with custom constraints.
  * Draft 4 compatible only.
  * No explicit support for the hypermedia validation / semantic validation (the 3rd part of the v4 spec).

# Installation

    git clone ... && cd JsonSchema/
    composer update --no-dev --ignore-platform-reqs

# Usage
In the simplest case, where you have a standalone JSON schema with no `$refs`:

```php
<?php
require_once './vendor/autoload.php';
use JsonDoc\JsonDocs;
use JsonSchema\JsonSchema;

$json = json_decode('{
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
}');
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
foreach(['/users/0', '/users/1', '/'] as $ptr) {
  $valid = $schema->validate(JsonDocs::getPointer($json, $ptr));
  if($valid === true)
    print "OK\n";
  else
    print $valid;
}
```

If you have any `$refs` in your JSON schema, you need to use the `JsonDocs` wrapper class to load and deref the JSON schema documents:

```php
<?php
require_once './vendor/autoload.php';
use JsonDoc\JsonDocs;
use JsonDoc\JsonLoader;
use JsonDoc\Uri;
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
// JsonDocs does the dereferencing, and acts as a cache of loaded JSON docs.
$jsonDocs = new JsonDocs(new JsonLoader());
$schema = new JsonSchema($jsonDocs->loadDocStr($schema, new Uri('file:///tmp/some-unique-name')));
$valid = $schema->validate($json);
if($valid === true)
  print "OK\n";
else
  print $valid;
```

Also see [validate-file.php](utils/validate-file.php) for example code.

# Implemented Constraints
All v4 Constraints are implemented. Some constraints were implemented with minor deviations from the spec. Please see [CONFORMANCE.md](CONFORMANCE.md). The `constant`, and `switch` v5 *proposals* have also been implemented.

# TODO
See [TODO](TODO.md)
