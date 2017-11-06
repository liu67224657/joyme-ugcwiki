<?php 
// This is the hook function. It adds the tag to the wiki parser and tells it what callback function to use.
function wfAddCompareSelHook() {
	global $wgParser;
	# register the extension with the WikiText parser
	$wgParser->setHook( "comparesel", "compareSel" );
}
# The callback function for converting the input text to HTML output
function compareSel( $input ,$argv ) {
	return '<label><input type="checkbox" onclick="card_sel(this);" />'.$argv['name'].'</label>'; 
}

$wgExtensionFunctions[] = "wfAddCompareSelHook";

?>