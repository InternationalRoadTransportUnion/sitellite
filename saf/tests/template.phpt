--TEST--
saf.Template
--FILE--
<?php

// test setup

// remove this when test is ready to be run
return;

include_once ('../init.php');

// include library

loader_import ('saf.Template');

// constructor method

$template = new Template;

?>
--EXPECT--
