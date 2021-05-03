# Overview
There are these main places we may deviate from the spec:

  - `id` or `$id` keywords.
  - The precise meaning of `additionalProperties` and `additionalItems`.
  - Some `format` formatters.

## `$id` not `id`
`id` is not a keyword (as in v4). `$id` is (as in (v6+). However, we don't implement relative URI rebasing as it's confusing and *never* actually used in the wild. See next section.

## The `$id` or  Keyword Does Not Establish a Base URI
JSON Schema defines a way to [establish a base URI](http://json-schema.org/latest/json-schema-core.html#anchor27), for resolution of relative URIs in a `$ref` object (JSON Schema calls this "defining a new resolution scope"). Specifically, JSON Schema says the `$id` field is used to establish the base URI of all descendant objects for which the given id is the closest ancestor id.

However, the spec is ambiguous and attempting to follow it or something like leads to issues, well covered in [this proposed amendment](https://github.com/json-schema/json-schema/wiki/The-%22id%22-conundrum#how-to-fix-that). In summary, this implementation uses [JsonRef](http://jsonref.org) which uses json reference v0.4.0.

Example of inline dereferencing with `$id`:

    {
      {
        "$id": "#foo",
        ...
      }
      {
        "bah": { "$ref": "#foo" }
        ...
      }
    }

The value of bah is replaced entirely by the object `id: "#foo"` is contained in.

## addtionalItems, additionalProperties
(Note we pass all the official tests on these properties, so need to reconfirm we are actually deviant).

On additionalItems the spec [states]:

    "Successful validation of an array instance with regards to these two keywords is determined as follows: ...
      - If the value of "additionalItems" is boolean value true or an object, validation of the instance always succeeds;"

It states the analogous rule for additionalProperties. This implies if adddtionalItems|Properties is true the related keywords are irrelevant. We do not implement additionalItems, or additionalProperties this way. We think that behavior would not be useful and would be confusing. Instead:

  * additional(Items|Properties) may be true, false or a a valid JSON Schema object.
  * additional(Items|Properties) *only* take effect *after* items|(properties|propertyPatterns) have been applied.
  * Any *additional* item|property not matched by items|(properties|propertyPatterns) are accounted for by additional(Items|Properties).
    - If additional(Items|Properties) is a schema then the items must match the schema.
    - If additional(Items|Properties) is false then validation fails if there are any additional items|properties.
    - true and {} are equivalent values for additional(Items|Properties).

## format
JSON Schema requires the formats it defines match specific existing RFCs or specifications. For example `regex` must be a ECMA regex. We use the closest approximation we could find in the native PHP (>=5.4) libs. Currently:

  * `uri` === parse_url() or fail.
  * `regex` === compile with preg_match() or fail.
