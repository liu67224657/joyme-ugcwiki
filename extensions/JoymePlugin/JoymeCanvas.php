<?php
/**
* 蜘蛛网图
*/
$wgExtensionFunctions[] = "CanvasTag";
function CanvasTag() { 
	global $wgParser; 
	$wgParser->setHook( "canvas", "CanvasData" ); 
} 

function CanvasData( $input, $argv ) {
	
	global $wgParser;
	$out = $wgParser->getOutput();
	$out->addModuleScripts('ext.joymescript.canvas.js');
	
	return '<canvas class="canvas" id="'.$argv['id'].'" height="'.$argv['height'].'" width="'.$argv['width']
			.'" data-msg="'.$argv['msg'].'" data-bgcolor="'.$argv['bgcolor']
			.'" data-color="'.$argv['color'].'" data-size="'.$argv['size']
			.'">canvas</canvas>';
}

$wgResourceModules['ext.joymescript.canvas.js'] = array(
		'scripts' => 'canvas.js',
		'position' => 'bottom',
		'localBasePath' => __DIR__ . '/../jsscripts',
		'remoteExtPath' => 'jsscripts',
);