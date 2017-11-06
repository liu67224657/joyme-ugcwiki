#!/usr/bin/env php
<?php
/**
 * Description: 后台批量执行runjobs任务
 * Author: gradydong
 * Date: 2017/5/3
 * Time: 11:37
 * Copyright: Joyme.com
 */

if(empty($argv[1])){
    echo 'no argv[1]';exit;
}
if(empty($argv[2])){
    echo 'no argv[2]';exit;
}

$_SERVER['HTTP_HOST'] = 'wiki.joyme.'.$argv[2];
$_SERVER['REQUEST_URI'] = '/'.$argv[1].'/';
$_SERVER['QUERY_STRING'] = '';


require_once __DIR__ . '/Maintenance.php';

class MultipleRunJobs extends Maintenance {

    function __construct() {
        parent::__construct();
        $this->mDescription = "MediaWiki Multiple RunJobs";
    }


    function execute() {
        $this->output( "starttime ".date('Y-m-d H:i:s')." \n\n" );
        $this->selectwikikeys();
        $this->output( "endtime ".date('Y-m-d H:i:s')." \n\n" );

    }

    public function selectwikikeys(){
        $dbr = wfGetDB(DB_SLAVE);
        $wikilist = $dbr->select(
            'joyme_sites',
            'site_key',
            array(
                'is_new'=>1
            ),
            __METHOD__,
            array('limit'=>1000)
        );
        if($wikilist){
            foreach($wikilist as $k=>$v){
                if( $v->site_key != 'home' ){
                    $this->checkrunjob($v->site_key);
                }
            }
        }else{
            $this->output( "no new ugcwiki \n\n" );
        }
    }

    /**
     * 判断wiki是否存在
     */
    public function checkrunjob( $wikikey ){
        global $wgEnv,$IP;
        $dbr = wfGetDB(DB_SLAVE);
        $rs = $dbr->selectDB($wikikey.'wiki');
        if($rs != false){
            $result = @shell_exec("php $IP/maintenance/runJobs.php $wikikey $wgEnv");
            if($result){
                $this->output( $wikikey." runjobresult：".$result." \n\n" );
            }else{
                $this->output( $wikikey." runjob is 0 \n\n" );
            }
            $this->output( "checkrunjob ".$wikikey." done\n\n" );
        }

    }

}

$maintClass = 'MultipleRunJobs';
require_once RUN_MAINTENANCE_IF_MAIN;
