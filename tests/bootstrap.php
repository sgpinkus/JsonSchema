<?php
error_reporting((E_ALL)&~(E_STRICT)); # Static abstract, Covariance.
require_once dirname(__FILE__) . '/../loader.php';
require_once dirname(__FILE__) . '/ConstraintTest.php';

putenv("DATADIR=".dirname(__FILE__) . "/test-data");
