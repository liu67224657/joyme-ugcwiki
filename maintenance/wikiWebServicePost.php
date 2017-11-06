<?php
/**
 * Description:
 * Author: gradydong
 * Date: 2017/7/6
 * Time: 15:55
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
use Joyme\net\Curl;

class WikiWebServicePost extends Maintenance {

    function __construct() {
        parent::__construct();
        $this->mDescription = "MediaWiki wikiindex to wikiservice";
    }

    function execute() {
        global $wgEnv;
        $this->output( "starttime ".date('Y-m-d H:i:s')." \n\n" );
        $this->indexpost();
        $this->output( "endtime ".date('Y-m-d H:i:s')." \n\n" );
    }


    public function indexpost()
    {
        global $wgEnv, $wgWikiname, $wgSiteGameTitle;
        $dbr = wfGetDB( DB_SLAVE );
        $results = $dbr->selectRow(
            'page',
            array("page_touched"),
            array(
                'page_id' => 1
            ),
            __METHOD__
        );
        if ($results) {
            if($results->page_touched){
                $page_touched = (strtotime($results->page_touched)+28800);
                $publishtime = floatval($page_touched) * 1000;
            }else{
                $publishtime = time()*1000;
            }
        }else{
            $publishtime = time()*1000;
        }
        $curl = new Curl();
        $data = array(
            'wikikey' => $wgWikiname,
            'title' => '首页',
            'wikiname' => $wgSiteGameTitle,
            'weburl' => 'http://wiki.joyme.' . $wgEnv . '/' . $wgWikiname . '/首页?useskin=MediaWikiBootstrap2',
            'publishtime' => $publishtime
        );
        $url = 'http://wikiservice.joyme.' . $wgEnv . '/api/wiki/content/wikipost';
        $res = $curl->Post($url, $data);
        $this->output( $res." \n\n" );
        $this->output( $wgWikiname."wiki done！\n\n" );
    }

    public function com()
    {
        $dbr = wfGetDB(DB_SLAVE);

        $wikilist = $dbr->select(
            'joyme_sites',
            'site_key,site_name',
            array(),
            __METHOD__,
            array('limit'=>1000)
        );
        if($wikilist){
            foreach($wikilist as $k=>$v){
                if( $v->site_key && $v->site_name && $v->site_key != 'home' ){
                    $this->wikiservice($v->site_key,$v->site_name);
                }
            }
        }else{
            $this->output( "no new ugcwiki \n\n" );
        }
    }

    public function wikiservice($wikikey,$wikiname)
    {
        global $wgEnv;
        $dbr = wfGetDB(DB_SLAVE);
        $rs = $dbr->selectDB( $wikikey.'wiki' );
        if($rs == false){
            $this->error( $wikikey.'wiki  failed', true );
        }
        $results = $dbr->selectRow(
            'page',
            array("page_touched"),
            array(
                'page_id' => 1
            ),
            __METHOD__
        );
        if ($results) {
            if($results->page_touched){
                $page_touched = (strtotime($results->page_touched)+28800);
                $publishtime = floatval($page_touched) * 1000;
            }else{
                $publishtime = time()*1000;
            }
        }else{
            $publishtime = time()*1000;
        }

        $curl = new Curl();
        $data = array(
            'wikikey' => $wikikey,
            'title' => '首页',
            'wikiname' => $wikiname,
            'weburl' => 'http://wiki.joyme.' . $wgEnv . '/' . $wikikey . '/首页?useskin=MediaWikiBootstrap2',
            'publishtime' => $publishtime
        );
        $url = 'http://wikiservice.joyme.' . $wgEnv . '/api/wiki/content/wikipost';
        $res = $curl->Post($url, $data);
        $this->output( $res." \n\n" );
        $this->output( $wikikey."wiki done！\n\n" );
    }

}

$maintClass = 'WikiWebServicePost';
require_once RUN_MAINTENANCE_IF_MAIN;