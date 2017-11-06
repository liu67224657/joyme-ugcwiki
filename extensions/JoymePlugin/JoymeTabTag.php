<?php 
// This is the hook function. It adds the tag to the wiki parser and tells it what callback function to use.
function wfSetTabHook() {
	global $wgParser;
	# register the extension with the WikiText parser
	$wgParser->setHook( "psettab", "setTab" );
}
# The callback function for converting the input text to HTML output
function setTab( $input ,$argv ) {
	if(isset($argv['spancss'])&&isset($argv['icss'])){
		return '<li id="'.$argv['li_id'].'" class="'.$argv['tabcss'].'" onclick="'.$argv['settabfn'].'"><span class="'.$argv['spancss'].'">'.$argv['li_font'].'</span><i class="'.$argv['icss'].'"> </i></li>';
	}else{
		return '<li id="'.$argv['li_id'].'" class="'.$argv['tabcss'].'" onclick="'.$argv['settabfn'].'">'.$argv['li_font'].'</li>';
	}
}

$wgExtensionFunctions[] = "wfSetTabHook";
