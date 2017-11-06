<?php 
// This is the hook function. It adds the tag to the wiki parser and tells it what callback function to use.
function wfAddPollHook() {
	global $wgParser;
	# register the extension with the WikiText parser
	$wgParser->setHook( "poll", "addPoll" );
}
# The callback function for converting the input text to HTML output
function addPoll( $input ,$argv ) {
	$lines = explode( "\n", $input );
	return '<div class="poll">'.$input.'</div>'; 
}

$wgExtensionFunctions[] = "wfAddPollHook";

?>