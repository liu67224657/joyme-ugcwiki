<?php
function wfshowspanHook() {
	global $wgParser;
	# register the extension with the WikiText parser
	$wgParser->setHook( "joymeshowspan", "showSpan" );
}
# The callback function for converting the input text to HTML output
function showSpan( $input ,$argv ) {
	if (!empty($argv)){
		$argv_onclick = isset($argv['onclick']) ? $argv['onclick']:'';
		$argv_name = isset($argv['name']) ? $argv['name']:'';
		$output ='<span onclick="'.$argv_onclick.'">'.$argv_name.'</span>';
		return  $output;
	}else {
		return  '';
	}
}

$wgExtensionFunctions[] = "wfshowspanHook";