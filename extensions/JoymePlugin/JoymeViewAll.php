<?php
$wgExtensionFunctions [] = "joymeShowView";
$GLOBALS ['asObjShowSpan'] = new  joymeShowViewClass ();
function joymeShowView() {
	global $asObjShowSpan;
	global $wgHooks;
	
	global $wgParser;
	$wgParser->setHook('joymeshowview', array( $asObjShowSpan, 'initJoymeShowView' ) );
}

class JoymeShowViewClass {
	var $slist;
	static function getGlobalObjectName() {
		return "asObjShowSpan";
	}
	
	static function &getGlobalObject() {
		return $GLOBALS ['asObjShowSpan'];
	}
	
	function initJoymeShowView( $input, $argv ) {
		if (!empty($argv)){
			
			$argv_content = isset($argv['content']) ? $argv['content']:'';
			$output =argv_content;
			return  $output;
		}else {
			return  true;
		}
		
	}
	
}