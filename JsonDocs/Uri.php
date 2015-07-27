<?php
namespace JsonDocs;

/**
 * Encapsulates a valid URI. Suprised PHP has no such class.
 * Note parse_url() parses URLs not URIs but it ~works. Only works with absolute file paths though.
 * @todo Not really a URI. This is a stub for an eventual higher quality implementation.
 */
class Uri
{
  private $parts = [];

  /**
   * Init.
   */
  public function __construct($uri) {
    $uri = preg_replace("/#$/", "# ", $uri); // "#" does not parse.
    $parse = parse_url($uri);
    if(isset($parse['fragment']) && $parse['fragment'] == " "){
      $parse['fragment'] = "";
    }
    if(!$parse) {
      throw new UriException("Could not parse URI '$uri'");
    }
    foreach($parse as $part => $value) {
      $this->set($part, $value);
    }
  }

  /**
   * Get part.
   */
  public function get($part) {
    return isset($this->parts[$part]) ? $this->parts[$part] : null;
  }

  /**
   * Set part.
   * @todo Ensure the URI remains valid.
   */
  public function set($part, $value) {
    $allow = ['scheme', 'host', 'user', 'pass', 'path', 'port', 'query', 'fragment'];
    if(!in_array($part, $allow)) {
      throw new UriException("Can't set $part.");
    }
    switch($part) {
      case 'path': {
        $this->parts['path'] = preg_replace("#\/+#", "/", $value);
        break;
      }
      default: {
        $this->parts[$part] = $value;
      }
    }
  }

  /**
   * Allow certain parts to be unset.
   * @todo Ensure the URI remains valid.
   */
  public function clear($part) {
    $allow = ['user', 'pass', 'path', 'query', 'fragment'];
    if(!in_array($part, $allow)) {
      throw new UriException("Can't unset $part.");
    }
    if($part == 'path') {
      unset($this->parts['query']);
      unset($this->parts['fragment']);
    }
    unset($this->parts[$part]);
  }

  public function __get($part) {
    return $this->get($part);
  }

  public function __set($part, $value) {
    return $this->set($part, $value);
  }

  public function __unset($part) {
    return $this->clear($part);
  }

  /**
   * Returns true iff this URI is an absolute URI.
   * According to RFC3986 only the scheme need be defined for a URI to be absolute.
   */
  public function isAbsoluteUri() {
    return isset($this->parts['scheme']);
  }

  /**
   * Returns true iff this URI is a relative URI.
   */
  public function isRelativeUri() {
    return !$this->isAbsoluteUri();
  }

  /**
   * Return a URI that is this URI rebased against $base, an absolute Base URI.
   * If this URI is an absolute URI a copy of this URI is returned.
   * Notes: Always use the reference's fragment. Replace the base's query only if path is non empty.
   * @see https://tools.ietf.org/html/rfc3986#section-5.2.
   */
  public function baseOn(Uri $base) {
    $uri = null;
    if($base->isRelativeUri()) {
      throw new UriException("Can't base $this against $base. Invalid base URI");
    }
    if($this->isAbsoluteUri()) {
      $uri = clone $this;
    }
    else {
      $uri = clone $base;
      $path = $this->get('path');
      $query = $this->get('query');
      if(empty($path)) {
        if(isset($query)) {
          $uri->set('query', $query);
        }
      }
      else if(strpos($path, '/') === 0) {
        $uri->set('path', $path);
        $uri->set('query', $query);
      }
      else {
        $basePath = preg_replace('#/[^/]*$#', "", $uri->get('path'));
        $uri->set('path',  $basePath . "/" . $path);
        $uri->set('query', $query);
      }
      $uri->set('fragment', $this->get('fragment'));
    }
    return $uri;
  }

  /**
   * Same as baseOn() but the other way around.
   */
  public function resolveRelativeUriOn(Uri $uri) {
    return $uri->baseOn($this);
  }

  /**
   * To a string!
   */
  public function __toString() {
    return self::unparse_url($this->parts);
  }

  /**
   * To an array!
   */
  public function toArray() {
    return $this->parts;
  }

  /**
   * To string method for parse_url() output. C&P from http://php.net/.
   */
  public static function unparse_url(array $parts) {
    $scheme   = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
    $host     = isset($parts['host']) ? $parts['host'] : '';
    $port     = isset($parts['port']) ? ':' . $parts['port'] : '';
    $user     = isset($parts['user']) ? $parts['user'] : '';
    $pass     = isset($parts['pass']) ? ':' . $parts['pass']  : '';
    $pass     = ($user || $pass) ? "$pass@" : '';
    $path     = isset($parts['path']) ? $parts['path'] : '';
    $query    = isset($parts['query']) ? '?' . $parts['query'] : '';
    $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
    return "$scheme$user$pass$host$port$path$query$fragment";
  }
}

class UriException extends \Exception {}
