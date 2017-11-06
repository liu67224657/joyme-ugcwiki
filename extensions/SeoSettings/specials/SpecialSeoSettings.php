<?php
/**
 * Created by PhpStorm.
 * User: xinshi
 * Date: 2015/11/20
 * Time: 9:20
 */
class SpecialSeoSettings extends SpecialPage{

    static $seo_title = null;
    static $seo_keywords = null;
    static $seo_id = null;
    static $seo_descripttion = null;
    static $user_permissions;
    static $discussion_area;
    static $mobile_status;
    static $icon;
    static $site_name;
    static $table_name = 'site_info';

    public function __construct(){

        parent::__construct('SeoSettings', 'seosettings');
    }

    protected function getGroupName() {

        return 'users';
    }

    public function execute($par) {

        global $wgUser;
        $request = $this->getRequest();
        $output = $this->getOutput();
        $this->setHeaders();
        if (!$wgUser->isAllowed( 'seosettings' ) ) {
            throw new PermissionsError( 'seosettings' );
        }
        if( $wgUser->isBlocked() ){
            throw new UserBlockedError( $this->getUser()->mBlock );
        }
        $seoInfo = self::seoInfo();
        if (empty($seoInfo['sid'])){
            throw new MWException( "SEO data is empty" );
        }
        # Get request data from, e.g.
        $newwikititle = trim($request->getText( 'newwikititle', $par ));
        $newwikikeywords = trim($request->getText( 'newwikikeywords', $par ));
        $newdescripttion = trim($request->getText( 'newdescripttion', $par ));
        $newsite_name = trim($request->getText( 'newsite_name', $par ));
        $newicon = trim($request->getText( 'newicon', $par ));
        $seiid = intval(trim($request->getText( 'seowikiseoid', $par )));
        $usereditid = trim($request->getText( 'usereditstatusid', $par ));
        $permissions = intval(trim($request->getText( 'newuser_permissions', $par )));
        $newdiscussion_area = intval(trim($request->getText( 'newdiscussion_area', $par )));
        $discussionareaid = intval(trim($request->getText( 'discussionareaid', $par )));
        $mobile_status = intval(trim($request->getText( 'mobile_status', $par )));
        $mobile_status_id = intval(trim($request->getText( 'mobile_status_id', $par )));
        # Do stuff
        # ...
        $flag = true;
        if(!empty($usereditid) && in_array($permissions,array(0,1))){
            self::$user_permissions = $permissions;
            self::$seo_id = $usereditid;
            $this->checkResult(self::saveUserInfo());
        }elseif(!empty($newwikikeywords) && !empty($newdescripttion) &&!empty($newwikititle) && !empty($seiid)){
            self::$seo_title = $newwikititle;
            self::$seo_descripttion = $newdescripttion;
            self::$seo_keywords = $newwikikeywords;
            self::$site_name = $newsite_name;
            self::$icon = $newicon;
            self::$seo_id = $seiid;
            if(strlen($newwikititle)>150){
                $output->addWikiMsg( 'seo_tip_title_lenth' );
                $flag = false;
            }
            if(strlen($newwikikeywords)>600){
                $output->addWikiMsg( 'seo_tip_keywords_lenth' );
                $flag = false;
            }
            if(strlen($newdescripttion)>600){
                $output->addWikiMsg( 'seo_tip_descripttion_lenth' );
                $flag = false;
            }
            if($flag){
                $this->checkResult(self::saveSeoInfo());
            }else{
                $this->getOutput()->addReturnTo( SpecialPage::getTitleFor( 'seosettings' ) );
            }
        }elseif(!empty($discussionareaid) && in_array($newdiscussion_area,array(0,1))){
            self::$seo_id = $discussionareaid;
            self::$discussion_area = $newdiscussion_area;
            $this->checkResult(self::savediscussion());
        }elseif(!empty($mobile_status_id) && in_array($mobile_status,array(0,1))){
            self::$mobile_status = $mobile_status;
            self::$seo_id = $mobile_status_id;
            $this->checkResult(self::savemobilestatus());
        }else{
            $output->addWikiText('<b>SEO管理</b>');
            $output->addHTML(
                Xml::openElement( 'form', array( 'method' => 'post', 'action' => $this->getPageTitle()->getLocalUrl(), 'id' => 'renameuser' ) ) .
                Xml::openElement( 'fieldset' ) .
                Xml::openElement( 'table', array( 'id' => 'mw-renameuser-table' ) ) .
                "<tr>
                    <td class='mw-label'>" .
                Xml::label( $this->msg( 'seo_wiki_title' )->text(), 'seonewwikititle' ) .
                "</td>
                    <td class='mw-input'>" .
                Xml::input( 'newsite_name', 140, $seoInfo['site_name'], array( 'type' => 'text', 'tabindex' => '1' ) ) . ' ' .
                "</td>
                </tr>".
                "<tr>
                    <td class='mw-label'>" .
                Xml::label( $this->msg( 'seo_wiki_icon' )->text(), 'seonewwikititle' ) .
                "</td>
                    <td class='mw-input'>" .
                Xml::input( 'newicon', 140, $seoInfo['icon'], array( 'type' => 'text', 'tabindex' => '1' ) ) . ' ' .
                "</td>
                </tr>".
                "<tr>
                    <td class='mw-label'>" .
                Xml::label( $this->msg( 'seo_wiki_seo_title' )->text(), 'seonewwikititle' ) .
                "</td>
                    <td class='mw-input'>" .
                Xml::input( 'newwikititle', 140, $seoInfo['site_title'], array( 'type' => 'text', 'tabindex' => '1' ) ) . ' ' .
                "</td>
                </tr>
                <tr>
                    <td class='mw-label'>" .
                Xml::label( $this->msg( 'seo_wiki_keywords' )->text(), 'seonewwikikeywords' ) .
                "</td>
                    <td class='mw-input'>" .
                Xml::input('newwikikeywords', 140, $seoInfo['site_seokeywords'], array( 'type' => 'text', 'tabindex' => '2' ) ) .
                "</td>
                </tr>
                <tr>
                    <td class='mw-label'>" .
                Xml::label( $this->msg( 'seo_wiki_descripttion' )->text(), 'seonewdescripttion' ) .
                "</td>
                    <td class='mw-input'>" .
                Xml::input('newdescripttion', 140, $seoInfo['site_seodescription'], array( 'type' => 'text', 'tabindex' => '3' ) ) .
                "</td>
                </tr>"
            );
            $output->addHTML( "
                <tr>
                    <td>&#160;
                    </td>
                    <td class='mw-submit'>" .
                Xml::submitButton(
                    $this->msg( 'seo_wiki_submit' )->text(),
                    array(
                        'name' => 'submit',
                        'tabindex' => '4',
                        'id' => 'submit'
                    )
                ) .
                ' ' .
                "</td>
                </tr>" .
                Xml::closeElement( 'table' ) .
                Xml::closeElement( 'fieldset' ) .
                Html::hidden( 'seowikiseoid',$seoInfo['sid']) .
                Xml::closeElement( 'form' ) . "\n"
            );
            $output->addElement('br');
            $output->addElement('br');
            $this->userEditstatusList($seoInfo,$output);
            $output->addElement('br');
            $output->addElement('br');
            $this->discussionAreaList($seoInfo,$output);
            $output->addElement('br');
            $output->addElement('br');
            $this->isMindexStatusForm($seoInfo,$output);
        }
    }

    //User permissions Settings form
    function userEditstatusList($seoInfo,$output){

        $output->addElement('hr');
        $output->addElement('br');
        $output->addWikiText('<b>权限管理</b>');

        if($seoInfo['useredit_status']==1){
            $output->addHTML(
                Xml::openElement( 'form', array( 'method' => 'post', 'action' => $this->getPageTitle()->getLocalUrl(), 'id' => 'renameuser' ) ) .
                Xml::openElement( 'fieldset' ) .
                Xml::openElement( 'table', array( 'id' => 'mw-renameuser-table' ) ) .
                "<tr>
                    <td class='mw-label'>" .
                Xml::label( $this->msg( 'user_permissions' )->text(), 'user_permissions' ) .
                "</td>
                    <td class='mw-input'>".
                Xml::label( $this->msg( 'user_permissions_yes' )->text(), 'user_permissions_yes' ).
                XML::radio('newuser_permissions',1,true).
                Xml::label( $this->msg( 'user_permissions_no' )->text(), 'user_permissions_no' ).
                XML::radio('newuser_permissions',0)
            );
        }else{
            $output->addHTML(
                Xml::openElement( 'form', array( 'method' => 'post', 'action' => $this->getPageTitle()->getLocalUrl(), 'id' => 'renameuser' ) ) .
                Xml::openElement( 'fieldset' ) .
                Xml::openElement( 'table', array( 'id' => 'mw-renameuser-table' ) ) .
                "<tr>
                    <td class='mw-label'>" .
                Xml::label( $this->msg( 'user_permissions' )->text(), 'user_permissions' ) .
                "</td>
                    <td class='mw-input'>".
                Xml::label( $this->msg( 'user_permissions_yes' )->text(), 'user_permissions_yes' ).
                XML::radio('newuser_permissions',1).
                Xml::label( $this->msg( 'user_permissions_no' )->text(), 'user_permissions_no' ).
                XML::radio('newuser_permissions',0,true)
            );
        }
        $output->addHTML( "
                <tr>
                    <td>&#160;
                    </td>
                    <td class='mw-submit'>" .
            Xml::submitButton(
                $this->msg( 'seo_wiki_submit' )->text(),
                array(
                    'name' => 'submit',
                    'tabindex' => '2',
                    'id' => 'submit'
                )
            ) .
            ' ' .
            "</td>
                </tr>" .
            Xml::closeElement( 'table' ) .
            Xml::closeElement( 'fieldset' ) .
            Html::hidden( 'usereditstatusid',$seoInfo['sid']) .
            Xml::closeElement( 'form' ) . "\n"
        );
    }

    //Discussion area set up the form
    function discussionAreaList($seoInfo,$output){

        $output->addElement('hr');
        $output->addElement('br');
        $output->addWikiText('<b>讨论区管理</b>');

        if($seoInfo['thread_status']==1){
            $output->addHTML(
                Xml::openElement( 'form', array( 'method' => 'post', 'action' => $this->getPageTitle()->getLocalUrl(), 'id' => 'renameuser' ) ) .
                Xml::openElement( 'fieldset' ) .
                Xml::openElement( 'table', array( 'id' => 'mw-renameuser-table' ) ) .
                "<tr>
                    <td class='mw-label'>" .
                Xml::label( $this->msg( 'discussion_area_setting' )->text(), 'discussion_area_setting' ) .
                "</td>
                    <td class='mw-input'>".
                Xml::label( $this->msg( 'discussion_area_yes' )->text(), 'discussion_area_yes' ).
                XML::radio('newdiscussion_area',1,true).
                Xml::label( $this->msg( 'discussion_area_no' )->text(), 'discussion_area_no' ).
                XML::radio('newdiscussion_area',0)
            );
        }else{
            $output->addHTML(
                Xml::openElement( 'form', array( 'method' => 'post', 'action' => $this->getPageTitle()->getLocalUrl(), 'id' => 'renameuser' ) ) .
                Xml::openElement( 'fieldset' ) .
                Xml::openElement( 'table', array( 'id' => 'mw-renameuser-table' ) ) .
                "<tr>
                    <td class='mw-label'>" .
                Xml::label( $this->msg( 'discussion_area_setting' )->text(), 'discussion_area_setting' ) .
                "</td>
                    <td class='mw-input'>".
                Xml::label( $this->msg( 'discussion_area_yes' )->text(), 'discussion_area_yes' ).
                XML::radio('newdiscussion_area',1).
                Xml::label( $this->msg( 'discussion_area_no' )->text(), 'discussion_area_no' ).
                XML::radio('newdiscussion_area',0,true)
            );
        }
        $output->addHTML( "
                <tr>
                    <td>&#160;
                    </td>
                    <td class='mw-submit'>" .
            Xml::submitButton(
                $this->msg( 'seo_wiki_submit' )->text(),
                array(
                    'name' => 'submit',
                    'tabindex' => '2',
                    'id' => 'submit'
                )
            ) .
            ' ' .
            "</td>
                </tr>" .
            Xml::closeElement( 'table' ) .
            Xml::closeElement( 'fieldset' ) .
            Html::hidden( 'discussionareaid',$seoInfo['sid']) .
            Xml::closeElement( 'form' ) . "\n"
        );
    }


    function isMindexStatusForm($seoInfo,$output){

        $output->addElement('hr');
        $output->addElement('br');
        $output->addWikiText('<b>是否需要手机版首页</b>');

        if($seoInfo['mindex_status']==1){
            $output->addHTML(
                Xml::openElement( 'form', array( 'method' => 'post', 'action' => $this->getPageTitle()->getLocalUrl(), 'id' => 'renameuser' ) ) .
                Xml::openElement( 'fieldset' ) .
                Xml::openElement( 'table', array( 'id' => 'mw-renameuser-table' ) ) .
                "<tr>
                    <td class='mw-label'>" .
                Xml::label( $this->msg( 'seting_mobile_status' )->text(), 'seting_mobile_status' ) .
                "</td>
                    <td class='mw-input'>".
                Xml::label( $this->msg( 'discussion_area_yes' )->text(), 'discussion_area_yes' ).
                XML::radio('mobile_status',1,true).
                Xml::label( $this->msg( 'discussion_area_no' )->text(), 'discussion_area_no' ).
                XML::radio('mobile_status',0)
            );
        }else{
            $output->addHTML(
                Xml::openElement( 'form', array( 'method' => 'post', 'action' => $this->getPageTitle()->getLocalUrl(), 'id' => 'renameuser' ) ) .
                Xml::openElement( 'fieldset' ) .
                Xml::openElement( 'table', array( 'id' => 'mw-renameuser-table' ) ) .
                "<tr>
                    <td class='mw-label'>" .
                Xml::label( $this->msg( 'seting_mobile_status' )->text(), 'seting_mobile_status' ) .
                "</td>
                    <td class='mw-input'>".
                Xml::label( $this->msg( 'discussion_area_yes' )->text(), 'discussion_area_yes' ).
                XML::radio('mobile_status',1).
                Xml::label( $this->msg( 'discussion_area_no' )->text(), 'discussion_area_no' ).
                XML::radio('mobile_status',0,true)
            );
        }
        $output->addHTML( "
                <tr>
                    <td>&#160;
                    </td>
                    <td class='mw-submit'>" .
            Xml::submitButton(
                $this->msg( 'seo_wiki_submit' )->text(),
                array(
                    'name' => 'submit',
                    'tabindex' => '2',
                    'id' => 'submit'
                )
            ) .
            ' ' .
            "</td>
                </tr>" .
            Xml::closeElement( 'table' ) .
            Xml::closeElement( 'fieldset' ) .
            Html::hidden( 'mobile_status_id',$seoInfo['sid']) .
            Xml::closeElement( 'form' ) . "\n"
        );
    }

    //All the data query
    static function seoInfo(){

        global $wgWikiname;

        $dbr = wfGetDB( DB_MASTER );
        $res = $dbr->selectRow(self::$table_name, '*','');
        $data = array();
        if( $res ){
            $data = $dbr->selectRow(
                'joyme_sites',
                '*',
                array(
                    'site_key'=>$wgWikiname
                )
            );
        }
        $array = self::objectToArray( $res );
        if($data){
            $array['icon'] = $data->site_icon;
        }
        return $array;
    }

    static function objectToArray($e){
        $e=(array)$e;
        foreach($e as $k=>$v){
            if( gettype($v)=='resource' ) return;
            if( gettype($v)=='object' || gettype($v)=='array' )
                $e[$k]=(array)objectToArray($v);
        }
        return $e;
    }



    //The results of judgment and output
    public function checkResult($result){

        if($result){

            SiteInfo::clearCache();
            $this->tipMessage(true);
        }else{
            $this->tipMessage(false);
        }
    }

    //site seo information
    static function saveSeoInfo(){

        global $wgWikiname;
        $dbr = wfGetDB( DB_MASTER );
        $where = array(
            'sid'=>self::$seo_id
        );
        $data = array(
            'site_title'=>self::$seo_title,
            'site_seokeywords'=>self::$seo_keywords,
            'site_seodescription'=>self::$seo_descripttion,
            'site_name'=>self::$site_name,
        );
        if($dbr->update(self::$table_name,$data,$where)){
            return $dbr->update(
                'joyme_sites',
                array(
                    'site_icon'=>self::$icon,
                    'site_name'=>self::$site_name
                ),
                array(
                    'site_key'=>$wgWikiname
                )
            );
        }else{
            return false;
        }
    }

    //Open or close the user to edit permissions
    static function saveUserInfo(){

        $dbr = wfGetDB( DB_MASTER );
        $where = array(
            'sid'=>self::$seo_id
        );
        $data = array(
            'useredit_status'=>self::$user_permissions
        );
        return $dbr->update(self::$table_name,$data,$where);
    }

    //Open or close the wiki forum function
    static function savediscussion(){

        $dbr = wfGetDB( DB_MASTER );
        $where = array(
            'sid'=>self::$seo_id
        );
        $data = array(
            'thread_status'=>self::$discussion_area
        );
        return $dbr->update(self::$table_name,$data,$where);
    }

    static function savemobilestatus(){

        $dbr = wfGetDB( DB_MASTER );
        $where = array(
            'sid'=>self::$seo_id
        );
        $data = array(
            'mindex_status'=>self::$mobile_status
        );
        return $dbr->update(self::$table_name,$data,$where);
    }
    //The role of prompting information operation results
    public function tipMessage($tip){

        $output = $this->getOutput();
        if($tip){
            $output->addWikiMsg( 'seo_tip_sucess' );
        }else{
            $output->addWikiMsg( 'seo_tip_failure' );
        }
        $this->getOutput()->addReturnTo( SpecialPage::getTitleFor( 'seosettings' ) );
    }
}