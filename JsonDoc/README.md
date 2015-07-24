# Overview
PHP wrapper over the plain old data structure returned by json_decode() to implement [Json Reference](https://tools.ietf.org/html/draft-pbryan-zyp-json-ref-03) and by extension [Json Pointer](https://tools.ietf.org/html/draft-ietf-appsawg-json-pointer-04) (Json Reference requires Json Pointer).

# Analysis & Design

## JSON Reference
The Json Pointer specification is written without respect to JSON Reference. JSON References are JSON Pointers. However JSON Pointer cannot be specified independently. Specifically we need to answer the question; what happens when a JSON Pointer tries to point through a reference? The simple answer is that since JSON Pointer does not know what a JSON Reference is, it treats the JSON Reference no differently to normal JSON. However, this is rather unsatisfactory. This library recursively resolves JSON References as you'd expect and detects loops as you'd expect. This library maintains at all times a Base URI as required by JSON Reference. When a ref is encountered it is resolved with respect to the current base URI and loaded. A cache of looked up and loaded schemas is maintained.

The point in wanting to resolve references is to end up with a JSON Document you can use for other things. Say for example generate a parser for JSON Schema. We don't want the application to have to deal with the complexities of dereferencing a JSON doc. Dereferencing may be an expensive thing to do, but we take the stand point that it is just simpler (saner) to get it done and move on rather than trying to do it iteratively and expose the application to it.

Requirement 0: The application may have a needing to know that - as it traversing a JSON Doc - its just followed a reference somewhere into another doc:
Use Case Scenario: A parser has already worked on the sub tree at the pointer and does not want to duplicate that work.

## Loading & Caching
Two important requirements:

  1. We need a essentially a cache of individual JSON schema documents we have loaded. The cache will be keyed by the URI a given document is retreived from.
  2. We want to give the user control of loading if for some reason it wants to override behavior. This may be important for security. On the other hand we want thing to be simple so there should be a default loader.

## Resolution
Json documents shall be simply resolved and returned to the client. References are lost and replaced entirely with native language references. The id field cannot be reused after compilation of the document. To account for Requirement 0 we make the JSON document mutable. The applicaton / parser can mark up the document however it pleases to aid what ever the heck its doing.

A caveate to this is this. Documents are loaded from external sources, then they are mutated. Before the loaded resource can be usefully made available to the client it needs to be 1. decoded 2. dereferenced. Once its dereferenced there is a complex graph of dependencies between what were independently loaded resources. The whole thing has to be taken as one. The client has to take the whole cache. The given client then goes about mutating bits and pieces of the tree structure as it sees fit so the cache can't (safely) be reused by a separate client.

## Classes

*JsonLoader*
Load documents from remote source only. This object returns raw data not decoded JSON. Acts as a shim to allow client to override loading.

*JsonCache*
The operative class. A cahce of JSON docs keyed by the fully qualified URI the doc was loaded from. Loads decoded, dereferenced JSON documents.

*JsonDoc*
Thin wrapper over a plain old object returned by json_decode(). Delegates to the loader for loading remote resources.
