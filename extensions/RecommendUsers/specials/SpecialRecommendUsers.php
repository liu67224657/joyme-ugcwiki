<?php
/**
 * @ingroup SpecialPage
 */
class SpecialRecommendUsers extends SpecialPage{

    public function __construct(){

        parent::__construct('RecommendUsers', 'recommendusers');
    }

    public function execute($par) {

        global $wgUser,$wgIsLogin,$wgWikiname;

        if(isMobile()){
            $this->getOutput()->addHTML(
                '<span class="view-status">' .
                $this->msg( 'systemmessage_facility_error' )->plain() .
                '</span><br /><br />'
            );
            $this->getOutput()->addReturnTo( SpecialPage::getTitleFor( 'Specialpages' ) );
        }else{
            $this->setHeaders();
            $output = $this->getOutput();

            if($wgWikiname !='home'){
                $output->redirectHome('Special:RecommendUsers');
                return false;
            }

            $req = $this->getRequest();

            if( $wgIsLogin ){
                if (!$wgUser->isAllowed( 'recommendusers' ) ) {
                    throw new PermissionsError( 'recommendusers' );
                }
                if ( wfReadOnly() ) {
                    throw new ReadOnlyError;
                }
                if ( $wgUser->isBlocked() ) {
                    throw new UserBlockedError( $this->getUser()->mBlock );
                }
                $recommend_user = trim($req->getVal( 'recommend_user'));
                $delete_id = intval($req->getVal( 'delete_id'));
                $confirm = intval($req->getVal( 'confirm'));
                // Add CSS
                $output->addModuleStyles(
                    array(
                        'ext.RecommendUser.css',
                        'ext.socialprofile.userprofile.usercentercommon.css'
                    )
                );
                $RecommendUsers = new RecommendUsers();

                if($recommend_user && $req->wasPosted()){
                    //If the user does not exist
                    $user_exits = $this->checkUserName($recommend_user);
                    if(!$user_exits){
                        $output->addHTML(
                            '<span class="view-status">' .
                            $this->msg( 'recommendusers_exists_no' )->plain() .
                            '</span><br /><br />'
                        );
                        $this->getOutput()->addReturnTo( SpecialPage::getTitleFor( 'recommendusers' ) );
                    }else{
                        if(!$RecommendUsers->select_User_Id($user_exits)){
                            if($RecommendUsers->add_Recommend_Users($user_exits,$wgUser->mId)){
                                $output->addHTML(
                                    '<span class="view-status">' .
                                    $this->msg( 'recommendusers_success' )->plain() .
                                    '</span><br /><br />'
                                );
                            }else{
                                $output->addHTML(
                                    '<span class="view-status">' .
                                    $this->msg( 'recommendusers_error' )->plain() .
                                    '</span><br /><br />'
                                );
                            }
                        }else{
                            $output->addHTML(
                                '<span class="view-status">' .
                                $this->msg( 'recommendusers_user_existing' )->plain() .
                                '</span><br /><br />'
                            );
                        }
                        $this->getOutput()->addReturnTo( SpecialPage::getTitleFor( 'recommendusers' ) );
                    }
                }elseif(!$recommend_user && $req->wasPosted()){
                    $output->addHTML(
                        '<span class="view-status">' .
                        $this->msg( 'recommendusers_empty' )->plain() .
                        '</span><br /><br />'
                    );
                    $this->getOutput()->addReturnTo( SpecialPage::getTitleFor( 'recommendusers' ) );
                }elseif($delete_id && !$req->wasPosted() && !$confirm){
                    $output->addHTML(
                        '<span class="view-status">' .
                        $this->msg( 'recommendusers_confirm_delete' )->plain() .
                        '</span><br /><br />'
                    );
                    $output->addHTML('<a href="'.$this->getPageTitle()->getLocalUrl( ).'">'.$this->msg( 'recommendusers_return' ).'</a> <a href="'.$this->getPageTitle()->getLocalUrl( array('delete_id'=>$delete_id,'confirm'=>1)).'">'.$this->msg( 'recommendusers_delete_true' ).'</a>');
                }elseif($delete_id && $confirm){
                    $RecommendUsers->delete_Recommend_Users($delete_id);
                    $output->addHTML(
                        '<span class="view-status">' .
                        $this->msg( 'recommendusers_delete_success' )->plain() .
                        '</span><br /><br />'
                    );
                    $this->getOutput()->addReturnTo( SpecialPage::getTitleFor( 'recommendusers' ) );
                }else{
                    $this->displayPage($output,$recommend_user);
                    $this->displayList($output);
                }
            }else{
                $this->getOutput()->addModuleStyles( 'ext.RecommendUsers.not.logged' );
                $this->getOutput()->addHTML($this->msg( 'create-not-logged' )->text());
            }
        }
    }

    /**
     * Display the page
     */
    public function displayPage($output,$user=false){

        $output->addHTML(
            Xml::openElement( 'form', array( 'method' => 'post', 'action' => $this->getPageTitle()->getLocalUrl(), 'id' => 'recommendusers' ) ) .
            Xml::openElement( 'fieldset' ) .
            Xml::openElement( 'table', array( 'id' => 'mw-recommendusers-table' ) ) .
            "<tr>
                <td class='mw-input'>" .
            Xml::input( 'recommend_user', false, $user, array( 'type' => 'text') ) . ' ' .
            Xml::submitButton(
                $this->msg( 'recommendusers_submit' )->text(),
                array(
                    'name' => 'submit',
                    'tabindex' => '4',
                    'id' => 'submit'
                )
            ) .
            "</td>
                </tr>".
            Xml::closeElement( 'table' ) .
            Xml::closeElement( 'fieldset' ) .
            Html::hidden( 'recommendusers',1) .
            Xml::closeElement( 'form' ) . "\n"
        );
    }


    /**
     * Friends list
     */
    public function displayList($output){

        $data = $this->userInfo();
        if($data){
            $output->addHTML(
                '<ul class="rec_user_list">'
            );
            foreach($data as $k=>$v){
                $output->addHTML(
                    '<li>' . $v->user_name . '<a href="'.$this->getPageTitle()->getLocalUrl( 'delete_id='.$v->user_id).'">        '.$this->msg( 'recommendusers_delete' ).'</a>' .'</li>'
                );
            }
            $output->addHTML(
                '</ul>'
            );
        }
    }


    /**
     * Check  the user name or phone number exists
     * @param $user_name Mixed: user name or Mobile number
     * @return Integer:boole
     */
    public function checkUserName($user_name){

        if(!$user_name){
            return false;
        }
        $model = new RecommendUsers();
        $data = $model->find_User_Id($user_name);
        if($data){
            return $data->user_id;
        }
        return false;
    }

    /**
     * Query the user information
     * @return Integer:array
     */
    public function userInfo(){

        $model = new RecommendUsers();
        $res = $model->select_All_User();

        $userinfo = array();
        if($res->result->num_rows){
            foreach ( $res as $user ) {
                $userArray[] = $user->rec_user_id;
            }
            if(count($userArray)){
                $users = implode(',',$userArray);
                $result = $model->find_User_Name($users);
                foreach($result as $ks=>$vs){
                    $userinfo[] = $vs;
                }
                return $userinfo;
            }
        }
        return false;
    }

    protected function getGroupName() {

        return 'pages';
    }
}