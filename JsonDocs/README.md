# Overview
PHP wrapper over the plain old data structure returned by json_decode() to implement [Json Reference](https://tools.ietf.org/html/draft-pbryan-zyp-json-ref-03) and by extension [Json Pointer](https://tools.ietf.org/html/draft-ietf-appsawg-json-pointer-04) (Json Reference requires Json Pointer). This library simply resolves JSON References to native PHP references. It does this on an existing decode JSON document data structure, or loads and decodes the document from a URI.

# Usage

    use JsonDocs\JsonDocs;
    use JsonDocs\JsonLoader;
    use JsonDocs\Uri;
    $jsonDocs = new JsonDocs(new JsonLoader());
    $doc = $jsonDocs->get(new Uri('file://' . realpath('./tests/test-data/basic-refs.json')));
    var_dump($doc);
    # Or if the doc is already decoded.
    $unDoc = json_decode(file_get_contents(realpath('./tests/test-data/basic-refs.json')));
    $doc = $jsonDocs->get(new Uri('file:///tmp/still/need/some/baseuri'), $unDoc);
    var_dump($doc);

# Analysis & Design

## Class Structure
`JsonDocs\JsonDocs` is the operative class of this library. All the rest just support it.

A JSON reference can reference parts of the document it exists in or external JSON documents that need to be loaded. For this reason, in order to keep the referencing procedure atomic, loading of resources needs to be done in the dererencing operation as references to external resources are found. These externally loaded documents need to stored somewhere. `JsonDocs` maintains an internal cache. Beware that the entire document (or parts of it if the original source document is passed in) exist in this cache, to which a reference is returned.

## Limitations
Three main issues with JSON References / JSON Pointers:

  1. Pointers to pointers.
  2. Pointers to pointers that cause a loop.
  3. Pointers that point through other pointers.

Currently this library addresses the the first two problems by not allowing pointer to pointers at all. However, the references are resolved in a predictable order so if the referee is resolved before the referer, references to references works. As for the 3rd, if a pointer is encountered in a path an exception is thrown, because the pointer is invalid. But resolution order is important here too so a pointer through a pointer may work, but this is not guaranteed to. References are resolved in order of depth they exist at in the document, with shallowest first, and alphabetical if depth equal.
