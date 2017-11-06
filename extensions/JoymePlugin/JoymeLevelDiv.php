<?php 
// This is the hook function. It adds the tag to the wiki parser and tells it what callback function to use.
function wfleveldivHook() {
	global $wgParser;
	# register the extension with the WikiText parser
	$wgParser->setHook( "leveldiv", "showLevelDiv" );
}
# The callback function for converting the input text to HTML output
function showLevelDiv( $input ,$argv ) {
	$maxlevel = empty($argv['maxlevel'])?100:intval($argv['maxlevel']);
	$addition = empty($argv['addition'])?'':$argv['addition'];
	$alvs = empty($argv['alvs'])?'':$argv['alvs'];
	$iname = empty($argv['iname'])?'无科技加成':$argv['iname'];
	$bg = empty($argv['bg'])?'#0064CD':$argv['bg'];
	$levelname = empty($argv['lname'])?'人物等级':$argv['lname'];
	$dlev = empty($argv['dlev'])?$maxlevel:$argv['dlev'];
	$hasinputstr = '';
	$alvstr = '';
	if($addition){
		$additionarr = explode('|',$addition);
		$hasinputstr = '<div style="float:right;padding-top:2px;margin-left:100px;"><input type="radio" name="addition" checked="checked" value="1" iv1="1" iv2="1" iv3="1" />'.$iname.'';
		foreach($additionarr as $v){
			$tmp = explode('_',$v);
			$hasinputstr.='<span style="white-space: nowrap;"><input type="radio" name="addition" value="1" iv1="'.$tmp[1].'" iv2="'.$tmp[2].'" iv3="'.$tmp[3].'" />'.$tmp[0].'</span>';
		}
		$hasinputstr.= '</div>';
	}
	if($alvs){
		$alvsarr = explode('|',$alvs);
		foreach($alvsarr as $v){
			$tmp = explode('_',$v);
			$alvstr.='<input type="hidden" id="data-lv'.$tmp[0].'" value="'.$tmp[1].'" />';
		}
	}
	
	global $wgParser;
	$out = $wgParser->getOutput();
	$out->addModuleScripts('ext.joymescript.LevelTools.js');
	
	$str = <<<EOT
$hasinputstr<span style="display:inline;white-space: nowrap;"><form action="#">{$levelname}：<input id="userLevel" type="text" readonly="" value="$maxlevel" style="max-width: 35px;padding:4px;border:1px solid #CCCCCC;color:#808080;">{$alvstr}</form></span>
<div style="width:100%;float:left;padding-top: 12px; ">
<div id="slider" style="background-color: #FFF;border:1px solid #AAAAAA;height:0.8em;position:relative;text-align:left;" aria-disabled="false">
<div id='dslider' style="width: 100%;background-color: $bg;height:0.8em;"></div>
<span id='aslider' style="display:block;cursor:pointer;left: 100%;top: -0.4em;margin-left: -0.6em;height:1.5em;position:absolute;width:1.5em;z-index:2;background-color:#E6E6E6;border-color:#CCCCCC #CCCCCC #BBBBBB;border-style:solid;border-width:1px;"></span>
</div>
</div>
<input type='hidden' id='dlev' value="$dlev" />
<input type='hidden' id='maxLevel' value="$maxlevel" />
EOT;
	return $str;
}

$wgExtensionFunctions[] = "wfleveldivHook";

$wgResourceModules['ext.joymescript.LevelTools.js'] = array(
		'scripts' => 'LevelTools.js',
		'position' => 'bottom',
		'localBasePath' => __DIR__ . '/../jsscripts',
		'remoteExtPath' => 'jsscripts',
);

?>