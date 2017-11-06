<?php
$wgExtensionFunctions [] = "cardSelect";
$GLOBALS ['asObjSelect'] = new  CardSelectClass ();
function cardSelect() {
	global $asObjSelect;
	global $wgHooks;
	
	global $wgParser;
	$wgParser->setHook('cardselect', array( $asObjSelect, 'initCompareSelect' ) );
	//$wgHooks ['ParserAfterTidySelect'] [] = array ($asObjSelect,'feedScriptsSelect');
}

class CardSelectClass {
	var $slist;
	static function getGlobalObjectName() {
		return "asObjSelect";
	}
	
	static function &getGlobalObject() {
		return $GLOBALS ['asObjSelect'];
	}
	
	function initCompareSelect( $input, $argv ) {
		if(isset($argv['name'])){
			$this->slist = "this is hook text value";
		}
		if (!empty($argv)){
			$argv_value = isset($argv['value']) ? $argv['value']:'';
			$argv_dataoption = isset($argv['data-option']) ? $argv['data-option'] :'';
			$argv_datagroup =  isset($argv['data-group']) ? $argv['data-group'] :'';
			$argv_datas = isset($argv['data-s']) ? $argv['data-s'] :''; 
			$output ='<input type="button" value="'.$argv_value.'" data-option="'.$argv_dataoption.'" class="cardSelectOption" data-group="'.$argv_datagroup.'" data-s="'.$argv_datas.'" >';
			//$output ='<input type="button" value="'.$argv_value.'" data-option="'.$argv_dataoption.'" class="cardSelectOption" data-group="'.$argv_datagroup.'">';
			return  $output;
		}else {
			return  true;
		}
		
	}
	
/* 	public function feedScriptsSelect( &$parser, &$text )
	{
		global $wgScriptPath;
		global $wgOut;
		$page_title = $wgOut->mPagetitle;  
		//$wgOut->mArticleBodyOnly;
		//$output = '<input type="text" id="card_title_compare" value="'.$page_title.'" style="display:none;"  >';
		
		//$wgOut->addHTML($output);
		//$wgOut->addWikiText("{{123456}}");
		$this->slist = '';
		return true; 
	} */
	
	
	
}