<?php
namespace JsonDoc;

use JsonDoc\JsonCache;
use JsonDoc\JsonLoader;
use JsonDoc\Uri;

/**
 * A thin facade over JsonCache so client dont have to care about how that works.
 * @todo iterator.
 */
class JsonDoc
{
  private $jsonCache;
  private $baseUri;
  public $doc;

  public function __construct(Uri $uri, $jsonCache = null) {
    $this->jsonCache = $jsonCache ? $jsonCache : new JsonCache(new JsonLoader());
    $this->doc = $this->jsonCache->get($uri);
    $this->baseUri = JsonCache::normalizeKeyUri($uri);
  }

  public function pointer($pointer) {
    $uri = clone $this->baseUri;
    $uri->fragment = $pointer;
    return $this->jsonCache->pointer($uri);
  }
}
