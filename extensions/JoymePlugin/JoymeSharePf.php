<?php
$wgExtensionFunctions [] = "joymeSharePf";
$GLOBALS ['asObjSharePf'] = new  joymeSharePfClass ();
function joymeSharePf() {
	global $asObjSharePf;
	global $wgHooks;
	
	global $wgParser;
	$wgParser->setHook('joymesharepf', array( $asObjSharePf, 'initJoymeSharePf' ) );
}

class joymeSharePfClass {
	var $slist;
	static function getGlobalObjectName() {
		return "asObjSharePf";
	}
	
	static function &getGlobalObject() {
		return $GLOBALS ['asObjSharePf'];
	}
	
	function initJoymeSharePf( $input, $argv ) {
		if (!empty($argv)){
			$argv_description = isset($argv['description']) ? $argv['description']:'分享';
			$argv_sid = isset($argv['sid']) ? $argv['sid']:'';
			
			$share_url = 'http://www.joyme.com/json/share/getbyid?sid='.$argv_sid;
			
			$getJson = file_get_contents($share_url);
			//$getJson = '{"result":{"share_id":10000,"display_style":"分享这篇文章吧（测试）"},"msg":null,"rs":1}';
			$getShare = json_decode($getJson,true);
			///sinaweibo qweibo qq
			$output = '';
			if ($getShare['rs'] == 1){
				$sina_view_href = 'http://www.joyme.com/share/content/sinaweibo/bind?sid='.$getShare['result']['share_id'];
				$qweibo_view_href = 'http://www.joyme.com/share/content/qweibo/bind?sid='.$getShare['result']['share_id'];
				$qq_view_href = 'http://www.joyme.com/share/content/qq/bind?sid='.$getShare['result']['share_id'];
				$output .=  '<div class="wiki-share-type1"><div class="wiki-share-type1-left"></div><div class="wiki-share-type1-center"><span>'.$argv_description.'：</span><a href="'.$sina_view_href.'" class="share_sina" target="_blank" ></a><a href="'.$qweibo_view_href.'" class="share_tengxun" target="_blank" ></a><a href="'.$qq_view_href.'" class="share_qq" target="_blank" ></a></div><div class="wiki-share-type1-right"></div></div>
							';
			}
			
			if ($getShare['rs'] == 0){
				$output .= json_encode($getShare['msg']);
			}
		
			return  $output;
		}else {
			return  true;
		}
		
	}
	
}