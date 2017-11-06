<?php

/**
 * Description:更新新增钩子
 * Author: gradydong
 * Date: 2017/05/12
 * Time: 11:17
 * Copyright: Joyme.com
 */
class JoymeRecentChangesHooks
{

    /**
     * Registers the <joymerecentchanges> tag with the Parser.
     *
     * @param Parser $parser
     * @return bool
     */
    public static function onParserFirstCallInit(Parser &$parser)
    {
        $parser->setHook('joymerecentchanges', array('JoymeRecentChangesHooks', 'JoymeRecentChangesList'));
        return true;
    }


    /**
     * Callback function for onParserFirstCallInit().
     *
     * @param        $input
     * @param array  $args
     * @param Parser $parser
     * @return string HTML
     */
    public static function JoymeRecentChangesList($input, $argv)
    {
        global $wgWikiname, $wgOut,$wgEnv;
        wfProfileIn(__METHOD__);
//        $wgOut->addModuleStyles('ext.joymerecentchanges.css');
//        $wgOut->addModules('ext.joymerecentchanges.js');
        $wgOut->addModules('ext.joymerecentchanges');

        $limit = !empty($argv['limit']) ? intval($argv['limit']) : 8;
        $type = '';
        if (key_exists('style', $argv)) {
            $type = $argv['style'];
        }
        $data = self::getRecentChanges();
        $recent_html = '';
        if ($data->numRows()) {
            if (key_exists('stopwords', $argv)) {
                $stopwordsarr = explode('|', $argv['stopwords']);
            }
            $i = 0;
            foreach ($data as $k => $v) {
                if (isset($stopwordsarr)) {
                    $isstop = false;
                    foreach ($stopwordsarr as $val) {
                        if (!empty($val)) {
                            if (preg_match("|$val|is", $v->rc_title)) {
                                $isstop = true;
                                break;
                            }
                        }
                    }
                    if ($isstop) {
                        continue;
                    }
                }
                $i++;
                if ($i > $limit) {
                    break;
                }
                if ($v->rc_new == 1) {
                    $rec_new = '<div class="li-line1 clearfix"><span class="update newadd">新增</span>';
                } else {
                    $rec_new = '<div class="li-line1 clearfix"><span class="update">更新</span>';
                }
                $recent_html .= '<li class="fn-clear">' . $rec_new . '<a target="_blank" href="/' . $wgWikiname . '/' . $v->rc_title . '">' . $v->rc_title . '</a></div><div class="li-line2">贡献者：<a target="_blank" href="http://wiki.joyme.'.$wgEnv.'/home/用户:'.$v->rc_user_text.'">' . $v->rc_user_text . '</a><em>' . $v->time . '</em></div></li>';
            }
        }

        $output = ' <div class="wiki-update" style="' . $type . '">
                <ul class="update-content">' . $recent_html . '</ul>
            </div><div class="fn-clear"></div>';

        wfProfileOut(__METHOD__);
        return $output;
    }


    public static function getRecentChanges()
    {
        $dbr = wfGetDB(DB_MASTER);
        $res = $dbr->query("SELECT DATE_FORMAT(rc_timestamp,'%m-%d') as time,rc_title,rc_new,rc_user,rc_user_text FROM recentchanges WHERE rc_log_action !='delete' AND rc_id  IN (SELECT MAX(rc_id) FROM recentchanges GROUP BY rc_cur_id) AND rc_namespace = 0 ORDER BY rc_id desc LIMIT 60");
        $dbr->commit(__METHOD__);
        return $res;
    }

}