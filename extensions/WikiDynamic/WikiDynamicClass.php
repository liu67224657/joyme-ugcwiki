<?php
use Joyme\page\Page;
class WikiDynamicClass{

    const perpage = 50;

    static $pagebarnum = 10;

    static function listData( $pb_page = 1,$type1 = 1,$type2=0 ,$day = 0,$url=false){

        if(isMobile()){
            self::$pagebarnum = 5;
        }

        $return = array(
            'rs'=>0,
            'result'=>array(

            )
        );
        $type = '';
        if( $type1 == $type2 ){
            $type = $type1;
        }
        $conditions = array(
            'day' =>$day,
            'page_type'=>$type

        );
        $time = date('Y-m-d', strtotime('-'.$day.' days'));
        $skip = ($pb_page-1)*self::perpage;
        $dbw = wfGetDB( DB_MASTER );
        $res = $dbw->select(
            'recentchanges',
            array(
                'rc_user_text','rc_title',
                'rc_new',
                'DATE_FORMAT(rc_timestamp,"%Y-%m-%d %H:%i:%s") as time'
            ),
            array(
                'DATE_FORMAT(rc_timestamp,"%Y-%m-%d")>"'.$time.'"',
                'rc_new'=>array(
                    'in'=>$type1,$type2
                ),
                'rc_namespace'=>0,
            ),
            __METHOD__,
            array(
                'ORDER BY' =>'rc_timestamp DESC',
                'LIMIT'=>self::perpage,
                'OFFSET'=>$skip
            )
        );
        $total = self::selectCount( $type1 , $type2 , $day );
        if($total){
            $data = array();
            foreach($res as $k=>$v){
                $data[] = array(
                    'rc_user_text'=>$v->rc_user_text,
                    'rc_title'=>$v->rc_title,
                    'rc_new'=>$v->rc_new,
                    'time'=>$v->time
                );
            }
            $_page = new Page(array('total' => $total,'perpage'=>self::perpage,'nowindex'=>$pb_page,'pagebarnum'=>self::$pagebarnum,'url'=>$url,'classname'=>array( 'main_page'=>'paging1','active'=>'on')));
            $page_str = $_page->show(2,$conditions);
            $return = array(
                'rs'=>1,
                'result'=>array(
                    'data'=>$data,
                    'page'=>$page_str,
                    'max_page'=>ceil($total / self::perpage)
                )
            );
        }
        return $return;
    }


    static function selectCount($type1 = 1,$type2=0 ,$day = 0 ){

        $dbw = wfGetDB( DB_MASTER );
        $time = date('Y-m-d', strtotime('-'.$day.' days'));
        return $dbw->selectRowCount(
            'recentchanges',
            array(
               '*'
            ),
            array(
                'DATE_FORMAT(rc_timestamp,"%Y-%m-%d")>"'.$time.'"',
                'rc_new'=>array(
                    'in'=>$type1,$type2
                ),
                'rc_namespace'=>0,
            ),
            __METHOD__
        );
    }
}