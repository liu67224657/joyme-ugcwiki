<?php 
// This is the hook function. It adds the tag to the wiki parser and tells it what callback function to use.
function wfAddCarouselHook() {
	global $wgParser;
	# register the extension with the WikiText parser
	$wgParser->setHook( "lunbo", "addCarousel" );
}
# The callback function for converting the input text to HTML output
function addCarousel( $input ,$argv ) {
	global $wgParser;
	$out = $wgParser->getOutput();
	$out->addModuleScripts('ext.joymescript.lunbo.js');
	$out->addModuleStyles('ext.joymescript.lunbo.css');

	$ptime = empty($argv['ptime'])?3000:intval($argv['ptime']);
	$html ='<input type="hidden" id="plooptime" value="'.$ptime.'" />';
	return $html;
}

$wgExtensionFunctions[] = "wfAddCarouselHook";

$wgResourceModules['ext.joymescript.lunbo.css'] = array(
		'styles' => 'lunbo.css',
		'position' => 'top',
		'localBasePath' => __DIR__ . '/modules',
		'remoteExtPath' => 'JoymePlugin',
);

$wgResourceModules['ext.joymescript.lunbo.js'] = array(
		'scripts' => 'lunbo.js',
		'position' => 'bottom',
		'localBasePath' => __DIR__ . '/../jsscripts',
		'remoteExtPath' => 'jsscripts',
);

?>