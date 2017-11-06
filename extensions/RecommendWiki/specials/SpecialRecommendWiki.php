<?php
/**
 * @ingroup SpecialPage
 */
class SpecialRecommendWiki extends SpecialPage{

    public function __construct(){

        parent::__construct('RecommendWiki', 'recommendwiki');
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
                $output->redirectHome('Special:RecommendWiki');
                return false;
            }

            $req = $this->getRequest();

            if($wgIsLogin){
                if (!$wgUser->isAllowed( 'recommendwiki' ) ) {
                    throw new PermissionsError( 'recommendwiki' );
                }
                if ( wfReadOnly() ) {
                    throw new ReadOnlyError;
                }
                if ( $wgUser->isBlocked() ) {
                    throw new UserBlockedError( $this->getUser()->mBlock );
                }
                // Add JS
                $output->addModuleScripts( 'ext.RecommendWiki.js' );
                // Add CSS
                $output->addModuleStyles( array(
                    'ext.RecommendUser.css',
                    'ext.socialprofile.userprofile.usercentercommon.css'
                ));

                $wiki_key = trim($req->getVal( 'wiki_key'));
                $icon = trim($req->getVal( 'icon'));
                $delete_id = intval($req->getVal( 'delete_id'));
                $confirm = intval($req->getVal( 'confirm'));
//var_dump($wiki_key,$icon);exit;
                $RecommendWiki = new RecommendWiki();

                if($req->wasPosted() && $wiki_key && $icon){

                    if(RecommendWiki::getWikiCount()>=15){
                        $output->addHTML(
                            '<span class="view-status">' .
                            $this->msg( 'recommendwiki_num_max' )->plain() .
                            '</span><br /><br />'
                        );
                    }else{
                        if($RecommendWiki->add_Recommend_Wiki($wiki_key,$icon,$wgUser->mId)){
                            $output->addHTML(
                                '<span class="view-status">' .
                                $this->msg( 'recommendwiki_add_success' )->plain() .
                                '</span><br /><br />'
                            );
                        }else{
                            $output->addHTML(
                                '<span class="view-status">' .
                                $this->msg( 'recommendwiki_add_error' )->plain() .
                                '</span><br /><br />'
                            );
                        }
                    }
                    $this->getOutput()->addReturnTo( SpecialPage::getTitleFor( 'recommendwiki' ) );
                }elseif(($req->wasPosted() && !$wiki_key) || ($req->wasPosted() && !$icon)){
                    $output->addHTML(
                        '<span class="view-status">' .
                        $this->msg( 'recommendwiki_exists_no' )->plain() .
                        '</span><br /><br />'
                    );
                    $this->getOutput()->addReturnTo( SpecialPage::getTitleFor( 'recommendwiki' ) );
                }elseif($delete_id && !$confirm){
                    $output->addHTML(
                        '<span class="view-status">' .
                        $this->msg( 'recommendusers_confirm_delete' )->plain() .
                        '</span><br /><br />'
                    );
                    $output->addHTML('<a href="'.$this->getPageTitle()->getLocalUrl( ).'">'.$this->msg( 'recommendusers_return' ).'</a> <a href="'.$this->getPageTitle()->getLocalUrl( array('delete_id'=>$delete_id,'confirm'=>1)).'">'.$this->msg( 'recommendwiki_delete_true' ).'</a>');
                }elseif($delete_id && $confirm){
                    $RecommendWiki->delete_Recommend_Wiki($delete_id,$wgUser->mId);
                    $output->addHTML(
                        '<span class="view-status">' .
                        $this->msg( 'recommendwiki_delete_success' )->plain() .
                        '</span><br /><br />'
                    );
                    $this->getOutput()->addReturnTo( SpecialPage::getTitleFor( 'recommendwiki' ) );
                }else{
                    $this->displayPage($output);
                    $this->displayList($output,$RecommendWiki);
                }
            }else{
                $this->getOutput()->addModuleStyles( 'ext.RecommendWiki.not.logged' );
                $this->getOutput()->addHTML($this->msg( 'create-not-logged' )->text());
            }
        }
    }

    public function displayPage($output){

        global $wgServer,$wgScriptPath;

        $output->addHTML(
            '<div class="float-win">
                    <div>
                        <form method="post" action="'.$wgServer.$wgScriptPath.'/joyme_api.php?action=commentUpload" enctype="multipart/form-data" target="xframe" id="imgForm">
                            <input type="file" name="commentImg" id="commentImg" accept="image/*"/>
                            <input type="hidden" name="edittoken" id="edittoken" />
                        </form>
                        <iframe name="xframe" id="xframe" src="" style="display: none;"></iframe>​
                        <span id="errMsg" style="color: red;"></span>
                    </div>
            </div>
            <form method="post" action="'.$this->getPageTitle()->getLocalUrl().'" id="recommendwiki">
                <div>
                    <input type="text" name="wiki_key">
                    <input type="hidden" id="imageval" name="icon">
                    <input type="submit" value="'.$this->msg( 'recommendwiki_add' )->text().'">
                </div>
            </form>'
        );
    }

    public function displayList($output,$RecommendWiki){

        $data = $RecommendWiki->select_All_wiki();

        if($data->result->num_rows){
          $text = Xml::openElement( 'table', array( 'class' => 'wikitable mw-statistics-table' ) );
          foreach($data as $k=>$v){
              $text.=Xml::openElement( 'tr' ) .
                     Xml::tags( 'td', array( 'colspan' => '2' ) ,
                         $v->wiki_key ) .
                     Xml::tags( 'td', array( 'colspan' => '2' ) ,
                         '<img src="'.DataSynchronization::imagePath($v->icon,70,70,true).'">') .
                     Xml::tags( 'td', array( 'colspan' => '2' ) ,
                         '<a href='.$this->getPageTitle()->getLocalUrl( 'delete_id='.$v->rec_id).'>'.$this->msg( 'recommendwiki_add_delete' )->parse()).'</a>' .
                     Xml::closeElement( 'tr' );
          }
          $text .= Xml::closeElement( 'table' );
          $output->addHTML( $text );
        }
    }

    protected function getGroupName() {

        return 'pages';
    }
}