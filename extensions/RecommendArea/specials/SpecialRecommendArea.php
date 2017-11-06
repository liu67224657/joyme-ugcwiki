<?php

class SpecialRecommendArea extends SpecialPage{

    public $pageLink = array(
        '推荐区1(左侧导航区)' =>'推荐:推荐区1',
        '推荐区2(内容区)' =>'推荐:推荐区2'
    );

    public $url = 'http://wiki.joyme';

    public function __construct(){

        parent::__construct('RecommendArea', 'recommendarea');
    }

    public function execute($par) {

        global $wgUser,$wgIsLogin;

        $this->setHeaders();

        if( $wgIsLogin ){
            if (!$wgUser->isAllowed( 'recommendarea' ) ) {
                throw new PermissionsError( 'recommendarea' );
            }
            if ( wfReadOnly() ) {
                throw new ReadOnlyError;
            }
            if ( $wgUser->isBlocked() ) {
                throw new UserBlockedError( $this->getUser()->mBlock );
            }
            $this->buildListPage();
        }else{
            $this->getOutput()->addModuleStyles( 'ext.QuickMessage.not.logged' );
            $this->getOutput()->addHTML($this->msg( 'create-not-logged' )->text());
        }
    }

    //列表显示
    public function buildListPage(){

        $this->getOutput()->addHTML(
            $this->msg( 'recommendarea-edit-link')->parse().'</br>'
        );
        $text = Xml::openElement( 'table', array( 'class' => 'wikitable mw-system-message-table' ) );
        foreach($this->pageLink as $k=>$v){
            $text.=Xml::openElement( 'tr' );
            $text.=Xml::tags( 'td',null,$k ).
            Xml::tags( 'td',null,"<a target='_blank' href=".$this->getRecommendContent($v)." >".$this->getRecommendLink($v).'</a>');
            $text.=Xml::closeElement( 'tr' );
        }
        $text.= Xml::closeElement( 'table' );
        $this->getOutput()->addHTML( $text );
    }

    //推荐连接
    public function getRecommendLink( $params ){

        global $wgEnv,$wgWikiname;

        return $this->url.'.'.$wgEnv.'/'.$wgWikiname.'/'.$params;
    }

    //推荐区展示内容
    public function getRecommendContent( $params ){

        global $wgEnv,$wgWikiname;

        return $this->url.'.'.$wgEnv.'/'.$wgWikiname.'/index.php?title='.$params.'&action=edit';
    }


    //获取推荐区内容
    public static function getAreaContent( $title = '' ){

        global $wgParser,$wgTitle;
        $res = RecommendAreaClass::getRecommendQueryInfo( $title );
        if(isset($res->rev_text_id)){
            $text = RecommendAreaClass::getTextContentById( $res->rev_text_id );
            $content = $text->old_text?$text->old_text:null;
            if(isset($content)){
                $options = new ParserOptions;
                $result = $wgParser->parse($content, $wgTitle, $options);
                if($result){
                    return $result->mText;
                }
            }
        }
    }

    protected function getGroupName() {

        return 'wiki';
    }
}