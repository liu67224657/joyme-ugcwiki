<?php
$wgExtensionFunctions [] = "joymeShowDiv";
$GLOBALS ['asObjShowDiv'] = new  JoymeShowDivClass ();
function joymeShowDiv() {
	global $asObjShowDiv;
	global $wgHooks;
	
	global $wgParser;
	$wgParser->setHook('joymeshowdiv', array( $asObjShowDiv, 'initJoymeShowDiv' ) );
}

class JoymeShowDivClass {
	var $slist;
	static function getGlobalObjectName() {
		return "asObjShowDiv";
	}
	
	static function &getGlobalObject() {
		return $GLOBALS ['asObjShowDiv'];
	}
	
	function initJoymeShowDiv( $input, $argv ) {
		if (!empty($argv)){
			$argv_div = isset($argv['div']) ? $argv['div']:'';
			$output ='<input type="hidden"  id="show_div" value="'.$argv_div.'">';
			return  $output;
		}else {
			return  true;
		}
		
	}
	
}