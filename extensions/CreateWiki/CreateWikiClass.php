<?php
class CreateWikiClass{

    //站点表
    static $table_name = 'joyme_sites';

    /**
     * Adds a new Joyme Sites to the database.
     * @param
     * @return Integer:boole
     */
    static function add_Joyme_Sites( $params = array() ){

        $dbw = wfGetDB( DB_MASTER );
        $ret = $dbw->insert(
            self::$table_name,
            $params,
            __METHOD__
        );
        if ( $ret ) {
            $id = $dbw->insertId();
            $dbw->commit();
            return $id;
        }
        return false;
    }

    /**
     * find wiki key
     * @param
     * @return Integer:object
     */
    static function find_Joyme_Key_Exist( $key ){

        $dbw = wfGetDB( DB_MASTER );
        return $dbw->selectRow(
            self::$table_name,
            '*',
            array(
                'site_key'=>$key
            )
        );
    }


    /**
     * list data
     * @param
     * @return Integer:object
     */
    static function joyme_Site_List( $limit , $skip ,$where){

        $dbw = wfGetDB( DB_MASTER );
        return $dbw->select(
            self::$table_name,
            '*',
            $where,
            __METHOD__,
            array(
                'ORDER BY'=>'site_id desc',
                'LIMIT'=>$limit,
                'OFFSET'=>$skip
            )
        );
    }

    /**
     * count
     * @param
     * @return Integer:count
     */
    static function joyme_Site_Count( $where ){

        $dbw = wfGetDB( DB_MASTER );
        return $dbw->selectRowCount(
            self::$table_name,
            '*',
            $where
        );
    }


    static function update_Joyme_Site( $key,$icon ){

        $dbw = wfGetDB( DB_MASTER );
        $dbw->update(
            self::$table_name,
            array(
                'site_icon'=>$icon
            ),
            array(
                'site_key'=>$key
            )
        );
        $dbw->commit();
        return true;
    }

    static function getAllWikiTime(){

        $dbw = wfGetDB( DB_MASTER );
        return $dbw->select(
            self::$table_name,
            "distinct FROM_UNIXTIME( create_time, '%Y-%m' ) as time "
        );
    }
}