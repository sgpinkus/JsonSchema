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
