<?php
namespace JsonDocs;

/**
 * A JsonRef PQueue. Used by JsonCache in dereferencing.
 */
class JsonRefPriorityQueue extends \SplPriorityQueue
{
  public function compare(JsonRef $a, JsonRef $b) {
    return $a->compare($b);
  }
}
