<?php
namespace JsonDoc;

use JsonDoc\JsonCache;
use JsonDoc\JsonLoader;
use JsonDoc\Uri;

/**
 * A thin facade over JsonCache so clients dont have to care about how that works.
 * @todo iterator.
 */
class JsonDoc
{
  private $jsonCache;
  private $baseUri;
  private $doc;

  /**
   * Init.
   * @throws ResourceNotFoundException
   */
  public function __construct(Uri $uri, $jsonCache = null) {
    $this->jsonCache = $jsonCache ? $jsonCache : new JsonCache(new JsonLoader());
    $this->doc = $this->jsonCache->get($uri);
    $this->baseUri = JsonCache::normalizeKeyUri($uri);
  }

  /**
   * Resolve pointer to reference into doc.
   * @input $pointer a JSON Pointer.
   * @returns mixed.
   * @throws ResourceNotFoundException
   */
  public function &pointer($pointer) {
    $uri = clone $this->baseUri;
    $uri->fragment = $pointer;
    return $this->jsonCache->pointer($uri);
  }

  /**
   * Same as pointer("").
   */
  public function &getDoc() {
    return $this->doc;
  }
}
