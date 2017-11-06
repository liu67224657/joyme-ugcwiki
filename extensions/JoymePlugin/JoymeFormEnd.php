<?php 
// This is the hook function. It adds the tag to the wiki parser and tells it what callback function to use.
function wfAddFormEndHook() {
	global $wgParser;
	# register the extension with the WikiText parser
	$wgParser->setHook( "formend", "addFormend" );
}
# The callback function for converting the input text to HTML output
function addFormend( $input ) {
	return '</form>'; 
}

$wgExtensionFunctions[] = "wfAddFormEndHook";

?>