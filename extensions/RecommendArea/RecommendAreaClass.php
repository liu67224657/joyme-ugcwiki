<?php
/**
 * RecommendUsers class
 */
class RecommendAreaClass{

    //获取推荐区数据 rev_text_id
    static function getRecommendQueryInfo( $pageTitle ){

        $dbw = wfGetDB( DB_SLAVE );
        return $dbw->selectRow(
            array('page','revision'),
            array('rev_text_id'),
            array(
                'page_title'=>$pageTitle,
                'page_namespace' => 3000
            ),
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

}