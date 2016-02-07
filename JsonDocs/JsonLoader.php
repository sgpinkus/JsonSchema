<?php
namespace JsonDocs;

use JsonDocs\Exception\ResourceNotFoundException;

/**
 * Shim interface to alow client to control loading.
 * Loads what should be a raw JSON doc given a URI. Limit ourselves to local file for now.
 * @todo add remote capable JsonLoader or add scheme det here.
 */
class JsonLoader
{
  /**
   * Load raw data from $uri. The data returned should be a JSON doc decodable with json_decode().
   * @throws ResourceNotFoundException.
   */
  public function load($uri) {
    if(!is_file($uri)) {
      throw new ResourceNotFoundException("Could not load resource $uri. Not a local file.");
    }
    if(!is_readable($uri)) {
      throw new ResourceNotFoundException("Could not load resource $uri. Not readable.");
    }
    return file_get_contents($uri);
  }
}
