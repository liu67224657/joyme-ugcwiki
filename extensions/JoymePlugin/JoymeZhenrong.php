<?php 
// This is the hook function. It adds the tag to the wiki parser and tells it what callback function to use.
function wfAddZhenrongHook() {
	global $wgParser;
	# register the extension with the WikiText parser
	$wgParser->setHook( "zhenrong", "addZhenrong" );
}
# The callback function for converting the input text to HTML output
function addZhenrong( $input ,$argv ) {
	/*
	zr_one_num  zr_on_num zr_num
	*/
	
	global $wgParser;
	$out = $wgParser->getOutput();
	$out->addModuleScripts('ext.joymescript.zhenrong.js');
	
	return '<input type="hidden" id="zr_one_num" value="'.intval($argv['zr_one_num']).'"><input type="hidden" id="zr_on_num" value="'.intval($argv['zr_on_num']).'">'; 
}

$wgExtensionFunctions[] = "wfAddZhenrongHook";

$wgResourceModules['ext.joymescript.zhenrong.js'] = array(
		'scripts' => 'zhenrong.js',
		'position' => 'bottom',
		'localBasePath' => __DIR__ . '/../jsscripts',
		'remoteExtPath' => 'jsscripts',
);

?>