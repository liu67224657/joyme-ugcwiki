<?php
use Joyme\core\Request;
$wgAjaxExportList[] = 'wfWikiDynamic';

function wfWikiDynamic(){

    $pb_page = intval(Request::get('pb_page',1));
    if(Request::get('page_type')!=2){
        $type1 = Request::get('page_type',1);
        $type2 = Request::get('page_type',0);
    }else{
        $type1 = 1;
        $type2 = 0;
    }
    $time = Request::get('day',0);
    $model = new SpecialWikiDynamic();
    $html = $model->buildContent( $pb_page,$type1,$type2,$time );
    echo RecommendUsers::returnJson( $html );
    exit;
}