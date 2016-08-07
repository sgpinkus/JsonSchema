# Overview [![Build Status](https://api.travis-ci.org/sam-at-github/PhpJsonSchema.png)](https://travis-ci.org/sam-at-github/PhpJsonSchema)
Draft v4 compliant JSON Schema validator for PHP.

  * Simple design. In particular, the separation of code concerned with loading JSON, and JSON Reference, and code concerned with validation.
  * Simple interface for validation - doesn't expose the user to more than a couple of classes for the main use case -- validation.
  * Support for `$refs`, and the `id` keyword, following [this amendment](https://github.com/json-schema/json-schema/wiki/The-%22id%22-conundrum#how-to-fix-that) to the unimplementable spec.
  * Easily extensible with custom constraints.
  * Draft 4 compatible only.
  * No explicit support for the hypermedia validation / semantic validation (the 3rd part of the v4 spec).

# Usage
In the simplest case, where you have a standalone JSON schema with no `$refs`:

```php
<?php
require_once './vendor/autoload.php';
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
$schema = new JsonSchema\JsonSchema(json_decode('{
  "type": "object",
  "properties": {
    "firstName": { "type": "string", "minLength": 2 },
    "lastName": { "type": "string", "minLength": 2 },
    "email": { "type": "string", "format": "email" },
    "_id": { "type": "integer" }
  },
  "required": ["firstName", "lastName", "email", "_id"]
}'));
foreach(['/users/0', '/users/1', '/'] as $ptr) {
  $valid = $schema->validate(JsonDocs\JsonDocs::getPointer($json, $ptr));
  if($valid === true) {
    print "OK\n";
  }
  else {
    print $valid;
  }
}
```

If you have any `$refs` in your JSON schema, you need to use the `JsonDocs` wrapper class to load and deref the JSON schema documents:

```php
<?php
require_once './vendor/autoload.php';
use JsonDocs\JsonDocs;
use JsonDocs\JsonLoader;
use JsonDocs\Uri;
use JsonSchema\JsonSchema;
$json = '{}';
$schema = '{}';
// JsonDocs does the dereferencing, and acts as a cache of JSON docs.
$jsonDocs = new JsonDocs(new JsonLoader());
// Build validator, and dereference JSON Doc. JsonDocs would attempt to load from URI if 2nd arg not passed.
$schema = new JsonSchema($jsonDocs->get(new Uri("file:///tmp/fake.json#/users/0"), $schema));
$valid = $schema->validate(json_decode($json));
if($valid === true) {
  print "OK\n";
}
else {
  print $valid;
}
```

Also see [validate-file.php](utils/validate-file.php) for example code.

# Implemented Constraints
All v4 Constraints are implemented. Some constraints were implemented with minor deviations from the spec. Please see [CONFORMANCE.md](CONFORMANCE.md). The `constant`, and `switch` v5 *proposals* have also been implemented.


# TODO
See [TODO](TODO.md)
