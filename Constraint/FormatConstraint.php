<?php
namespace JsonSchema\Constraint;

use JsonSchema\Constraint\Constraint;
use JsonSchema\Constraint\Exception\ConstraintParseException;
use JsonSchema\Constraint\PatternConstraint;

/**
 * The format constraint.
 * @todo Should probably break this down into associated classes for each format type.
 */
class FormatConstraint extends Constraint
{
  private static $FORMATS = [
    'date-time',
    'email',
    'hostname',
    'ipv4',
    'ipv6',
    'uri',
    'regex'
  ];
  private $format;

  public function __construct($format) {
    $this->format = $format;
  }

  /**
   * @override
   */
  public static function getName() {
    return 'format';
  }

  /**
   * @override
   */
  public function validate($doc, $context) {
    $valid = true;
    if(is_string($doc)) {
      switch($this->format) {
        case 'date-time': {
          $valid = (bool)\DateTime::createFromFormat(\DateTime::RFC3339, $doc) ||
            (bool)\DateTime::createFromFormat("Y-m-d\TH:i:s.uP", $doc);
          break;
        }
        case 'email': {
          $valid = (bool)filter_var($doc, FILTER_VALIDATE_EMAIL);
          break;
        }
        case 'hostname': {
          $valid = (bool)preg_match("/^([a-z0-9-]{1,63}\.)+[a-z0-9-]{1,63}\.?$/i", $doc); // Spec requires RFC1034. Is this all g?
          break;
        }
        case 'ipv4': {
          $valid = (bool)filter_var($doc, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
          break;
        }
        case 'ipv6': {
          $valid = (bool)filter_var($doc, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
          break;
        }
        case 'uri': {
          $valid = (bool)filter_var($doc, FILTER_VALIDATE_URL); // @todo parses RFC2396 *URL*.
          break;
        }
        case 'regex': {
          // Test validity. Note no such thing as a compiled regexp in PHP, but does have a cache.
          $pattern = PatternConstraint::fixPreg($pattern);
          if(@preg_match($pattern, "0") === false) {
            $valid = false;
          }
        }
      }
      if(!$valid) {
        $valid = new ValidationError($this, "'$doc' not a valid {$this->format}.", $context);
      }
    }
    return $valid;
  }



  /**
   * @override
   */
  public static function build($context) {
    $doc = $context->format;
    if(!is_string($doc)) {
      throw new ConstraintParseException('The value MUST be a string.');
    }
    if(!in_array($doc, static::$FORMATS)) {
      throw new ConstraintParseException("Unsupported format. $doc not in " . json_encode(static::$FORMATS));
    }
    return new static($doc);
  }
}
