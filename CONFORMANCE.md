# Overview
There are two place we deviate from the spec. `$ref`+`id`, and the meaning of `addtionalProperties` and `additionalItems`

## The `id` Keyword Does Not Establish a Base URI
JSON Schema defines a way to [establish a base URI](http://json-schema.org/latest/json-schema-core.html#anchor27), for resolution of relative URIs in a $ref object (JSON Schema calls this "defining a new resolution scope"). Specifically, JSON Schema says the "id" field is used to establish the base URI of all descendent objects for which the given id is the closest ancestor id. However, the spec is ambiguous and attempting to follow it or something like leads to issues, well covered in [this proposed amendment](https://github.com/json-schema/json-schema/wiki/The-%22id%22-conundrum#how-to-fix-that). In summary:

  * `id` at the root level if present shall establish a base URI for the document. It SHOULD be a valid URI.
  * Non root `id` fields shall *not* establish a new Base URI. The base URI concept is *deprecated*.
  * Non root `id` fields establish targets for linking to via $refs (similar to `id` in XSD, except they can occur at any level). This is what the v4 spec means by "inline dereferencing mode" - as far as we can tell.
  * Non root `id` fields must be unique and must not start with "/".

Example of inline dereferencing with `id`:

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

The value of `bah is replaced entirely by the object `id: "#foo"` is contained in.

## Limitations to $ref
The way $ref is intended in Json Schema is more or less like a UNIX symlink. However the spec does not address how to handle certain situations that can arise (like loops). In this implementation $refs are completely replaced by native PHP references. This is done in a preprocessing step by a submodule `JsonDocs`. The limitations to $refs are discussed in [JsonDocs/README.md](JsonDocs/README.md). Basically $refs to $refs (including but not limited to loops) are not allowed.

## addtionalItems, additionalProperties
On additionalItems the spec [states]:

    "Successful validation of an array instance with regards to these two keywords is determined as follows: ... . If the value of "additionalItems" is boolean value true or an object, validation of the instance always succeeds;"

It states the analogous rule for additionalProperties. We do not implement additionalItems, or additionalProperties this way. We think this behavior is not useful and confusing. Instead.

  * additional(Items|Properties) may be true, false or a a valid JSON Schema object.
  * additional(Items|Properties) *only* take effect *after* items|(properties|propertyPatterns) have been applied.
  * Any *additional* item|property not matched by items|(properties|propertyPatterns) are accounted for by additional(Items|Properties).
    - If additional(Items|Properties) is a schema then the items must match the schema.
    - If additional(Items|Properties) is false then validation fails if there are any additional items|properties.
    - true and {} are equivalent values for additional(Items|Properties).

## format
JSON Schema requires the formats it defines match specific existing RFCs or specifications. For example `regex` must be a ECMA regex. We use the closest approximation we could find in the native PHP (>=5.4) libs. Currently:

  * `date-time` seems to be less strict than required by the JSON Schema spec.
  * `uri` === parse_url() or fail.
  * `regex` === compile with preg_match() or fail.
