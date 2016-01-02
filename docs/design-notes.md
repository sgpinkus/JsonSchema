# Objective
To implement a full functional JSON Schema validator in PHP.

#  Analysis of Alternatives
Of those PHP libs listed at http://json-schema.org, https://github.com/justinrainbow/json-schema seems to be the most full featured. But even this lib only partially implements "$refs" and does not seem to check the validity of the json its using for validation. There seems to be a number of other open issues with it too.

# Major Requirements & Constraints
The major requirements and constraints are listed in [README.md](/README.md#Overview). Basically I wanted a fully functional simple and elegant class library.

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

  4. A JSON Schema document can be a mix of JSON defined constraints and arbitrary fields. Its kind of strange but that is how it is. Valid JSON which can be addressed via JSON Pointer can be nested at arbitrary depth in a document. See http://json-schema.org/latest/json-schema-core.html#anchor7.

  5. How do we interpret this seemingly contradictory schema?:

    {
      "type": "object",
      "minimum": 3,
      "pattern": "foo"
    }

    Upon initial reading of the spec one might draw the conclusion that "minimum" for example is only relevant when "type" is "number" or "integer". That is, "minimum" is a keyword that is only relevant in this circumstance and does not represent a standalone constraint. However this is not the intention of the spec. The above schema specifies 3 constraints. Many constraints are only relevant to certain type. For instance the constraint "minimum" is only relevant to numeric types, and is ignored (or equivalently always succeeds) when the type being validated is not numeric. This is specified some what ambiguously in [JSON Schema Validation, Section 4.1](http://json-schema.org/latest/json-schema-validation.html#anchor8).

## On "id" JSON Reference & JSON Pointer
JSON Schema refers to [JSON Reference](http://tools.ietf.org/html/draft-pbryan-zyp-json-ref-03) which in turn refers to [JSON Pointer](http://tools.ietf.org/html/draft-ietf-appsawg-json-pointer-04). JSON Reference defines the semantics of the  `{ $ref: ... }` object. JSON Pointer defines the semantics of pointers which may *only* occur in the *fragment* of a JSON Reference URI. JSON Schema itself states that JSON References are allowed, and additionally defines a way to [establish a base URI](http://json-schema.org/latest/json-schema-core.html#anchor27), for resolution of relative URIs in a $ref object (JSON Schema calls this "defining a new resolution scope"). Specifically, JSON Schema says the "id" field is used to establish the base URI of all descendent object for which the given id is the closest ancestor id. The URI specification already rigorously defined an algorithm for ["Establishing a Base URI"](http://tools.ietf.org/html/rfc3986#section-5.1). Unfortunately the JSON Schema specification does not explicitly refer to it. They *do* however state that the id field is a URI, and indeed a base URI. So one has to defer to the URI specification where the JSON Schema spec is ambiguous, which is exactly what we do.

Long story short. Forget what the Json Schema spec says here. Its twisted and ambiguous. Follow this - https://github.com/json-schema/json-schema/wiki/The-%22id%22-conundrum#how-to-fix-that:

  * `id` at the root level if present shall establish a base URI.
  * Non root `id` fields shall not establish a new Base URI!
  * Non root `id` fields shall be like `id` in XSD. That is they establish ids that can be referenced with a `$ref`.

Example:

  {
    {
      id: "#foo",
      ...
    }
    {
      bah: { $ref: "#foo" }
      ...
    }
  }

Does what you think it does.

There are still more issues with JSON Schema references and JSON pointers not well addressed in the spec. Specifically:

  1. How to handle refs that you can't be resolved.
  2. A chain of refs - refs to ref to ref to ...
  3. Loops in refs.
  4. A ref through another ref.

Consider how XSD handled refs. References must reference elements at the top level of a document. These elements must not be refs and must have unique ids. Simple. Serves 99% of all use cases.

## On Constraints - Interpreting the spec
Some ambiguity in the spec on the following properties. Design decisions outline in the following.

### Properties, PatternProperties, AdditionalProperties
This trio has to be considered together, they are interdependent. There is IMO some ambiguity around `additionalProperties`. The spec states "if its value is boolean true or a schema, validation succeeds". But its not clear whether they are talking about `additionalProperties` in isolation or the trio. I take it they are talking about the property in isolation. Its not useful and very unintuitive to have `additionalProperties` override what is specified by properties, and patternProperties.

The specification does clearly describe a precedence order with `properties` being applied to properties first, then `patternProperties` applied to matching properties. `additionalProperties` naturally applies to the rest. That makes sense to me.

### Items, AdditionalItems
Same ambiguity as for additionalProperties. I take it `additionalItems` only applies to items not caught by items. Note this entails additionalItems is irrelevant it items is an object, since there are no additional properties.

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
