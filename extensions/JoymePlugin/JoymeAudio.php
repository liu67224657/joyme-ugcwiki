<?php 
// This is the hook function. It adds the tag to the wiki parser and tells it what callback function to use.
function wfAddAudioHook() {
	global $wgParser;
	# register the extension with the WikiText parser
	$wgParser->setHook( "Audio", "addAudio" );
}
# The callback function for converting the input text to HTML output
function addAudio( $input ,$argv ) {
	$style = $controls = $class = '';
	if(empty($argv['src']))
	{
		return 'no src';
	}
	if(!empty($argv['style']))
	{
		$style = ' style="'.$argv['style'].'" ';
	}
	if(!empty($argv['controls']))
	{
		$controls = ' controls ';
	}
	if(!empty($argv['class']))
	{
		$class = ' class="'.$argv['class'].'" ';
	}
	return '<audio '.$style.$controls.$class.' src="'.$argv['src'].'" controls="controls"></audio>'; 
}

$wgExtensionFunctions[] = "wfAddAudioHook";

?>