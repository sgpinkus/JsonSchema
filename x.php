<?php
require_once 'vendor/autoload.php';
use JsonDoc\Uri;
use JsonDoc\JsonDocs;
print new Uri("file://x") . "\n";
print new Uri("http://x") . "\n";
print new Uri("file:///") . "\n";
// print new Uri("http:///") . "\n";
print new Uri("/x") . "\n";
print new Uri("x") . "\n";
print new Uri("#x") . "\n";
print new Uri("?x=y") . "\n";
print new Uri("?x=y#x") . "\n";
print (new Uri("?x=y#x"))->fragment . "\n";
$x = print new Uri("/x") . "\n";


$jsonDocs = new JsonDocs();
