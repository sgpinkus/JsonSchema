<?php
namespace JsonDoc\Exception;

/**
 * Map error code returned by json_last_error() to an exception.
 * @see https://secure.php.net/manual/en/function.json-last-error.php
 */
class JsonDecodeException extends \RuntimeException
{
  public function __construct($code = JSON_ERROR_NONE, \Exception $previous = null)
  {

    switch ($code) {
      case JSON_ERROR_NONE:
        $message = 'An unspecified error has occurred';
        break;
      case JSON_ERROR_DEPTH:
        $message = 'The maximum stack depth has been exceeded';
        break;
      case JSON_ERROR_STATE_MISMATCH:
        $message = 'Invalid or malformed JSON';
        break;
      case JSON_ERROR_CTRL_CHAR:
        $message = 'Control character error, possibly incorrectly encoded';
        break;
      case JSON_ERROR_UTF8:
        $message = 'Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
      case JSON_ERROR_SYNTAX:
        $message = 'Syntax error';
        break;
      default:
        $message = 'An unknown error has occurred';
    }
    parent::__construct($message, $code, $previous);
  }
}
