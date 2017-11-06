<?php
use Joyme\core\Request;
use Joyme\page\Page;

class SpecialCreateWiki extends SpecialPage{

    public $name;
    public $key;
    public $reason;
    public $is_mobile;
    public $note;
    public $title;
    public $keywords;
    public $description;
    public $type = 1;
    public $user_editstatus = 1;
    public $skin_type = '';
    public $icon;
    public $dbName = null;

    public $table_num = 99;
    public $file_path = "/extensions/CreateWiki/public/wiki.sql";
    public $create_log_name = "/cache/wikis.txt";
    public $loading_file = "/extensions/CreateWiki/style/loading.gif";
    public $site_default_icon = "/extensions/CreateWiki/style/site_default.png";
    

    public $search_array = array(

    );

    public $optionsR = array(
        0=>'请选择',
        1=>'发行',
        2=>'商务',
        3=>'自运营',
        4=>'玩家申请',
        5=>'其他'
    );

    public $optionsT = array(
        '0'=>'请选择',
        '1'=>'原生wiki',
        '2'=>'数字链wikiwiki'
    );

    const perpage = 10;

    public $paras = array();
    public $ajaxReturn = array(
                'rs'=>1,
                'message'=>''
           );


    public function __construct(){
    	global $wgResourceBasePath;
    	$this->site_default_icon = $wgResourceBasePath.$this->site_default_icon;
        parent::__construct('CreateWiki', 'createwiki');
    }


    public function execute($par) {

        global $wgUser,$wgServer,$wgIsLogin,$wgWikiname;

        $out = $this->getOutput();

        if(isMobile()){
            $this->getOutput()->addHTML(
                '<span class="view-status">' .
                $this->msg( 'systemmessage_facility_error' )->plain() .
                '</span><br /><br />'
            );
            $out->addReturnTo( SpecialPage::getTitleFor( 'Specialpages' ) );
        }else{

            $this->setHeaders();
            $reg = $this->getRequest();

            if($wgWikiname !='home'){
                $out->redirectHome('Special:CreateWiki');
                return false;
            }

            $out->addModuleScripts( 'ext.CreateWiki.js' );
            // Add CSS
            $out->addModules(
                array(
                    'ext.CreateWiki',
                    'ext.socialprofile.userprofile.usercentercommon.css'
                )
            );

            if($wgIsLogin){

                if (!$wgUser->isAllowed( 'createwiki' ) ) {
                    throw new PermissionsError( 'createwiki' );
                }
                if ( wfReadOnly() ) {
                    throw new ReadOnlyError;
                }
                if ( $wgUser->isBlocked() ) {
                    throw new UserBlockedError( $this->getUser()->mBlock );
                }

                $pb_page = intval($reg->getVal('pb_page'));
                $search_wiki = intval($reg->getVal('search_wiki'));

                if( !$reg->getVal( 'create_history') && !$search_wiki){

                    if($reg->wasPosted()){
                        $data['wiki_name'] = trim($reg->getVal( 'wiki_name'));
                        $data['wiki_key'] = trim($reg->getVal( 'wiki_key'));
                        $data['create_reason'] = trim($reg->getVal( 'create_reason'));
                        $data['is_mobile'] = $reg->getInt( 'createwiki-need-phone');
                        $data['note'] = trim($reg->getVal( 'create_note'));
                        $data['title'] = trim($reg->getVal( 'wiki_title'));
                        $data['keywords'] = trim($reg->getVal( 'wiki_keywords'));
                        $data['description'] = trim($reg->getVal( 'wiki_description'));
                        $data['site_icon'] = trim($reg->getVal( 'wiki_icon'));
                        $data['site_skin'] = trim($reg->getVal( 'create_skins_type'));
                        $callback = $reg->getVal( 'callback','');

                        $this->int($data);

                        $this->checkValue( $callback );

                        $wikiModel = new CreateDatabase();

                        $this->dbName = $this->key.'wiki';

                        $res_database = false;
                        //创建数据库
//                        if($wgEnv == 'com'){
//                            //com环境用阿里云数据库，做区分
//                            if(count(CreateAliYunClass::alyfindDataBase( $this->dbName ))>=1){
//                                if($wikiModel->getTableNumByDbName($this->dbName)==0){
//                                    $res_database = true;
//                                }
//                            }else{
//                                $res_database = CreateAliYunClass::alyCreateDatabase( $this->dbName, $data['description']);
//                                sleep(5);
//                            }
//                        }else{
                            $res_database = $wikiModel->createDataBase($this->dbName);
                            sleep(3);
//                        }

                        if($res_database){
                            //创建表
                            $wikiModel->createTable($this->createSql(),$this->dbName);
                            //检验表创建结果
                            if($wikiModel->getTableNumByDbName($this->dbName)==$this->table_num){
                                //入站点信息表
                                $wikiModel->insertSeoTable(
                                    $this->type,
                                    $this->name,
                                    $this->title,
                                    $this->keywords,
                                    $this->description,
                                    $this->user_editstatus,
                                    $this->is_mobile,
                                    $this->skin_type
                                );
                                //入着迷站点表
                                $params = array(
                                    'site_name'=>$this->name,
                                    'site_key'=>$this->key,
                                    'site_type'=>$this->type,
                                    'create_reason'=>$this->reason,
                                    'create_remark'=>$this->note,
                                    'second_domain'=>0,
                                    'user_name'=>$wgUser->mName,
                                    'site_icon'=>$this->icon,
                                    'is_new'=>1,
                                    'create_time'=>time()
                                );

                                $id = CreateWikiClass::add_Joyme_Sites( $params );
                                if( $id&& $wikiModel->insertUserGroups()){
                                    //更新创建时间
                                    $wikiModel->updateRevision();

                                    JoymeWikiUser::addUserSiteRelation($wgUser->mId, $id, 1);
                                    /*JoymeWikiUser::addActionLog(
                                        $wgUser->mId,
                                        1,
                                        '创建了<a href="/'.$this->key.'/首页" target="_blank" >'.$this->name.'</a>'
                                    );*/
                                    JoymeWikiUser::adduseractivity(
                                        $wgUser->mId,
                                        'add_wiki',
                                        '创建了 <a href="'.$wgServer.'/'.$this->key.'/首页" target="_blank">'.$this->name.'</a>'
                                    );
                                    
                                    $params['site_id'] = $id;
                                    JoymeWikiUser::cmsaddsite( $params );
                                    //新创建的wiki，首页提交到wikiservice
                                    JoymeSite::wikiwebservicepost('首页',1,$this->key,$this->name,time()*1000);

                                    $ajaxReturn = array(
                                        'rs'=>0,
                                        'message'=>'success!'
                                    );
                                }else{
                                    $ajaxReturn = array(
                                        'rs'=>1,
                                        'message'=>'Add error site information!'
                                    );
                                }
                            }else{
                                $ajaxReturn = array(
                                    'rs'=>1,
                                    'message'=>'Table number is not enough!'
                                );
                            }
                        }else{
                            $ajaxReturn = array(
                                'rs'=>1,
                                'message'=>'Database creation error!'
                            );
                        }
                        self::returnJson( $ajaxReturn ,$callback);
                    }else{
                        $this->buildCreateHtml();
                    }
                }else{
                    if($reg->getVal( 'search-wiki-type')){
                        $this->search_array['site_type'] = $reg->getVal( 'search-wiki-type');
                    }else{
                        $this->search_array[] = 'site_type in (1,2)';
                    }
                    if($reg->getVal( 'search-wiki-reason')){
                        $this->search_array['create_reason'] = $reg->getVal( 'search-wiki-reason');
                    }
                    if($reg->getVal( 'search_time')){
                        $time = $reg->getVal( 'search_time');
                        $this->paras['create_time'] = $time;
                        $this->search_array[] = 'create_time >= '.strtotime($time) .' && create_time <= '.strtotime($time.'-'.date("t",strtotime($time)).' 23:59:59');
                    }
                    $this->buildCreateHistoryList();
                }
            }else{
                $out->addModuleStyles( 'ext.CreateWiki.not.logged' );
                $out->addHTML($this->msg( 'create-not-logged' )->text());
            }
        }
    }

    //初始化数据
    public function int( $data = array()){

        if( $data ){
            $this->name = $data['wiki_name'];
            $this->key = strtolower($data['wiki_key']);
            $this->reason = $data['create_reason'];
            $this->is_mobile = $data['is_mobile'];
            $this->note = $data['note'];
            $this->title = $data['title'];
            $this->keywords = $data['keywords'];
            $this->description = $data['description'];
            $this->icon = $data['site_icon'];
            $this->skin_type = $data['site_skin'];
        }
    }

    //检查值
    public function checkValue( $callback ){

        if(!$this->name || !$this->key || !$this->reason || !$this->title || !$this->keywords || !$this->description){
            $ajaxReturn = array(
                'rs'=>1,
                'message'=>'An empty parameter error!'
            );
            self::returnJson( $ajaxReturn ,$callback);
        }
        //检测Key是否存在
        if( CreateWikiClass::find_Joyme_Key_Exist( $this->key ) ){
            $ajaxReturn = array(
                'rs'=>1,
                'message'=>'A repeated wikikey!'
            );
            self::returnJson( $ajaxReturn ,$callback);
        }
    }


    //数据库创建脚本
    public function createSql(){

        global $IP;
        $path = $IP.$this->file_path;
        if(file_exists($path)){
            $lines=file($path);
            $sqlstr="";
            foreach($lines as $line){
                $line=trim($line);
                if($line!=""){
                    if(!($line{0}=="#" || $line{0}.$line{1}=="--")){
                        $sqlstr.=$line;
                    }
                }
            }
            $sqls=explode(";",rtrim($sqlstr,";"));
            return $sqls;
        }else{
            return false;
        }
    }

    //HTMLL创建页面
    public function  buildCreateHtml(){

        global $wgSkinsList;
        $optionsReason = array();
        foreach($this->optionsR as $k=>$v){
            if($k == 0){
                $optionsReason[] = '<option value="">'.$v.'</option>';
            }else{
                $optionsReason[] = '<option value="'.$k.'">'.$v.'</option>';
            }
        }
        $optionSkins = array();
        array_unshift($wgSkinsList,"请选择");//向数组插入元素
        foreach($wgSkinsList as $sk=>$sv){
            if(is_numeric($sk)){
                $optionSkins[] = '<option value="">'.$sv.'</option>';
            }else{
                $optionSkins[] = '<option value="'.$sk.'">'.$sv.'</option>';
            }
        }
        $this->getOutput()->addHTML(
        Xml::openElement( 'form', array( 'method' => 'post', 'action' => $this->getPageTitle()->getLocalUrl() ,'id'=>'create_wiki') ) .
        Xml::openElement( 'fieldset' ) .
        Xml::openElement( 'table', array( 'id' => 'mw-createwiki-table' ) ) ."<tr><td class='mw-input'>" .
        $this->msg( 'createwiki-wiki-name' )->text()."</td><td>".
        Xml::input( 'wiki_name', false, $this->name, array( 'type' => 'text','for'=>'wiki_name','id'=>'wiki_name') ).
        Xml::openElement( 'span', array( 'color'=>'red','id'=>'error_wiki_name' )).
        "</td><td></tr><tr><td>".
        $this->msg( 'createwiki-wiki-key' )->text()."</td><td>".
        Xml::input( 'wiki_key', false, $this->key, array( 'type' => 'text','for'=>'wiki_key','id'=>'wiki_key' ) ).
        Xml::openElement( 'span', array( 'color'=>'red','id'=>'error_wiki_key' )).
        "</td><td></tr><tr><td>".
        $this->msg( 'createwiki-wiki-icon' )->text()."</td><td>".
        Xml::input( 'wiki_icon', false, $this->key, array( 'type' => 'text','for'=>'wiki_icon','id'=>'wiki_icon') ).
        Xml::openElement( 'span', array( 'color'=>'red','id'=>'error_wiki_icon' ))."</td><td></tr><tr><td>".
        $this->msg( 'createwiki-skins' )->text()."</td><td>".
        Xml::tags( 'select',array('name'=>'create_skins_type','id'=>'create_skins_type','for'=>'create_skins_type'),implode( "\n", $optionSkins ) ) .'</td><td>'.
        "<span id='error_create_skins_type' style='color:red'></span></td><td></tr><tr><td>".
        $this->msg( 'createwiki-reason' )->text()."</td><td>".
        Xml::tags( 'select',array('name'=>'create_reason','id'=>'create_reason','for'=>'create_reason'),implode( "\n", $optionsReason ) ) .'</td><td>'.
        "<span id='error_create_reason' style='color:red'></span></td><td></tr><tr><td>".
        $this->msg( 'createwiki-need-phone' )->text()."</td><td>".
        $this->msg( 'createwiki-need-phone-yes' )->text().
        Xml::input( 'createwiki-need-phone', false, 1, array( 'type' => 'radio') ).
        $this->msg( 'createwiki-need-phone-no' )->text().
        Xml::input( 'createwiki-need-phone', false, 0, array( 'type' => 'radio','checked'=>'checked') )."</td><td></tr><tr><td>".
        $this->msg( 'createwiki-note' )->text()."</td><td>".
        Xml::textarea( 'create_note',false)."</td><td></tr><tr><td colspan='2'>".
        $this->msg( 'createwiki-seo-info' )->text()."<tr><td>".
        $this->msg( 'createwiki—wiki-title' )->text()."</td><td>".
        Xml::input( 'wiki_title', false, $this->title, array( 'type' => 'text','for'=>'wiki_title','id'=>'wiki_title') ).
        Xml::openElement( 'span', array( 'color'=>'red','id'=>'error_wiki_title' ))."</td><td></tr><tr><td>".
        $this->msg( 'createwiki—wiki-keywords' )->text()."</td><td>".
        Xml::input( 'wiki_keywords', false, $this->keywords, array( 'type' => 'text','for'=>'wiki_keywords','id'=>'wiki_keywords') ).
        Xml::openElement( 'span', array( 'color'=>'red','id'=>'error_wiki_keywords' ))."</td><td></tr><tr><td>".
        $this->msg( 'createwiki—wiki-description' )->text()."</td><td>".
        Xml::input( 'wiki_description', false, $this->description, array( 'type' => 'text','for'=>'wiki_description','id'=>'wiki_description') ).
        Xml::openElement( 'span', array( 'color'=>'red','id'=>'error_wiki_description' ))."</td><td></tr><tr><td>".
        Xml::submitButton(
            $this->msg( 'createwiki-submit' )->text(),
            array(
                'name' => 'submit',
                'tabindex' => '4',
                'id' => 'submit-go'
            )
        ) ."</td><td>".
//        "<a id='back_list' href=".$this->getPageTitle()->getLocalUrl('create_history=true').">".
        Xml::input( 'createwiki-wiki-return',false,$this->msg( 'createwiki-wiki-return' )->text(),array( 'type'=>'button','id'=>'createwikireturn' )).
//        "</a></td></tr>".
        "</td></tr>".
        Xml::closeElement( 'table' ) .
        Xml::closeElement( 'fieldset' ) .
        Xml::input('url',false, $this->getPageTitle()->getLocalUrl(),array('type'=>'hidden','id'=>'url')).
        Xml::input('createwiki-wiki-return',false, $this->getPageTitle()->getLocalUrl( 'create_history=true' ),array('type'=>'hidden','id'=>'createwiki-wiki-return')).
            '<div id="loading" ></div>'.
        Xml::closeElement( 'form' ) . "\n"
    );
    }



    //历史页面
    function buildCreateHistoryList(){

        $this->getOutput()->addHTML(
            "<a href=".$this->getPageTitle()->getLocalUrl().">".
            Xml::input( 'createwiki-wiki-return',false,$this->msg( 'create-add-new' )->text(),array( 'type'=>'button' ))
            ."</a>"
        );
        $data = $this->HistoryListData();
        $text = Xml::openElement( 'table', array( 'class' => 'wikitable mw-system-message-table' ) );

//        $text.=$this->msg( 'createwiki-history' )->parse();
        $text.=Xml::openElement( 'tr' ) .
            Xml::tags( 'td',null,$this->msg( 'history-site-id' )->parse() ) .
            Xml::tags( 'td',null,$this->msg( 'createwiki-wiki-icon' )->parse() ) .
            Xml::tags( 'td',null,$this->msg( 'history-site-name' )->parse()) .
            Xml::tags( 'td',null,$this->msg( 'history-site-key' )->parse()).
            Xml::tags( 'td',null,$this->msg( 'history-create-time' )->parse()).
            Xml::tags( 'td',null,$this->msg( 'history-user_name' )->parse()).
            Xml::tags( 'td',null,$this->msg( 'createwiki-type' )->parse()).
            Xml::tags( 'td',null,$this->msg( 'createwiki-reason' )->parse()).
            Xml::tags( 'td',null,$this->msg( 'createwiki-note' )->parse()).
            Xml::closeElement( 'tr' );
        if($data['rs']==0){
            foreach($data['data'] as $k=>$v){
            	
            	$v->site_icon = empty($v->site_icon)?$this->site_default_icon:$v->site_icon;
                $type = $v->site_type==1?'原生wiki':$this->msg( 'createwiki-games-type' )->text();
                if($v->create_reason == 1){
                    $reason = $this->msg( 'createwiki-reason-r1' )->text();
                }elseif($v->create_reason == 2){
                    $reason = $this->msg( 'createwiki-reason-r2' )->text();
                }elseif($v->create_reason == 3){
                    $reason = $this->msg( 'createwiki-reason-r3' )->text();
                }elseif($v->create_reason == 4){
                    $reason = $this->msg( 'createwiki-reason-r4' )->text();
                }elseif($v->create_reason == 5){
                    $reason = $this->msg( 'createwiki-reason-r5' )->text();
                }else{
                    $reason = '';
                }
                $text.=Xml::openElement( 'tr' ) .
                    Xml::tags( 'td',null,$v->site_id ) .
                    Xml::tags( 'td',null,'<img src="'.$v->site_icon.'" width=30px; height=30px;/>') .
                    Xml::tags( 'td',null,$v->site_name) .
                    Xml::tags( 'td',null,$v->site_key).
                    Xml::tags( 'td',null,date('Y-m-d H:i:s',$v->create_time)).
                    Xml::tags( 'td',null,$v->user_name).
                    Xml::tags( 'td',null,$type).
                    Xml::tags( 'td',null,$reason).
                    Xml::tags( 'td',null,$v->create_remark).
                    Xml::closeElement( 'tr' );
            }
            $text.=Xml::openElement( 'tr' ) .
                Xml::tags( 'td', array( 'colspan' => '9','align'=> 'center') ,
                    $data['page']).
                Xml::closeElement( 'tr' );
        }
        $text .= Xml::closeElement( 'table' );
        $this->getOutput()->addHTML( $this->buildSearchForm(). $text );
    }


    //历史页面分页数据
    function HistoryListData(){

        $result = array(
            'rs'=>1,
        );
        $pb_page = Request::get('pb_page',1);
        $skip = ($pb_page-1)*self::perpage;

        $url = $this->getPageTitle()->getLocalUrl('create_history=true');

        $condition = array( );
        if( key_exists('site_type',$this->search_array) ){
            $condition['search-wiki-type'] = $this->search_array['site_type'];
        }

        if(key_exists('create_reason',$this->search_array)){
            $condition['search-wiki-reason'] = $this->search_array['create_reason'];
        }

        if(key_exists('create_time',$this->paras)){
            $condition['search_time'] = $this->paras['create_time'];
        }

        $total = CreateWikiClass::joyme_Site_Count( $this->search_array );
        $res = CreateWikiClass::joyme_Site_List( self::perpage ,$skip ,$this->search_array);
        $data = array();
        if ( $total ){
            foreach($res as $v){
                $data[] = $v;
            }
            $_page = new Page(array('total' => $total,'perpage'=>self::perpage,'nowindex'=>$pb_page,'pagebarnum'=>10,'url'=>$url));
            $page_str = $_page->show(2,$condition);
            $result = array(
                'rs'=>0,
                'data'=>$data,
                'page'=>$page_str
            );
        }
        return $result;
    }


    //搜索表单
    function buildSearchForm(){

        $time = CreateWikiClass::getAllWikiTime();
        $optionsTime = array(
            '<option value="">请选择</option>'
        );
        foreach($time as $k=>$v){
            if(key_exists('create_time',$this->paras)){
                if($this->paras['create_time'] == $v->time){
                    $optionsTime[] = '<option value="'.$v->time.'" selected="selected">'.$v->time.'</option>';
                }else{
                    $optionsTime[] = '<option value="'.$v->time.'">'.$v->time.'</option>';
                }
            }else{
                $optionsTime[] = '<option value="'.$v->time.'">'.$v->time.'</option>';
            }
        }

        $optionsType = array();
        foreach($this->optionsT as $k=>$v){
            if(key_exists('site_type',$this->search_array)){
                if($this->search_array['site_type'] == $k){
                    $optionsType[] = '<option value="'.$k.'" selected="selected">'.$v.'</option>';
                }else{
                    $optionsType[] = '<option value="'.$k.'">'.$v.'</option>';
                }
            }else{
                $optionsType[] = '<option value="'.$k.'">'.$v.'</option>';
            }
        }


        $optionsReason = array();
        foreach($this->optionsR as $k=>$v){
            if(key_exists('create_reason',$this->search_array)){
                if($this->search_array['create_reason'] == $k){
                    $optionsReason[] = '<option value="'.$k.'" selected="selected">'.$v.'</option>';
                }else{
                    $optionsReason[] = '<option value="'.$k.'">'.$v.'</option>';
                }
            }else{
                $optionsReason[] = '<option value="'.$k.'">'.$v.'</option>';
            }
        }
        return Xml::openElement( 'form', array( 'method' => 'post', 'action' => $this->getPageTitle()->getLocalUrl() ,'id'=>'search_wiki') ) .
        Xml::openElement( 'fieldset' ) .
        Xml::openElement( 'table', array( 'id' => 'mw-createwiki-table' ) ) ."<tr><td class='TablePager_col_pr_page'>" .
        $this->msg( 'search-wiki-type' )->text()."</td><td>".
        Xml::tags( 'select',array('name'=>'search-wiki-type'),implode( "\n", $optionsType ) ) .'</td><td>'.
        $this->msg( 'search-wiki-reason' )->text()."</td><td>".
        Xml::tags( 'select',array('name'=>'search-wiki-reason'),implode( "\n", $optionsReason ) ) .'</td><td>'.
        $this->msg( 'search-wiki-time' )->text()."</td><td>".
        Xml::tags( 'select',array('name'=>'search_time'),implode( "\n", $optionsTime ) ) .'</td><td>'.
        Xml::submitButton(
            $this->msg( 'search-wiki-value' )->text(),
            array(
                'name' => 'submit',
                'tabindex' => '4',
                'id' => 'submit-go'
            )
        ) ."</td></tr>".
        Xml::closeElement( 'table' ) .
        Xml::closeElement( 'fieldset' ) .
        Xml::input('search_wiki',false, true,array('type'=>'hidden')).
        Xml::closeElement( 'form' ) . "\n";
    }


    public static function returnJson( $data , $callback = '' ){

        if (strlen($callback) > 1) {
            echo $callback . "([" . json_encode($data) . "])";
        } else {
            echo json_encode($data);
        }
        exit;
    }

    protected function getGroupName() {
        return 'wiki';
    }
}