<?php
$wgExtensionFunctions [] = "joymeMovie";
$GLOBALS ['asObjMovie'] = new  JoymeMovieClass ();
function joymeMovie() {
	global $asObjMovie;
	global $wgHooks;
	
	global $wgParser;
	$wgParser->setHook('joymemovie', array( $asObjMovie, 'initJoymeMovie' ) );
}

class JoymeMovieClass {
	var $slist;
	static function getGlobalObjectName() {
		return "asObjMovie";
	}
	
	static function &getGlobalObject() {
		return $GLOBALS ['asObjMovie'];
	}
	
	function initJoymeMovie( $input, $argv ) {
		if (!empty($argv)){
			$argv_viewdiv = isset($argv['viewdiv']) ? $argv['viewdiv']:'joyme_movie';
			$argv_swfname = isset($argv['swfname']) ? $argv['swfname']:'';
			$argv_htmlname = isset($argv['htmlname']) ? $argv['htmlname']:'';
			$argv_moviepic = isset($argv['moviepic']) ? $argv['moviepic']: '';
			$argv_vWidth = isset($argv['vwidth']) ? $argv['vwidth']: 428;
			$argv_vHeight = isset($argv['vheight']) ? $argv['vheight']: 320;
			
			$output = '<div id="'.$argv_viewdiv.'">';
			$output .= '</div>';
			$output.='<input type="hidden"  id="move_'.$argv_viewdiv.'_swfname" value="'.$argv_swfname.'">';
			$output.='<input type="hidden"  id="move_'.$argv_viewdiv.'_htmlname" value="'.$argv_htmlname.'">';
			$output.='<input type="hidden"  id="move_'.$argv_viewdiv.'_moviepic" value="'.$argv_moviepic.'">';
			$output.='<input type="hidden"  id="move_'.$argv_viewdiv.'_vWidth" value="'.$argv_vWidth.'">';
			$output.='<input type="hidden"  id="move_'.$argv_viewdiv.'_vHeight" value="'.$argv_vHeight.'">';
			return  $output;
		}else {
			return  true;
		}
		
	}
	
}