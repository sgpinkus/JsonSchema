# Objective
To implement a full functional JSON Schema validator in PHP.

#  Analysis of Alternatives
Of those PHP libs listed at http://json-schema.org, https://github.com/justinrainbow/json-schema seems to be the most full featured. But even this lib only partially implements "$refs" and does not seem to check the validity of the JSON its using for validation. There seems to be a number of other open issues with it too.

# Major Requirements & Constraints
The major requirements and constraints are listed in [README.md](/README.md#Overview). Basically we wanted a fully functional simple and elegant class library.

# Analysis & Design

## On JSON Schema
Json Schema has a simple and elegant design but some parts are ambiguous or at least vulnerable to misinterpretation. Some things to note about JSON Schema:

  1. This is a valid constraint. Its the empty constraint:

      {}

  2. Constraints extend the empty constraint in that all constraints are objects, with additional fields. Example:

      {
        "title": "All of nothing",
        "allOf": [{}]
      }

  3. Certain constraints are recursive. Example: object, array type constraints, allOf, anyOf.

  4. A JSON Schema document can be a mix of JSON defined constraints and arbitrary fields. Valid JSON which can be addressed via JSON Pointer can be nested at arbitrary depth in a document. See http://json-schema.org/latest/json-schema-core.html#anchor7.

  5. How do we interpret this seemingly contradictory schema?:

    {
      "type": "object",
      "minimum": 3,
      "pattern": "foo"
    }

    Upon initial reading of the spec one might draw the conclusion that "minimum" for example is only relevant when the "type" keyword is "number" or "integer". That is, "minimum" is only relevant in this circumstance and does not represent a standalone constraint. However this is not the intention of the spec. The above schema specifies 3 standalone constraints. Many constraints are only relevant to certain type. For instance the constraint "minimum" is only relevant to numeric types, and is ignored (or equivalently always succeeds) when the type being validated is not numeric. This is specified some what ambiguously in [JSON Schema Validation, Section 4.1](http://json-schema.org/latest/json-schema-validation.html#anchor8).

## On "id" JSON Reference & JSON Pointer
`PHPJsonSchema` should have no idea what `$ref` is. Resolving refs to plain old PHP refs is handled completely in a pre processing stage. This separation of concerns simplifies things. Never the less there are issues and ambiguities in the JSON Schema spec with respect to `$ref`. Refer to [JsonDocs](JsonDocs/README.md).

## On Constraints - Interpreting the spec
Some ambiguity in the spec on the following properties. Design decisions outline in the following.

### Properties, PatternProperties, AdditionalProperties
This trio has to be considered together, they are interdependent. There is in our opinion some ambiguity around `additionalProperties`. The spec states

    "if its value is boolean true or a schema, validation succeeds".

But its not clear whether they are talking about `additionalProperties` in isolation or the trio ass a whole. I take it they are talking about the property in isolation. Its not useful and very unintuitive to have `additionalProperties` override what is specified by `properties`, and `patternProperties`.

The specification does clearly describe a precedence order with `properties` being applied to properties first, then `patternProperties` applied to matching properties. `additionalProperties` naturally applies to the rest. That makes sense to me.

### Items, AdditionalItems
Same ambiguity as for `additionalProperties` exists for `additionalItems`. I take it `additionalItems` only applies to items *not* caught by `items`. Note this entails additionalItems is irrelevant if items is an object, since there are no additional properties.

## Parsing JSON References - { $ref: ... }
Some notes on [JSON Reference](http://tools.ietf.org/html/draft-pbryan-zyp-json-ref-03).

  * A JSON Ref has the form `{ $ref: <ref> }`, where <ref> is a valid URI. Everything but `$ref` in the reference object is ignored.
  * A JSON Reference may reference a value in some JSON document.
  * The entire ref object is replaced by its value.

The JSON Schema document and the validator that will be generated from it, should be treated separate entities. Dereferencing should be done in an initial pass in order to separate concerns. The validator generator need not know or care about JSON Reference. Note however, where ever a ref occurred there will be a native PHP reference, so the presence of a reference in the underlying JSON Schema document is detectable in this way.

## Generating a Validator
The implementation is simplified if we allow mutation of the parsed JSON Schema document in order to "peg" the corresponding generated validator code to the document. For two main reasons:

  1. We need to support JSON Pointers to arbitrary bits of valid JSON Schema that can be used to validate documents out of context of the original document.
  2. JSON Pointer leads to references. Its possible, after $refs in the JSON Schema have been resolved, that many references to the same part of the document exist. We don't want to generate parser code for each one. Marking up the document with generated code is a simple way of caching the relevant generated code so that next time the document segment is traversed we can detect the previous code and avoid generating it again.

## Validators
All validators / constraints shall be represented by classes.

## Validator/Generator Class Design
A JSON Document represents a schema. This schema is parsed into a validator. In this class library, the validator and the code that generates a validator from a JSON document are coupled into the same class. This is somewhat inflexible but its also simple.

Generating a constraint from a part of a JSON schema doc is done via the static method build(). In general this is because construction need not necessarily be from a document. Tempting to KISS and just stick it in the constructor. Currently I have one concrete use case for not putting the parser logic in the constructor. TypeConstraint reuses a OneOfConstraint. But doing it this way we have an issue of duplicating validation logic between the constructor and the build method.

Let build() contain all document parsing logic in general. Let the constructor handle validation of it's parameters. There is still an issue with this. The constructor validation may occlude the nature of an exception.
