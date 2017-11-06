<?php 
// This is the hook function. It adds the tag to the wiki parser and tells it what callback function to use.
function wfAddShowWeekIdHook() {
	global $wgParser;
	# register the extension with the WikiText parser
	$wgParser->setHook( "showweekid", "showWeekId" );
}
# The callback function for converting the input text to HTML output
function showWeekId( $input ,$argv ) {
	if(empty($argv['week']))
	{
		return '';
	}
	
	global $wgParser;
	$out = $wgParser->getOutput();
	$out->addModuleScripts('ext.joymescript.week.js');
	
	return '<input type="hidden" id="pweek" value="'.$argv['week'].'" />'; 
}

$wgExtensionFunctions[] = "wfAddShowWeekIdHook";

$wgResourceModules['ext.joymescript.week.js'] = array(
		'scripts' => 'week.js',
		'position' => 'bottom',
		'localBasePath' => __DIR__ . '/../jsscripts',
		'remoteExtPath' => 'jsscripts',
);

?>