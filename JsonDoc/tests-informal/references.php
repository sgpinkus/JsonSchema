<?php
/**
 * What the fuck is happening why the fuck does shit work!?
 */

function queueAllRefs(&$doc, &$queue) {
  if(is_object($doc) || is_array($doc)) {
    foreach($doc as $k => &$v) {
      $ref =& $v; //getRef($doc, $k);
      if(is_object($ref) || is_array($ref)) {
        $queue[] =& $ref;
        queueAllRefs($ref, $queue);
      }
    }
  }
}

/**
 * Convenience wrapper to get a reference.
 */
function &getRef(&$doc, $k) {
  if(is_object($doc)) {
    return $doc->$k;
  }
  if(is_array($doc)) {
    return $doc[$k];
  }
}

$doc = '{
  "x": {},
  "y": [{},1,2,3]
}';
$doc = json_decode($doc);
$q = [];
queueAllRefs($doc, $q);
var_dump($q);
$q[2] = 99;
$q[0] = 77;
var_dump($doc);
