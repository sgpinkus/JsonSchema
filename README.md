# Purpose
To implement a full functional JSON Schema implementation in PHP. Of those PHP libs listed at http://json-schema.org, https://github.com/justinrainbow/json-schema seemed to be the most full featured. But even this lib only partially implments "$refs" and does not validate the schema its using for validation. There seems to be a number of other open issues with it too.

# Major Requirements & Constraints

  * Complete support for $refs, and the id keyword except for the next point.
  * Do not support "inline dereferencing" option. Its a stupid extension to the spec that does not add anything but confusion.
  * Draft 4 compatible only.
  * Well thought out design. Separation of JSON Reference and JSON Schema.
  * Simple interface for validation - don't expose the user to more thatn a couple of classes at the most for the main use cases.
  * Initially no support for the hypermedia validation or semantic validation or whatever it is. Allow for easy extensions for arbitrary new constraints.

# Analysis & Design

## JSON Schema
Some things to note about JSON Schema:

  1. This is a valid constraint. Its the empty constraint:

      {}

  2. Constraints extend the empty constraint in that all constraints are objects, with additional fields. Example:

      {
        "title": "All of nothing",
        "allOf": [{}]
      }

  3. Certain constraints are recursive. Example the object, and array type constraints, allOf, anyOf.

  4. A JSON Schema document can be a mix of JSON defined constraints and arbitrary fields. Its kind of strange but that is how it is. Valid JSON which can be addressed via JSON Pointer can be nested at arbitrary depth in a document. See http://json-schema.org/latest/json-schema-core.html#anchor7.

  5. A given level (by that I mean nesting level in a JSON obect) of a valid JSON Schema represents a single constraint on a single value of the target JSON document, except in the case that the level is a sublevel of some other JSON Schema constraint. This is true even for parts of the json schema doc that are not intended to be constraints - since `{}` is a valid constraint. Example given:

      {
        "SomeSchemas" : {
          "PartA" { ... },
          "PartB" { ... }
        },
        "MoreSchemas" {
          "Foo": { ... },
          "Bah": { ... }
        }
      }

    The doc at fragment addresss `#MoreSchemas` is a constraint equivalent to the empty constraint.

## JSON Schema Semantics
How do we interpret this seemingly contradictory schema?:

    {
      "type": "object",
      "minimum": 3,
      "pattern": "foo"
    }

Upon initial reading of the spec one might draw the conclusion that "minimum" for example is only relevant when "type" is "number" or "integer". That is, "minimum" is a keyword that is only relevant in this circumstance and does not represent a standalone constraint. However this is not the intention of the spec. The above schema specifies 3 cosntraints. Many constraints are only relevant to certain type. For instance the constraint "minimum" is only relvant to numeric types, and succeeds  (or alternatively is simply ignored) when the type being validated is not numeric. This is specified some what ambiguously in [JSON Schema Validation, Section 4.1](http://json-schema.org/latest/json-schema-validation.html#anchor8).

## JSON References - Handling $refs
JSON References reference a value. The entire ref object is replaced by the value. The JSON Schema document and the validator that will be generated from it are separate things. Handling references is doen in an initial phase. The generation of a validator is done from a JSON document is done on the in memory deserialized version of a JSON document and has no concept of a JSON $ref. With the exception that where ever a ref occured there is now a native PHP reference.

## Generating a Parser/Validator
The implementation is simplified if we allow mutatation of the parsed JSON Schema document, pegging the corresponding generated validator code to the document. For two main reasons:

  1. We need to support JSON Pointers to arbitrary bit of valid JSON Schema that can be used to validate documents out of context of the original document.
  2. JSON Pointer leads to references. Its possible, after $refs in the JSON Schema has been resolved, many references to the same part of the document exist. We don't want to generate parser code for each one. Marking up the document with generated code is a simple way of caching the relevatn generated code so that next time the document segment is traversed we can detect the previous code and avoid generating it again.

## Pointers To Validators
What gets pegged to the document and is thus addressable, what doesn't? JSON Schema is a recursive language. In short only objects are addressable. Given:

    { a: { allOf: [{minimum: 3}, {maximum: 4}]}

    /           VALID
    /a          VALID
    /a/allOf    INVALID
    /a/allOf/0  VALID

## Validators
The following constriants will be represented by classes:

    type
    enum
    allOf
    anyOf
    oneOf
    not
    empty
    multipleOf
    maximum
    minimum
    maxLength
    minLength
    pattern
    ...

## Validator/Generator Class Design
A JSON Document represents a schema. This schema is parsed into a validator. In this class library, the validator and the code that generates a validator from a JSON document are coupled into the same class. This is somewhat inflexible but its also simple.

Generating a constraint from a part of a JSON schema doc is done via the static method build(). In general this is because construction need not necessarily be from a document. Tempting to KISS and just stick it in the constructor. Currently I have one concrete use case for not putting the parser logic in the constructor. TypeConstraint reuses a OneOfConstraint. But doing it this way we have an issue of duplicating validation logic between the constructor and the build method.

Let build()  containt all document parsing logic in general. Let the constructor handle validation of it's parameters. There is still an issue with this. The constructor validation may occlude the nature of an exception.
