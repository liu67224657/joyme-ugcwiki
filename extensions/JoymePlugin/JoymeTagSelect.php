<?php
$wgExtensionFunctions [] = "tagSelect";

function tagSelect() {
	global $wgParser;
	$wgParser->setHook('tagselect', 'select' );
}

	
function Select( $input ,$argv ) {
	$num = count($argv)-1;
	$option = '';
	for($i=0;$i<$num;$i++){
		$option .= '<option value="'.$i.'">'.$argv[$i].'</option>';
	}
	return "<select id=".$argv['select_id'].">$option</select>";
	//return '<select '.$argv['select_id'].'><option value="">选择升级起点</option></select>';
}
