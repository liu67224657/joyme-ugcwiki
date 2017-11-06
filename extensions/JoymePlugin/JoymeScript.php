<?php 
$wgExtensionFunctions[] = "joymeScriptExtension"; 
function joymeScriptExtension() { 
   global $wgParser;
   # register the extension with the WikiText parser
   # the first parameter is the name of the new tag.
   # In this case it defines the tag <example> ... </example>
   # the second parameter is the callback function for
   # processing the text between the tags
   $wgParser->setHook( "joymescript", "renderjoymescript" );
   
   
} 

function renderjoymescript( $input, $argv ) {
	global $wgParser;
  	$out = $wgParser->getOutput();
	$out->addModuleScripts('ext.joymescript.'.$argv['argument'].'.js');
  	return '';
} 

$wgResourceModules['ext.joymescript.year.js'] = array(
		'scripts' => 'year.js',
		'position' => 'bottom',
		'localBasePath' => __DIR__ . '/../jsscripts',
		'remoteExtPath' => 'jsscripts',
);

$wgResourceModules['ext.joymescript.ShowMsg.js'] = array(
		'scripts' => 'ShowMsg.js',
		'position' => 'bottom',
		'localBasePath' => __DIR__ . '/../jsscripts',
		'remoteExtPath' => 'jsscripts',
);
$wgResourceModules['ext.joymescript.actor-tab.js'] = array(
		'scripts' => 'actor-tab.js',
		'position' => 'bottom',
		'localBasePath' => __DIR__ . '/../jsscripts',
		'remoteExtPath' => 'jsscripts',
);
$wgResourceModules['ext.joymescript.CardSelect.js'] = array(
		'scripts' => 'CardSelect.js',
		'position' => 'bottom',
		'localBasePath' => __DIR__ . '/../jsscripts',
		'remoteExtPath' => 'jsscripts',
);
$wgResourceModules['ext.joymescript.CardSelectTr.js'] = array(
		'scripts' => 'CardSelectTr.js',
		'position' => 'bottom',
		'localBasePath' => __DIR__ . '/../jsscripts',
		'remoteExtPath' => 'jsscripts',
);
$wgResourceModules['ext.joymescript.lableLib.js'] = array(
		'scripts' => 'lableLib.js',
		'position' => 'bottom',
		'localBasePath' => __DIR__ . '/../jsscripts',
		'remoteExtPath' => 'jsscripts',
);
$wgResourceModules['ext.joymescript.LevelTools.js'] = array(
		'scripts' => 'LevelTools.js',
		'position' => 'bottom',
		'localBasePath' => __DIR__ . '/../jsscripts',
		'remoteExtPath' => 'jsscripts',
);
$wgResourceModules['ext.joymescript.rool.js'] = array(
		'scripts' => 'rool.js',
		'position' => 'bottom',
		'localBasePath' => __DIR__ . '/../jsscripts',
		'remoteExtPath' => 'jsscripts',
);
$wgResourceModules['ext.joymescript.TabTag.js'] = array(
		'scripts' => 'TabTag.js',
		'position' => 'bottom',
		'localBasePath' => __DIR__ . '/../jsscripts',
		'remoteExtPath' => 'jsscripts',
);
$wgResourceModules['ext.joymescript.w-lunbo.js'] = array(
		'scripts' => 'w-lunbo.js',
		'position' => 'bottom',
		'localBasePath' => __DIR__ . '/../jsscripts',
		'remoteExtPath' => 'jsscripts',
);
$wgResourceModules['ext.joymescript.JoymeMovie.js'] = array(
		'scripts' => 'JoymeMovie.js',
		'position' => 'bottom',
		'localBasePath' => __DIR__ . '/../jsscripts',
		'remoteExtPath' => 'jsscripts',
);




