<?php
/**
*图片转换插件 调用方式
*/

$wgExtensionFunctions[] = "joymeImgChange"; 
function joymeImgChange() { 
   global $wgParser;
   $wgParser->setHook( "joymeimgchange", "imgdata" );
} 

function imgdata( $input, $argv ) {
	global $wgParser;
	$out = $wgParser->getOutput();
	$out->addModuleScripts('ext.joymescript.imgChange.js');

	return '<input type="hidden" value="'.json_encode($argv).'" class="imgchange"/>';
} 

$wgResourceModules['ext.joymescript.imgChange.js'] = array(
		'scripts' => 'imgChange.js',
		'position' => 'bottom',
		'localBasePath' => __DIR__ . '/../jsscripts',
		'remoteExtPath' => 'jsscripts',
);

