<?php

/**
 * Description:用户管理wiki-API
 * Author: gradydong
 * Date: 2016/11/24
 * Time: 11:24
 * Copyright: Joyme.com
 */
class JUserMWikiAPI extends ApiBase
{
    private $user;
    public static $site_default_icon = "/extensions/CreateWiki/style/site_default.png";

    public function execute()
    {
        $userid = $this->getMain()->getVal( 'userid' );

        $this->user = User::newFromId($userid);
        if($this->user->whoIs($userid) == false){
            $res = array('msg'=>'用户不存在','rs'=>-10104);
            $this->getResult()->addValue( null, $this->getModuleName(), $res );
            return true;
        }

        $usermwikis = $this->getWikiLists($userid);
        $usermwikis['rs'] = 1;
        $this->getResult()->addValue( null , $this->getModuleName(), $usermwikis );
        return true;
    }


    public function getWikiLists($userid){
        global $wgEnv,$wgResourceBasePath;
        $userprofile = new UserProfile($this->user);
        //管理wiki
        $manageWikis = $userprofile->getUserWikis($userid,1,5);
        if (!empty($manageWikis)) {
            $site_ids = array_column($manageWikis,'site_id');
            $joymesite = new JoymeSite();
            $siteinfos = $joymesite->getSiteInfo($site_ids);
            if($siteinfos){
                $site_keys = array_column($siteinfos,'site_key','site_id');
                $site_icons = array_column($siteinfos,'site_icon','site_id');
            }
            foreach ($manageWikis as $mk => $manageWiki) {
                if(isset($site_keys[$manageWiki['site_id']])
                    && $site_keys[$manageWiki['site_id']]
                ){
                    $manageWikis[$mk]['site_url'] = 'http://wiki.joyme.'.$wgEnv.'/'.$site_keys[$manageWiki['site_id']].'/%E9%A6%96%E9%A1%B5';
                }else{
                    $manageWikis[$mk]['site_url'] = '';
                }
                if(isset($site_icons[$manageWiki['site_id']])
                    && $site_icons[$manageWiki['site_id']]
                ){
                    $manageWikis[$mk]['site_icon'] = $site_icons[$manageWiki['site_id']];
                }else{
                    $manageWikis[$mk]['site_icon'] = $wgResourceBasePath.$this::$site_default_icon;
                }
            }
            $joymewikiuser = new JoymeWikiUser();
            $ua = $joymewikiuser->getUserAddition($userid);
            if($ua){
                $editcount = $ua[1]['total_edit_count'];
            }else{
                $editcount = 0;
            }
            $mwcount = $userprofile->getUserWikisCount($userid,1);
            $mwikis = array(
                'editcount' => $editcount,
                'mwcount' => $mwcount,
                'mwiki' => $manageWikis
            );
        }else{
            $mwikis = array(
                'editcount' => '0',
                'mwcount' => '0',
                'mwiki' => array()
            );
        }
        return $mwikis;

    }

}