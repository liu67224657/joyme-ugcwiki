<?php

use Joyme\core\Request;
use Joyme\page\Page;
class SpecialSystemMessage extends SpecialPage{

    public function __construct(){

        parent::__construct('SystemMessage', 'systemmessage');
    }

    public function execute($par) {

        global $wgUser,$wgIsLogin,$wgWikiname;
        $output = $this->getOutput();
        $req = $this->getRequest();

        if(isMobile()){
            $output->addHTML(
                '<span class="view-status">' .
                $this->msg( 'systemmessage_facility_error' )->plain() .
                '</span><br /><br />'
            );
            $this->getOutput()->addReturnTo( SpecialPage::getTitleFor( 'Specialpages' ) );
        }else{
            $this->setHeaders();

            if($wgWikiname !='home'){
                $output->redirectHome('Special:SystemMessage');
                return false;
            }

            if($wgIsLogin){
                if (!$wgUser->isAllowed( 'systemmessage' ) ) {
                    throw new PermissionsError( 'systemmessage' );
                }
                if ( wfReadOnly() ) {
                    throw new ReadOnlyError;
                }
                if ( $wgUser->isBlocked() ) {
                    throw new UserBlockedError( $this->getUser()->mBlock );
                }
                // Add CSS
                
                $output->addModuleStyles( array(
                		'ext.socialprofile.userprofile.usercentercommon.css',
                		'ext.AboutMe.css'
                )
                );

                $submit_disable = false;
                $system_message_data = '';
                $system_message_key = null;

                $pb_page = intval($req->getVal('pb_page'));

                if($req->wasPosted()){

                    $system_message_data = trim($req->getVal( 'system-message-data'));
                    $system_message_key = trim($req->getVal( 'system-message-key'));
                    $exists = array();
                    if(!empty($system_message_data)){
                        if( $system_message_key ){
                            $keys = explode(',',$system_message_key);
                            foreach($keys as $kk=>$kv){
                                if(!SystemMessageClass::getWikiSiteId($kv)){
                                    $exists[] = $kv;
                                }
                            }
                        }

                        if($exists){
                            $output->addHTML(
                                $this->msg( 'not-exists-list' )->plain().'<span class="view-status" style="color: red">' .
                                implode(',',$exists).
                                '</span><br /><br />'
                            );
                        }else{
                            if( SystemMessageClass::add_System_Message(
                                $system_message_key,
                                $system_message_data,
                                $wgUser->mId,
                                $wgUser->getName()
                            ) ){
                                $output->addHTML(
                                    '<span class="view-status">' .
                                    $this->msg( 'system-message_success' )->plain() .
                                    '</span><br /><br />'
                                );
                                $system_message_key = '';
                                $system_message_data = '';
//                                $submit_disable = 'disabled';
                            }else{
                                $output->addHTML(
                                    '<span class="view-status">' .
                                    $this->msg( 'system-message_fild' )->plain() .
                                    '</span><br /><br />'
                                );
                            }
                        }
                    }else{
                        $output->addHTML(
                            '<span class="view-status">' .
                            $this->msg( 'system-message_empty' )->plain() .
                            '</span><br /><br />'
                        );
                    }
                }
                $this->setHeaders();
                $output->addHTML(
                    Xml::openElement( 'form', array( 'method' => 'post', 'action' => $this->getPageTitle()->getLocalUrl(), 'id' => 'systemmessage' ) ) .
                    Xml::openElement( 'fieldset' ) .
                    Xml::openElement( 'table', array( 'id' => 'mw-system-message-table' ) ) ."<tr><td class='mw-input'>".
                    $this->msg( 'system-message-input-wikikey' )->text()."</td><tr><td>".
                    Xml::input( 'system-message-key', 30, $system_message_key, array( 'type' => 'text') ) .$this->msg( 'system-message-input-wikikey—tip' )->text()."</td></tr><tr><td>".
                    $this->msg( 'system-message-input-message' )->text()."</td><tr><td>".
                    Xml::textarea( 'system-message-data' , $system_message_data , false , false ,array( 'name'=>'system-message-data') )."</td></tr><tr><td>".
                    Xml::submitButton(
                        $this->msg( 'system-message_submit' )->text(),
                        array(
                            'name' => 'submit',
                            'tabindex' => '4',
                            'id' => 'submit',
                            'disabled' => $submit_disable
                        )
                    ) .
                    Xml::closeElement( 'table' ) .
                    Xml::closeElement( 'fieldset' ) .
                    Xml::closeElement( 'form' ) . "\n"
                );
                $this->SystemMessageList($output);
            }else{
                $this->getOutput()->addModuleStyles( 'ext.aboutMe.not.logged' );
                $this->getOutput()->addHTML($this->msg( 'create-not-logged' )->text());
            }
        }
    }

    public function SystemMessageList($output){

        $data = $this->SystemMessageData();
        $text = Xml::openElement( 'table', array( 'class' => 'wikitable mw-system-message-table' ) );

        $text.=$this->msg( 'system-message-history' )->parse();
        $text.=Xml::openElement( 'tr' ) .
            Xml::tags( 'td', array( 'colspan' => '2' ) ,
                $this->msg( 'system-message-history-td1' )->parse() ) .
            Xml::tags( 'td', array( 'colspan' => '2' ) ,
                $this->msg( 'system-message-history-td2' )->parse()) .
            Xml::tags( 'td', array( 'colspan' => '2' ) ,
                $this->msg( 'system-message-history-td3' )->parse()).
            Xml::closeElement( 'tr' );
        if($data){
            foreach($data['data'] as $k=>$v){
                $text.=Xml::openElement( 'tr' ) .
                    Xml::tags( 'td', array( 'colspan' => '2' ) ,
                        $v->um_date ) .
                    Xml::tags( 'td', array( 'colspan' => '2' ) ,
                        $v->wiki_keys) .
                    Xml::tags( 'td', array( 'colspan' => '2' ) ,
                        $v->um_message).
                    Xml::closeElement( 'tr' );
            }
            $text.=Xml::openElement( 'tr' ) .
                Xml::tags( 'td', array( 'colspan' => '6' ) ,
                    $data['page']).
                Xml::closeElement( 'tr' );
            $text .= Xml::closeElement( 'table' );
        }

        $output->addHTML( $text );
    }

    public function SystemMessageData(){

        $result = array();
        $perpage = 20;
        $pb_page = Request::get('pb_page',1);
        $skip = ($pb_page-1)*$perpage;
        $url = $this->getPageTitle()->getLocalUrl('realize=1');
        $dbw = wfGetDB( DB_MASTER );
        $res = $dbw->select(
            'user_system_messages',
            '*',
            array( 'um_type'=>1000),
            __METHOD__,
            array(
                'ORDER BY' =>'um_id DESC',
                'LIMIT'=>$perpage,
                'OFFSET'=>$skip
            )
        );
        $totle = $dbw->selectRowCount(
            'user_system_messages',
            '*',
            array( 'um_type'=>1000)
        );
        $data = array();
        if ( $totle ){
            foreach($res as $v){
                $data[] = $v;
            }
            $_page = new Page(array('total' => $totle,'perpage'=>$perpage,'nowindex'=>$pb_page,'pagebarnum'=>10,'url'=>$url));
            $page_str = $_page->show(2);
            $result = array(
                'data'=>$data,
                'page'=>$page_str
            );
        }
        return $result;
    }

    protected function getGroupName() {
        return 'wiki';
    }
}