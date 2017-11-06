<?php
$wgExtensionFunctions [] = "joymeHiddenId";
$GLOBALS ['asObjHiddenId'] = new  joymeHiddenIdClass ();
function joymeHiddenId() {
	global $asObjHiddenId;
	global $wgHooks;
	
	global $wgParser;
	$wgParser->setHook('joymehiddenid', array( $asObjHiddenId, 'initJoymeHiddenId' ) );
}

class joymeHiddenIdClass {
	var $slist;
	static function getGlobalObjectName() {
		return "asObjHiddenId";
	}
	
	static function &getGlobalObject() {
		return $GLOBALS ['asObjHiddenId'];
	}
	
	function initJoymeHiddenId( $input, $argv ) {
		if (!empty($argv)){
			$argv_hiddenId = isset($argv['hiddenid']) ? $argv['hiddenid']:'';
			$output ='<input type="hidden"  id="hidden-tag" value="'.$argv_hiddenId.'">';
			return  $output;
		}else {
			return  true;
		}
		
	}
	
}