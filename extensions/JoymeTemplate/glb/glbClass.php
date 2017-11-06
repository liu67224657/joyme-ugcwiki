<?php

class glbClass{

    //定义返回值格式
    static public $data = array(
        'body'=>array(
            'title'=>'',
            'update_time'=>'2016-11-14',
            'content'=>'暂时还没有内容',
        ),
        'Advertise'=>array(

        ),
        'recomm'=>array(

        )
    );

    //模版名称
    static public $templateName = '哥伦布推荐';

    //推荐默认图片
    static $imgSrc = '/extensions/JoymeTemplate/glb/img/default.jpg';


    //获取文章内容
    static function getContent( $pageTitle = '' ,$flag = false){

        global $wgParser;

        if(isset($pageTitle) && $flag == false){
            self::$data['body']['title'] = $pageTitle;
        }
        $wgTitle = Title::makeTitle( NS_SPECIAL, $pageTitle );
        //查找text_id
        $res = self::getTextIdByTitle( $pageTitle ,$flag);

        if(isset($res->rev_text_id) && isset($res->rev_page)){

            //获取更新时间
            if($flag == false){
                $pageInfo  = self::getPageUpdateTime($res->rev_page);
                if(isset($pageInfo->time)){
                    self::$data['body']['update_time'] = $pageInfo->time;
                }
            }
            $text = self::getTextContentById( $res->rev_text_id );

            $content = $text->old_text?$text->old_text:null;
            //解析内容
            if(isset($content)){
                $options = new ParserOptions;
                $result = $wgParser->parse($content, $wgTitle, $options);
                if($result && $flag == false){
                    self::$data['body']['content'] = $result->mText;
                }else if($result && $flag == true){
                    //匹配图片/文本
                    self::matchingText($result->mText);
                }
            }
        }
        if( $flag == false ){
            self::getTemplateData();
        }
        return self::$data;
    }


    //获取模板推荐
    static  function getTemplateData(){

        self::getContent( self::$templateName,true );
    }


    //正则匹配图片
    static function matchingText( $text ){

        $arr = array();
        $search = '/<li>(.*?)<\/li>/is';
        preg_match_all($search,$text,$r,PREG_SET_ORDER );
        foreach($r as $k=>$v){
            $arr[] = explode('|',$v[1]);
        }
        $pattern ='<img.*?src="(.*?)">';
        foreach($arr as $kk=>&$vv){
            preg_match($pattern,$vv[1],$matches);
            if($vv[0] == '广告'){
                $vv[] = DataSynchronization::imagePath($matches[1],110,82,true);
                self::$data['Advertise'][] = $vv;
            }else{
                $vv[] = DataSynchronization::imagePath($matches[1],149,104,true);
                self::$data['recomm'][] = $vv;
            }
        }
    }

    //获取text_id
    static function getTextIdByTitle( $pageTitle = '',$flag ){

        $dbw = wfGetDB( DB_SLAVE );
        $where['page_title'] =  $pageTitle;
        if( $flag == true ){
            $where['page_namespace'] = 10;
        }
        return $dbw->selectRow(
            array('page','revision'),
            array('rev_text_id','rev_page'),
            $where,
            __METHOD__,
            array(),
            array(
                'revision' => array( 'LEFT JOIN', 'rev_id=page_latest' ),
            )
        );
    }

    //获取text指定ID内容
    static function getTextContentById( $old_id ){

        $dbw = wfGetDB( DB_SLAVE );
        return $dbw->selectRow(
            'text',
            array('old_text'),
            array(
                'old_id'=>$old_id
            )
        );
    }

    //获取页面更新时间
    static function getPageUpdateTime( $page_id ){

        $dbr = wfGetDB( DB_MASTER );
        return $dbr->selectRow(
            'page_addons',
            "last_edit_user,DATE_FORMAT(pa_timestamp,'%Y-%m-%d') as time",
            array(
                'page_id'=>$page_id
            )
        );
    }
}