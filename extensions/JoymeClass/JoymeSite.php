<?php

/**
 * Description: 着迷站点类
 * Author: gradydong
 * Date: 2016/7/8
 * Time: 12:39
 * Copyright: Joyme.com
 */
use Joyme\net\Curl;
use Joyme\core\Log;

class JoymeSite
{
    public static $site_default_icon = "/extensions/CreateWiki/style/site_default.png";

    public static $provices = array(
        '1' => '北京市',
        '2' => '天津市',
        '3' => '上海市',
        '4' => '重庆市',
        '5' => '河北省',
        '6' => '山西省',
        '7' => '辽宁省',
        '8' => '吉林省',
        '9' => '黑龙江省',
        '10' => '江苏省',
        '11' => '浙江省',
        '12' => '安徽省',
        '13' => '福建省',
        '14' => '江西省',
        '15' => '广东省',
        '16' => '山东省',
        '17' => '河南省',
        '18' => '湖北省',
        '19' => '湖南省',
        '20' => '甘肃省',
        '21' => '四川省',
        '22' => '贵州省',
        '23' => '海南省',
        '24' => '云南省',
        '25' => '青海省',
        '26' => '陕西省',
        '27' => '广西',
        '28' => '西藏',
        '29' => '宁夏',
        '30' => '新疆',
        '31' => '内蒙古',
        '32' => '台湾',
        '33' => '香港',
        '34' => '澳门',
    );

    public function __construct()
    {

    }


    /**
     * 修改站点每天编辑次数
     */
    public static function editSiteEditCountLog()
    {
        global $wgSiteId;

        if (empty($wgSiteId)) {
            return false;
        }

        $dbr = wfGetDB(DB_SLAVE);
        $res = $dbr->selectRowCount(
            'site_editcount_log',
            '*',
            array(
                'site_id' => $wgSiteId,
                'edit_date' => date('Y-m-d')
            ),
            __METHOD__
        );
        $dbw = wfGetDB(DB_MASTER);
        if ($res) {
            $ret = $dbw->update(
                'site_editcount_log',
                array("edit_count=edit_count+1"),
                array(
                    'site_id' => $wgSiteId,
                    'edit_date' => date('Y-m-d')
                ),
                __METHOD__
            );
            $dbw->commit(__METHOD__);
            return $ret;
        } else {
            return $dbw->insert(
                'site_editcount_log',
                array(
                    'site_id' => $wgSiteId,
                    'edit_count' => 1,
                    'edit_date' => date('Y-m-d'),
                ),
                __METHOD__
            );
        }
    }


    /**
     * 更新站点页面总数
     */
    public static function updateSitePageCount()
    {
        global $wgSiteId;

        if (empty($wgSiteId)) {
            return false;
        }
        $dbr = wfGetDB(DB_SLAVE);
        $pageCount = $dbr->selectRowCount(
            'page',
            '1',
            array(
                'page_namespace' => 0
            )
        );
        if ($pageCount) {
            $dbw = wfGetDB(DB_MASTER);
            $ret = $dbw->update(
                'joyme_sites',
                array(
                    'site_page_count' => $pageCount
                ),
                array('site_id' => $wgSiteId),
                __METHOD__
            );
            $dbw->commit(__METHOD__);
            return $ret;
        } else {
            return false;
        }
    }


    /**
     * 更新站点编辑总数
     */
    public static function updateSiteEditCount()
    {
        global $wgSiteId;

        if (empty($wgSiteId)) {
            return false;
        }
        $dbw = wfGetDB(DB_MASTER);
        $ret = $dbw->update(
            'joyme_sites',
            array("site_edit_count=site_edit_count+1"),
            array('site_id' => $wgSiteId),
            __METHOD__
        );
        $dbw->commit(__METHOD__);
        return $ret;
    }


    /**
     * 获取站点昨日编辑次数
     */
    public function getSiteYesCount($site_ids)
    {
        if (empty($site_ids)) {
            return false;
        }
        $dbr = wfGetDB(DB_SLAVE);
        $result = array();
        if (is_array($site_ids)) {
            $keystr = implode(',', $site_ids);
            $where = array(
                'site_id in ( ' . $keystr . ')'
            );
        } else {
            $where = array(
                'site_id' => $site_ids
            );
        }
        $where['edit_date'] = date("Y-m-d", strtotime("-1 day"));

        $res = $dbr->select(
            'site_editcount_log',
            array(
                'site_id',
                'edit_count',
            ),
            $where
        );
        if ($res) {
            foreach ($res as $k => $row) {
                $result[$k]['site_id'] = $row->site_id;
                $result[$k]['edit_count'] = $row->edit_count;
            }
        }

        return $result;
    }

    /**
     * 获取站点关注人数
     */
    public function getSiteUserFollowCount($site_ids)
    {
        if (empty($site_ids)) {
            return false;
        }
        $dbr = wfGetDB(DB_SLAVE);
        $result = array();
        if (is_array($site_ids)) {
            $keystr = implode(',', $site_ids);
            $where = array(
                'site_id in ( ' . $keystr . ')'
            );
        } else {
            $where = array(
                'site_id' => $site_ids
            );
        }
//        $where['status'] = 3;

        $res = $dbr->select(
            'user_site_relation',
            array(
                'site_id',
                'count(user_id) as usercount',
            ),
            $where,
            __METHOD__,
            array(
                'GROUP BY' => "site_id"
            )
        );
        if ($res) {
            foreach ($res as $k => $row) {
                $result[$k]['site_id'] = $row->site_id;
                $result[$k]['follow_user'] = $row->usercount;
            }
        }

        return $result;
    }

    //更新站点编辑人数
    public static function updateSiteEditUserCount()
    {
        global $wgSiteId;

        if (empty($wgSiteId)) {
            return false;
        }
        $dbr = wfGetDB(DB_SLAVE);
        $editusers = $dbr->selectRowCount(
            'revision',
            '*',
            array('rev_user != 0'),
            __METHOD__,
            array(
                'GROUP BY' => 'rev_user'
            )
        );
        if ($editusers) {
            $dbw = wfGetDB(DB_MASTER);
            $ret = $dbw->update(
                'joyme_sites',
                array(
                    'site_edituser_count' => $editusers
                ),
                array('site_id' => $wgSiteId),
                __METHOD__
            );
            $dbw->commit(__METHOD__);
            return $ret;
        } else {
            return false;
        }
    }


    /**
     * 获取wiki站点信息
     */
    public static function getSiteInfo($site_ids)
    {
        if (empty($site_ids)) {
            return false;
        }
        $dbr = wfGetDB(DB_SLAVE);
        $result = array();
        if (is_array($site_ids)) {
            $keystr = implode(',', $site_ids);
            $where = array(
                'site_id in ( ' . $keystr . ')'
            );
        } else {
            $where = array(
                'site_id' => $site_ids
            );
        }
        $res = $dbr->select(
            'joyme_sites',
            '*',
            $where
        );
        if ($res) {
            $joymesite = new JoymeSite();
            //昨日编辑次数
            $siteyescounts = $joymesite->getSiteYesCount($site_ids);
            if ($siteyescounts) {
                $sycounts = array_column($siteyescounts, 'edit_count', 'site_id');
            }
            //关注人数
            $sitefollowusers = $joymesite->getSiteUserFollowCount($site_ids);
            if ($sitefollowusers) {
                $fucounts = array_column($sitefollowusers, 'follow_user', 'site_id');
            }
            foreach ($res as $k => $row) {
                $result[$k]['site_id'] = $row->site_id;
                $result[$k]['site_name'] = $row->site_name;
                $result[$k]['site_key'] = $row->site_key;
                $result[$k]['site_type'] = $row->site_type;
                $result[$k]['site_icon'] = $row->site_icon;
                $result[$k]['page_count'] = $row->site_page_count;
                $result[$k]['edit_count'] = $row->site_edit_count;
                $result[$k]['edituser_count'] = $row->site_edituser_count;
                //昨日编辑次数
                if (isset($sycounts[$row->site_id])
                    && $sycounts[$row->site_id]
                ) {
                    $result[$k]['yes_editcount'] = $sycounts[$row->site_id];
                } else {
                    $result[$k]['yes_editcount'] = 0;
                }
                //关注人数
                if (isset($fucounts[$row->site_id])
                    && $fucounts[$row->site_id]
                ) {
                    $follow = JoymeWikiUser::getJoymeSiteFollowNum($row->site_id, $fucounts[$row->site_id]);
//                  $result[$k]['follow_usercount'] = $fucounts[$row->site_id];
                    $result[$k]['follow_usercount'] = $follow->site_follow;
                } else {
                    $result[$k]['follow_usercount'] = 0;
                }
            }
        }

        return $result;
    }

    /**
     * 我的wiki
     */
    public static function UserWikiInfo($userid)
    {

        global $joyme_u_adminid, $wgEnv, $wgResourceBasePath;
        if (empty($userid) || !is_numeric($userid)) {
            return array(
                'code' => '0',
                'msg' => '参数错误'
            );
        }

        $joymewikiuser = new JoymeWikiUser();
        $userwikicount = self::getUserWikisCount($userid);
        $userwikis = array(
            'manageWikis' => array(),
            'contributeWikis' => array(),
            'followWikis' => array(),
            'manageWikis_hasnext' => 0,
            'contributeWikis_hasnext' => 0,
            'followWikis_hasnext' => 0
        );
        if (!empty($userwikicount)) {
            $userallwikis = self::getUserWikis($userid);
            $site_ids = array_column($userallwikis, 'site_id');
            if ($site_ids) {
                $joymesite = new JoymeSite();
                $siteinfos = $joymesite->getSiteInfo($site_ids);
                if ($siteinfos) {
                    $site_names = array_column($siteinfos, 'site_name', 'site_id');
                    $site_keys = array_column($siteinfos, 'site_key', 'site_id');
                    $site_icons = array_column($siteinfos, 'site_icon', 'site_id');
                    $page_counts = array_column($siteinfos, 'page_count', 'site_id');
                    $edit_counts = array_column($siteinfos, 'edit_count', 'site_id');
                    $edituser_counts = array_column($siteinfos, 'edituser_count', 'site_id');
                    $yes_editcounts = array_column($siteinfos, 'yes_editcount', 'site_id');
                    $follow_usercounts = array_column($siteinfos, 'follow_usercount', 'site_id');
                }
                $offercounts = $joymewikiuser->getUserSiteOfferCount($userid, $site_ids);
                if ($offercounts) {
                    $offer_counts = array_column($offercounts, 'offer_count', 'site_id');
                }
            }
            //管理wiki
            $manageWikis = self::getUserWikis($userid, 1, 3);
            if (!empty($manageWikis)) {
                foreach ($manageWikis as $mk => $manageWiki) {
                    if (isset($site_keys[$manageWiki['site_id']])
                        && $site_keys[$manageWiki['site_id']]
                    ) {
                        $manageWikis[$mk]['site_key'] = $site_keys[$manageWiki['site_id']];
                        $manageWikis[$mk]['site_url'] = 'http://wiki.joyme.' . $wgEnv . '/' . $site_keys[$manageWiki['site_id']] . '/%E9%A6%96%E9%A1%B5';
                    } else {
                        $manageWikis[$mk]['site_key'] = '';
                        $manageWikis[$mk]['site_url'] = '';
                    }
                    if (isset($site_icons[$manageWiki['site_id']])
                        && $site_icons[$manageWiki['site_id']]
                    ) {
                        $manageWikis[$mk]['site_icon'] = $site_icons[$manageWiki['site_id']];
                    } else {
                        $manageWikis[$mk]['site_icon'] = $wgResourceBasePath . JoymeSite::$site_default_icon;
                    }
                    //wiki名称
                    if (isset($site_names[$manageWiki['site_id']])
                        && $site_names[$manageWiki['site_id']]
                    ) {
                        $manageWikis[$mk]['site_name'] = $site_names[$manageWiki['site_id']];
                    } else {
                        $manageWikis[$mk]['site_name'] = '';
                    }
                    //页面总数量
                    if (isset($page_counts[$manageWiki['site_id']])
                        && $page_counts[$manageWiki['site_id']]
                    ) {
                        $manageWikis[$mk]['page_count'] = $page_counts[$manageWiki['site_id']];
                    } else {
                        $manageWikis[$mk]['page_count'] = 0;
                    }
                    //编辑总次数
                    if (isset($edit_counts[$manageWiki['site_id']])
                        && $edit_counts[$manageWiki['site_id']]
                    ) {
                        $manageWikis[$mk]['edit_count'] = $edit_counts[$manageWiki['site_id']];
                    } else {
                        $manageWikis[$mk]['edit_count'] = 0;
                    }

                    if (isset($follow_usercounts[$manageWiki['site_id']])
                        && $follow_usercounts[$manageWiki['site_id']]
                    ) {
                        $manageWikis[$mk]['follow_usercount'] = $follow_usercounts[$manageWiki['site_id']];
                    } else {
                        $manageWikis[$mk]['follow_usercount'] = 0;
                    }

                    if (isset($edituser_counts[$manageWiki['site_id']])
                        && $edituser_counts[$manageWiki['site_id']]
                    ) {
                        $manageWikis[$mk]['edituser_count'] = $edituser_counts[$manageWiki['site_id']];
                    } else {
                        $manageWikis[$mk]['edituser_count'] = 0;
                    }

                    if (isset($yes_editcounts[$manageWiki['site_id']])
                        && $yes_editcounts[$manageWiki['site_id']]
                    ) {
                        $manageWikis[$mk]['yes_editcount'] = $yes_editcounts[$manageWiki['site_id']];
                    } else {
                        $manageWikis[$mk]['yes_editcount'] = 0;
                    }
                }
                $userwikis['manageWikis'] = $manageWikis;
                //查询是否有下一页
                $manageWikis_count = self::getUserWikisCount($userid, 1);
                if ($manageWikis_count > 3) {
                    $userwikis['manageWikis_hasnext'] = 1;
                }
            }
            //如果是超级管理员，不显示贡献wiki和关注wiki，都是管理wiki
            if (!in_array($userid, $joyme_u_adminid)) {
                //贡献wiki
                $contributeWikis = self::getUserWikis($userid, 2, 3);
                if (!empty($contributeWikis)) {
                    foreach ($contributeWikis as $ck => $contributeWiki) {
                        if (isset($site_keys[$contributeWiki['site_id']])
                            && $site_keys[$contributeWiki['site_id']]
                        ) {
                            $contributeWikis[$ck]['site_key'] = $site_keys[$contributeWiki['site_id']];
                            $contributeWikis[$ck]['site_url'] = 'http://wiki.joyme.' . $wgEnv . '/' . $site_keys[$contributeWiki['site_id']] . '/%E9%A6%96%E9%A1%B5';
                        } else {
                            $contributeWikis[$ck]['site_key'] = '';
                            $contributeWikis[$ck]['site_url'] = '';
                        }

                        if (isset($site_icons[$contributeWiki['site_id']])
                            && $site_icons[$contributeWiki['site_id']]
                        ) {
                            $contributeWikis[$ck]['site_icon'] = $site_icons[$contributeWiki['site_id']];
                        } else {
                            $contributeWikis[$ck]['site_icon'] = $wgResourceBasePath . JoymeSite::$site_default_icon;
                        }
                        //wiki名称
                        if (isset($site_names[$contributeWiki['site_id']])
                            && $site_names[$contributeWiki['site_id']]
                        ) {
                            $contributeWikis[$ck]['site_name'] = $site_names[$contributeWiki['site_id']];
                        } else {
                            $contributeWikis[$ck]['site_name'] = '';
                        }

                        if (isset($offer_counts[$contributeWiki['site_id']])
                            && $offer_counts[$contributeWiki['site_id']]
                        ) {
                            $contributeWikis[$ck]['offer_count'] = $offer_counts[$contributeWiki['site_id']];
                        } else {
                            $contributeWikis[$ck]['offer_count'] = 0;
                        }

                        if (isset($yes_editcounts[$contributeWiki['site_id']])
                            && $yes_editcounts[$contributeWiki['site_id']]
                        ) {
                            $contributeWikis[$ck]['yes_editcount'] = $yes_editcounts[$contributeWiki['site_id']];
                        } else {
                            $contributeWikis[$ck]['yes_editcount'] = 0;
                        }
                    }
                    $userwikis['contributeWikis'] = $contributeWikis;
                    //查询是否有下一页
                    $contributeWikis_count = self::getUserWikisCount($userid, 2);
                    if ($contributeWikis_count > 3) {
                        $userwikis['contributeWikis_hasnext'] = 1;
                    }
                }

                //关注wiki
                $followWikis = self::getUserWikis($userid, 3, 3);
                if (!empty($followWikis)) {
                    foreach ($followWikis as $fk => $followWiki) {
                        if (isset($site_keys[$followWiki['site_id']])
                            && $site_keys[$followWiki['site_id']]
                        ) {
                            $followWikis[$fk]['site_key'] = $site_keys[$followWiki['site_id']];
                            $followWikis[$fk]['site_url'] = 'http://wiki.joyme.' . $wgEnv . '/' . $site_keys[$followWiki['site_id']] . '/%E9%A6%96%E9%A1%B5';
                        } else {
                            $followWikis[$fk]['site_key'] = '';
                        }

                        if (isset($site_icons[$followWiki['site_id']])
                            && $site_icons[$followWiki['site_id']]
                        ) {
                            $followWikis[$fk]['site_icon'] = $site_icons[$followWiki['site_id']];
                        } else {
                            $followWikis[$fk]['site_icon'] = $wgResourceBasePath . JoymeSite::$site_default_icon;
                        }

                        //wiki名称
                        if (isset($site_names[$followWiki['site_id']])
                            && $site_names[$followWiki['site_id']]
                        ) {
                            $followWikis[$fk]['site_name'] = $site_names[$followWiki['site_id']];
                        } else {
                            $followWikis[$fk]['site_name'] = '';
                        }
                        //页面总数量
                        if (isset($page_counts[$followWiki['site_id']])
                            && $page_counts[$followWiki['site_id']]
                        ) {
                            $followWikis[$fk]['page_count'] = $page_counts[$followWiki['site_id']];
                        } else {
                            $followWikis[$fk]['page_count'] = 0;
                        }

                        if (isset($yes_editcounts[$followWiki['site_id']])
                            && $yes_editcounts[$followWiki['site_id']]
                        ) {
                            $followWikis[$fk]['yes_editcount'] = $yes_editcounts[$followWiki['site_id']];
                        } else {
                            $followWikis[$fk]['yes_editcount'] = 0;
                        }
                    }
                    $userwikis['followWikis'] = $followWikis;
                    //查询是否有下一页
                    $followWikis_count = self::getUserWikisCount($userid, 3);
                    if ($followWikis_count > 3) {
                        $userwikis['followWikis_hasnext'] = 1;
                    }
                }
            }
        }

        $hotwikis = (array)RecommendWiki::getWikiInfo();
        if ($hotwikis) {
            $wiki_keys = array_column($hotwikis, 'site_key');
            $joymewikiuser = new JoymeWikiUser();
            $siteinfos = $joymewikiuser->getSiteInfo($wiki_keys);
            if ($siteinfos) {
                $site_names = array_column($siteinfos, 'site_name', 'site_key');
                $page_counts = array_column($siteinfos, 'page_count', 'site_key');
                $yes_editcounts = array_column($siteinfos, 'yes_editcount', 'site_key');
            }
            foreach ($hotwikis as $hk => $hwiki) {

                //wiki名称
                if (isset($site_names[$hwiki['site_key']])
                    && $site_names[$hwiki['site_key']]
                ) {
                    $hotwikis[$hk]['site_name'] = $site_names[$hwiki['site_key']];
                    $hotwikis[$hk]['site_url'] = 'http://wiki.joyme.' . $wgEnv . '/' . $hwiki['site_key'] . '/%E9%A6%96%E9%A1%B5';
                } else {
                    $hotwikis[$hk]['site_name'] = '';
                }
                //页面总数量
                if (isset($page_counts[$hwiki['site_key']])
                    && $page_counts[$hwiki['site_key']]
                ) {
                    $hotwikis[$hk]['page_count'] = $page_counts[$hwiki['site_key']];
                } else {
                    $hotwikis[$hk]['page_count'] = 0;
                }
                //昨日编辑数
                if (isset($yes_editcounts[$hwiki['site_key']])
                    && $yes_editcounts[$hwiki['site_key']]
                ) {
                    $hotwikis[$hk]['yes_editcount'] = $yes_editcounts[$hwiki['site_key']];
                } else {
                    $hotwikis[$hk]['yes_editcount'] = 0;
                }
            }
        }
        $userprofiles = array();
        $stats = new UserStats($userid, null);
        $stats_data = $stats->getUserStats();
        if ($stats_data) {
            $total_edit_count = isset($stats_data['total_edit_count']) && $stats_data['total_edit_count'] ? $stats_data['total_edit_count'] : 0;
            $total_edit_count = UserProfile::showFormatNumber($total_edit_count);
            $today_edit_count = $joymewikiuser->getUserTodayEditCount($userid);
            $today_edit_count = UserProfile::showFormatNumber($today_edit_count);
            $userprofiles = array(
                'total_edit_count' => $total_edit_count,
                'today_edit_count' => $today_edit_count
            );
        }
        $data = array(
            'code' => '1',
            'msg' => array(
                'userprofiles' => $userprofiles,
                'userwikis' => $userwikis,
                'hotwikis' => $hotwikis,
            )
        );


        return $data;
    }

    /**
     * 我的wiki点击加载更多
     * parameter wtype = array(
     *      mwiki => 管理wiki
     *      cwiki => 贡献wiki
     *      fwiki => 关注wiki
     * )
     */
    public static function ajaxUserWikiInfo($wtype, $userid, $page)
    {
        global $wgEnv, $wgResourceBasePath;
        if (empty($wtype) || empty($userid) || !is_numeric($userid) || empty($page)) {
            return array(
                'code' => '0',
                'msg' => '参数错误'
            );
        }
        $hasnext = 0;
        if ($wtype == 'mwiki') {
            $mwikis = self::getUserWikis($userid, 1, 3, $page);
            if ($mwikis) {
                $site_ids = array_column($mwikis, 'site_id');
                if ($site_ids) {
                    $joymesite = new JoymeSite();
                    $siteinfos = $joymesite->getSiteInfo($site_ids);
                    if ($siteinfos) {
                        $site_names = array_column($siteinfos, 'site_name', 'site_id');
                        $site_keys = array_column($siteinfos, 'site_key', 'site_id');
                        $site_icons = array_column($siteinfos, 'site_icon', 'site_id');
                        $page_counts = array_column($siteinfos, 'page_count', 'site_id');
                        $edit_counts = array_column($siteinfos, 'edit_count', 'site_id');
                        $edituser_counts = array_column($siteinfos, 'edituser_count', 'site_id');
                        $yes_editcounts = array_column($siteinfos, 'yes_editcount', 'site_id');
                        $follow_usercounts = array_column($siteinfos, 'follow_usercount', 'site_id');
                    }
                }

                foreach ($mwikis as $mk => $manageWiki) {
                    if (isset($site_keys[$manageWiki['site_id']])
                        && $site_keys[$manageWiki['site_id']]
                    ) {
                        $mwikis[$mk]['site_key'] = $site_keys[$manageWiki['site_id']];
                        $mwikis[$mk]['site_url'] = 'http://wiki.joyme.' . $wgEnv . '/' . $site_keys[$manageWiki['site_id']] . '/%E9%A6%96%E9%A1%B5';
                    } else {
                        $mwikis[$mk]['site_key'] = '';
                        $mwikis[$mk]['site_url'] = '';
                    }
                    if (isset($site_icons[$manageWiki['site_id']])
                        && $site_icons[$manageWiki['site_id']]
                    ) {
                        $mwikis[$mk]['site_icon'] = $site_icons[$manageWiki['site_id']];
                    } else {
                        $mwikis[$mk]['site_icon'] = $wgResourceBasePath . JoymeSite::$site_default_icon;
                    }
                    //wiki名称
                    if (isset($site_names[$manageWiki['site_id']])
                        && $site_names[$manageWiki['site_id']]
                    ) {
                        $mwikis[$mk]['site_name'] = $site_names[$manageWiki['site_id']];
                    } else {
                        $mwikis[$mk]['site_name'] = '';
                    }
                    //页面总数量
                    if (isset($page_counts[$manageWiki['site_id']])
                        && $page_counts[$manageWiki['site_id']]
                    ) {
                        $mwikis[$mk]['page_count'] = $page_counts[$manageWiki['site_id']];
                    } else {
                        $mwikis[$mk]['page_count'] = 0;
                    }
                    //编辑总次数
                    if (isset($edit_counts[$manageWiki['site_id']])
                        && $edit_counts[$manageWiki['site_id']]
                    ) {
                        $mwikis[$mk]['edit_count'] = $edit_counts[$manageWiki['site_id']];
                    } else {
                        $mwikis[$mk]['edit_count'] = 0;
                    }

                    if (isset($follow_usercounts[$manageWiki['site_id']])
                        && $follow_usercounts[$manageWiki['site_id']]
                    ) {
                        $mwikis[$mk]['follow_usercount'] = $follow_usercounts[$manageWiki['site_id']];
                    } else {
                        $mwikis[$mk]['follow_usercount'] = 0;
                    }

                    if (isset($edituser_counts[$manageWiki['site_id']])
                        && $edituser_counts[$manageWiki['site_id']]
                    ) {
                        $mwikis[$mk]['edituser_count'] = $edituser_counts[$manageWiki['site_id']];
                    } else {
                        $mwikis[$mk]['edituser_count'] = 0;
                    }

                    if (isset($yes_editcounts[$manageWiki['site_id']])
                        && $yes_editcounts[$manageWiki['site_id']]
                    ) {
                        $mwikis[$mk]['yes_editcount'] = $yes_editcounts[$manageWiki['site_id']];
                    } else {
                        $mwikis[$mk]['yes_editcount'] = 0;
                    }
                }

                //查询是否有下一页
                $followWikis_count = self::getUserWikisCount($userid, 1);
                if ($page * 3 < $followWikis_count) {
                    $hasnext = 1;
                }

                return array(
                    'code' => '1',
                    'msg' => $mwikis,
                    'hasnext' => $hasnext
                );

            } else {
                return array(
                    'code' => '0',
                    'msg' => array(),
                    'hasnext' => $hasnext
                );
            }
        } elseif ($wtype == 'cwiki') {
            $cwikis = self::getUserWikis($userid, 2, 3, $page);
            if ($cwikis) {
                $site_ids = array_column($cwikis, 'site_id');
                if ($site_ids) {
                    $joymesite = new JoymeSite();
                    $siteinfos = $joymesite->getSiteInfo($site_ids);
                    if ($siteinfos) {
                        $site_names = array_column($siteinfos, 'site_name', 'site_id');
                        $site_keys = array_column($siteinfos, 'site_key', 'site_id');
                        $site_icons = array_column($siteinfos, 'site_icon', 'site_id');
                        $yes_editcounts = array_column($siteinfos, 'yes_editcount', 'site_id');
                    }
                }
                foreach ($cwikis as $ck => $contributeWiki) {
                    if (isset($site_keys[$contributeWiki['site_id']])
                        && $site_keys[$contributeWiki['site_id']]
                    ) {
                        $cwikis[$ck]['site_key'] = $site_keys[$contributeWiki['site_id']];
                        $cwikis[$ck]['site_url'] = 'http://wiki.joyme.' . $wgEnv . '/' . $site_keys[$contributeWiki['site_id']] . '/%E9%A6%96%E9%A1%B5';
                    } else {
                        $cwikis[$ck]['site_key'] = '';
                        $cwikis[$ck]['site_url'] = '';
                    }

                    if (isset($site_icons[$contributeWiki['site_id']])
                        && $site_icons[$contributeWiki['site_id']]
                    ) {
                        $cwikis[$ck]['site_icon'] = $site_icons[$contributeWiki['site_id']];
                    } else {
                        $cwikis[$ck]['site_icon'] = $wgResourceBasePath . JoymeSite::$site_default_icon;
                    }
                    //wiki名称
                    if (isset($site_names[$contributeWiki['site_id']])
                        && $site_names[$contributeWiki['site_id']]
                    ) {
                        $cwikis[$ck]['site_name'] = $site_names[$contributeWiki['site_id']];
                    } else {
                        $cwikis[$ck]['site_name'] = '';
                    }

                    if (isset($offer_counts[$contributeWiki['site_id']])
                        && $offer_counts[$contributeWiki['site_id']]
                    ) {
                        $cwikis[$ck]['offer_count'] = $offer_counts[$contributeWiki['site_id']];
                    } else {
                        $cwikis[$ck]['offer_count'] = 0;
                    }

                    if (isset($yes_editcounts[$contributeWiki['site_id']])
                        && $yes_editcounts[$contributeWiki['site_id']]
                    ) {
                        $cwikis[$ck]['yes_editcount'] = $yes_editcounts[$contributeWiki['site_id']];
                    } else {
                        $cwikis[$ck]['yes_editcount'] = 0;
                    }
                }

                //查询是否有下一页
                $Wikis_count = self::getUserWikisCount($userid, 2);
                if ($page * 3 < $Wikis_count) {
                    $hasnext = 1;
                }

                return array(
                    'code' => '1',
                    'msg' => $cwikis,
                    'hasnext' => $hasnext
                );
            } else {
                return array(
                    'code' => '0',
                    'msg' => array(),
                    'hasnext' => $hasnext
                );
            }
        } elseif ($wtype == 'fwiki') {
            $fwikis = self::getUserWikis($userid, 3, 3, $page);
            if ($fwikis) {
                $site_ids = array_column($fwikis, 'site_id');
                if ($site_ids) {
                    $joymesite = new JoymeSite();
                    $siteinfos = $joymesite->getSiteInfo($site_ids);
                    if ($siteinfos) {
                        $site_names = array_column($siteinfos, 'site_name', 'site_id');
                        $site_keys = array_column($siteinfos, 'site_key', 'site_id');
                        $site_icons = array_column($siteinfos, 'site_icon', 'site_id');
                        $page_counts = array_column($siteinfos, 'page_count', 'site_id');
                        $yes_editcounts = array_column($siteinfos, 'yes_editcount', 'site_id');
                    }
                }
                foreach ($fwikis as $fk => $followWiki) {
                    if (isset($site_keys[$followWiki['site_id']])
                        && $site_keys[$followWiki['site_id']]
                    ) {
                        $fwikis[$fk]['site_key'] = $site_keys[$followWiki['site_id']];
                        $fwikis[$fk]['site_url'] = 'http://wiki.joyme.' . $wgEnv . '/' . $site_keys[$followWiki['site_id']] . '/%E9%A6%96%E9%A1%B5';
                    } else {
                        $fwikis[$fk]['site_key'] = '';
                    }

                    if (isset($site_icons[$followWiki['site_id']])
                        && $site_icons[$followWiki['site_id']]
                    ) {
                        $fwikis[$fk]['site_icon'] = $site_icons[$followWiki['site_id']];
                    } else {
                        $fwikis[$fk]['site_icon'] = $wgResourceBasePath . JoymeSite::$site_default_icon;
                    }

                    //wiki名称
                    if (isset($site_names[$followWiki['site_id']])
                        && $site_names[$followWiki['site_id']]
                    ) {
                        $fwikis[$fk]['site_name'] = $site_names[$followWiki['site_id']];
                    } else {
                        $fwikis[$fk]['site_name'] = '';
                    }
                    //页面总数量
                    if (isset($page_counts[$followWiki['site_id']])
                        && $page_counts[$followWiki['site_id']]
                    ) {
                        $fwikis[$fk]['page_count'] = $page_counts[$followWiki['site_id']];
                    } else {
                        $fwikis[$fk]['page_count'] = 0;
                    }

                    if (isset($yes_editcounts[$followWiki['site_id']])
                        && $yes_editcounts[$followWiki['site_id']]
                    ) {
                        $fwikis[$fk]['yes_editcount'] = $yes_editcounts[$followWiki['site_id']];
                    } else {
                        $fwikis[$fk]['yes_editcount'] = 0;
                    }
                }
                //查询是否有下一页
                $Wikis_count = self::getUserWikisCount($userid, 3);
                if ($page * 3 < $Wikis_count) {
                    $hasnext = 1;
                }
                return array(
                    'code' => '1',
                    'msg' => $fwikis,
                    'hasnext' => $hasnext
                );
            } else {
                return array(
                    'code' => '0',
                    'msg' => array(),
                    'hasnext' => $hasnext
                );
            }
        } else {
            return array(
                'code' => '0',
                'msg' => '参数错误'
            );
        }
    }

    public static function getUserWikis($user_id, $uw_status = '', $limit = 0, $offset = 0)
    {
        $where = array();
        $options = array();
        if (empty($user_id)) {
            return false;
        } else {
            $where['user_id'] = $user_id;
        }
        if ($uw_status) {
            $where['status'] = $uw_status;
        }

        if ($limit > 0) {
            $limitvalue = 0;
            if ($offset) {
                $limitvalue = ($offset * $limit) - $limit;
            }
            $options['LIMIT'] = $limit;
            $options['OFFSET'] = $limitvalue;
        }
        $options['ORDER BY'] = 'usr_id DESC';

        $dbr = wfGetDB(DB_SLAVE);
        $res = $dbr->select(
            'user_site_relation',
            'user_id,site_id,status',
            $where,
            __METHOD__,
            $options
        );

        $wikis = array();
        if ($res) {
            foreach ($res as $row) {
                $wikis[] = array(
                    'user_id' => $row->user_id,
                    'site_id' => $row->site_id,
                    'status' => $row->status
                );
            }
        }

        return $wikis;
    }

    public static function getUserWikisCount($user_id, $status = 0)
    {
        $where = array();
        if (empty($user_id)) {
            return false;
        } else {
            $where['user_id'] = $user_id;
        }
        if ($status) {
            $where['status'] = $status;
        }
        $dbr = wfGetDB(DB_SLAVE);
        return $dbr->selectRowCount(
            'user_site_relation',
            '*',
            $where
        );
    }

    public static function getPageCreator($pageid)
    {
        $dbr = wfGetDB(DB_SLAVE);
        return $dbr->selectRow(
            'revision',
            'rev_user',
            array(
                'rev_page' => $pageid
            ),
            __METHOD__,
            array(
                'ORDER BY' => 'rev_id ASC'
            )
        );
    }

    //修改页面url
    public static function changeSkinUrl($url)
    {
        global $wgRequest;
        $u = $wgRequest->getText('useskin');     // using $_GET["useskin"] had unwanted side effects
        if ($u) {
            if ($u != "") {
                # if there is already a "?" in the URL (like in "?title="),
                # then append "&useskin=", else "?useskin="
                if (strpos($url, '?') === false) {
                    $url .= "?useskin=" . $u;
                } else {
                    $url .= "&useskin=" . $u;
                }
            }
        }
        return $url;
    }

    //关于社交APP，wiki内容上报
    public static function wikiwebservicepost($title,$articleid,$wiki_key=null,$wiki_name=null,$publish_time=null)
    {
        global $wgEnv, $wgWikiname, $wgSiteGameTitle;
        if (!empty($title)) {
            $curl = new Curl();
            if(empty($publish_time)){
                $publishtime = self::getPageCreateTime($articleid);
            }else{
                $publishtime = $publish_time;
            }
            if($wiki_key){
                $wikikey = $wiki_key;
            }else{
                $wikikey = $wgWikiname;
            }
            if($wiki_name){
                $wikiname = $wiki_name;
            }else{
                $wikiname = $wgSiteGameTitle;
            }
            $data = array(
                'wikikey' => $wikikey,
                'title' => $title,
                'wikiname' => $wikiname,
                'weburl' => 'http://wiki.joyme.' . $wgEnv . '/' . $wikikey . '/' . $title . '?useskin=MediaWikiBootstrap2',
                'publishtime' => $publishtime
            );
            $url = 'http://wikiservice.joyme.' . $wgEnv . '/api/wiki/content/wikipost';
            $res = $curl->Post($url, $data);
            $res = json_decode($res, true);
            Log::info($res, __METHOD__);
            if ($res['rs'] != '1') {
                $res['data'] = $data;
                Log::error($res, __METHOD__);
            }
        }
    }

    //获取wiki页面第一张图片
    public static function getWikiFirstPic($title)
    {
        global $wgEnv, $wgWikiname, $wgMemc;
        $key = wfForeignMemcKey('Jshareicon', false, $wgWikiname, $title);
        $pic = $wgMemc->get($key);
        if ($pic) {
            return $pic;
        } else {
            $fpic = 'http://static.joyme.com/mobile/wikiapp/share_icon.png';
            $curl = new Curl();
            $curl->SetGzip(true);
            $url = 'http://wiki.joyme.' . $wgEnv . '/' . $wgWikiname . '/' . urlencode($title);
            $content = $curl->Get($url);
            if ($content) {
                $pattern = "/<div id=\"mw-content-text\" lang=\"zh-CN\" dir=\"ltr\" class=\"mw-content-ltr\">(.*?)<input type=\"hidden\" name=\"pageId\" id=\"pageId\" value=\".*?\"><\/div>/is";
                preg_match($pattern, $content, $divm);
                if ($divm) {
                    $mwcontent = $divm[1];
                    preg_match_all('/<img.*?src="(.*?)".*?/is', $mwcontent, $match);
                    if (isset($match[1]) && $match[1]) {
                        $imgurls = $match[1];
                        foreach ($imgurls as $k => $imgurl) {
                            if ($imgurl) {
                                $fpic = $imgurl;
                                break;
                            }
                        }
                        //604800 = 7 * 24 * 60 * 60
                        $wgMemc->set($key, $fpic, 604800);
//                        $wgMemc->set($key, $fpic, 60);
                    }
                }
            }
            return $fpic;
        }
    }

    //根据wikikey获取游戏名称
    public static function getGameNameByKey()
    {
        global $wgEnv, $wgWikiname, $wgMemc;
        $key = wfForeignMemcKey('Jwikigamename', false, $wgWikiname . 'wiki');
        $gamename = $wgMemc->get($key);
        if ($gamename) {
            return $gamename;
        } else {
            $curl = new Curl();
            $querygameurl = 'http://hezuo.joyme.' . $wgEnv . '/wiki/index.php?c=wiki&a=getgamenamebykey';
            $res = $curl->Get($querygameurl, array(
                'wikikey' => $wgWikiname
            ));
            $res = json_decode($res, true);
            if ($res['rs'] == '1') {
                //604800 = 7 * 24 * 60 * 60
                $wgMemc->set($key, $res['result'], 604800);
                return $res['result'];
            } else {
                Log::error($res, __METHOD__);
                return '';
            }
        }
    }

    //获取分享短连接地址
    public static function getShareShortUrl($shareurl, $title)
    {
        global $wgWikiname, $wgMemc;
        $key = wfForeignMemcKey('Jwikishorturl', false, $wgWikiname . 'wiki', $title);
        $shorturl = $wgMemc->get($key);
        if ($shorturl) {
            return $shorturl;
        } else {
            $curl = new Curl();
            $sinatcnurl = 'http://api.t.sina.com.cn/short_url/shorten.json';
            $res = $curl->Get($sinatcnurl, array(
                'source' => 1245341962,
                'url_long' => $shareurl
            ));
            $res = json_decode($res, true);
            $res = $res[0];
            if (isset($res['url_short']) && $res['url_short']) {
                $wgMemc->set($key, $res['url_short']);
                return $res['url_short'];
            } else {
                Log::error($res, __METHOD__);
                return $shareurl;
            }
        }
    }

    //获取文章创建时间
    public static function getPageCreateTime($articleid)
    {
        $dbr = wfGetDB( DB_SLAVE );
        $results = $dbr->selectRow(
            'page',
            array("page_touched"),
            array(
                'page_id' => $articleid
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
        return $publishtime;
    }

} 