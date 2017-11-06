<?php 
// This is the hook function. It adds the tag to the wiki parser and tells it what callback function to use.
function wfAddFormStartHook() {
	global $wgParser;
	# register the extension with the WikiText parser
	$wgParser->setHook( "formstart", "addFormstart" );
}
# The callback function for converting the input text to HTML output
function addFormstart( $input ) {
	return '<form>'; 
}

$wgExtensionFunctions[] = "wfAddFormStartHook";

?>