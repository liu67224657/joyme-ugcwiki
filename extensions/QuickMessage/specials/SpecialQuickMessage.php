<?php
ini_set("memory_limit","1024M");
use Joyme\core\Request;
use Joyme\page\Page;
class SpecialQuickMessage extends SpecialPage{

    //快速消息类型
    public $qkms = '2000';
    //分页
    const perpage = 10;
    //搜索条件
    public $search_array = array();
    //分页参数
    public $paras = array();
    //页面参数
    public $pagePaars = array();

    //详情按状态搜索
    public $optionStatus = array(
        0=>'请选择',
        1=>'成功',
        2=>'失败',
        3=>'不存在'
    );

    //允许发送的最大条数
    public $sen_max_num = 200;

    public function __construct(){

        parent::__construct('QuickMessage', 'quickmessage');
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
            $req = $this->getRequest();

            if($wgWikiname !='home'){
                $output->redirectHome('Special:QuickMessage');
                return false;
            }
            if( $wgIsLogin ){
                if (!$wgUser->isAllowed( 'quickmessage' ) ) {
                    throw new PermissionsError( 'quickmessage' );
                }
                if ( wfReadOnly() ) {
                    throw new ReadOnlyError;
                }
                if ( $wgUser->isBlocked() ) {
                    throw new UserBlockedError( $this->getUser()->mBlock );
                }
                // Add CSS
                $output->addModuleStyles(
                    array(
                        'ext.QuickMessage.css',
                        'ext.socialprofile.userprofile.usercentercommon.css'
                    )
                );
                // Add JS
                $output->addModuleScripts(
                    array(
                        'ext.QuickMessage.js'
                    )
                );
                //get paras
                $search_theme = $req->getVal( 'search_theme');
                if($search_theme){
                    $this->search_array[] = "theme like '%".$search_theme."%'";
                    $this->paras['theme'] = $search_theme;
                }

                $search_status = $req->getVal( 'search_status');
                if($search_status){
                    $this->search_array['status'] = $search_status;
                }

                $search_user_name = $req->getVal( 'item-search_user_name');
                if($search_user_name){
                    $this->search_array[] = "user_name like '%".$search_user_name."%'";;
                    $this->paras['item-search_user_name'] = $search_user_name;
                }

                $list_item = $req->getVal( 'list_item');
                if($list_item){
                    $this->search_array['um_id'] = $list_item;
                }

                $suc_num = $req->getVal( 'suc_num');
                if($suc_num){
                    $this->paras['suc_num'] = $suc_num;
                }

                $um_id = $req->getVal( 'um_id');
                if($um_id){
                    $this->search_array['um_id'] = $um_id;
                }

                $searchStartTime = $req->getVal( 'searchStartTime');
                $searchEndTime = $req->getVal( 'searchEndtTime');
                //列表搜索开始时间
                if($searchStartTime && !$searchEndTime){
                    $this->search_array[] = 'create_time >= '.strtotime($searchStartTime);
                    $this->paras['sTime'] = $searchStartTime;
                    $this->pagePaars['sTime']= $searchStartTime;
                }
                //列表搜索结束时间
                if($searchEndTime && !$searchStartTime){

                    $this->pagePaars['eTime']= substr($searchEndTime,0,10);
                    if($req->wasPosted()){
                        $searchEndTime = $searchEndTime.' 23:59:59';
                    }
                    $this->search_array[] = 'create_time <= '.strtotime($searchEndTime);
                    $this->paras['eTime'] = $searchEndTime;

                }
                //开始结束时间都不为空
                if($searchStartTime && $searchEndTime){

                    $this->pagePaars['sTime']= $searchStartTime;
                    $this->pagePaars['eTime']= substr($searchEndTime,0,10);;
                    if($req->wasPosted()){
                        $searchEndTime = $searchEndTime.' 23:59:59';
                    }
                    $this->search_array[] = 'create_time <= '.strtotime($searchEndTime) .' && create_time >= '.strtotime($searchStartTime);
                    $this->paras['sTime'] = $searchStartTime;
                    $this->paras['eTime'] = $searchEndTime;
                }

                if($req->getVal( 'downCsv')){
                    $this->downCsv();
                    die;
                }

                if($req->getVal( 'downXlsx')){
                    $this->downXlsx();
                    die;
                }

                if($req->wasPosted()){
                    $form_name = $req->getVal('quick_message_check_list');
                    $search_user_message = $req->getVal('search_user_message');
                    $search_user_message_item = $req->getVal('search_user_message_item');
                    //!check列表提交
                    if(!$form_name && !$search_user_message && !$search_user_message_item){
                        $suffix_flag = false;
                        $suffix  = substr(strrchr($_FILES['quickmessage_upload_file']['name'], '.'), 1);
                        if($suffix == 'xlsx'){
                            $contents = $this->getExcelContents( $_FILES['quickmessage_upload_file']['tmp_name'] );
                            $suffix_flag = true;
                        }else if($suffix == 'csv'){
                            $contents = eval('return '.iconv('gbk','utf-8',var_export($this->getFileContent($_FILES['quickmessage_upload_file']['tmp_name']),true)).';');
                            $suffix_flag = true;
                        }
                        if($suffix_flag){
                            $theme = $req->getVal('user_message_theme');
                            $content_format = $req->getVal('quickmessage_content_format');
                            if(isset($contents[0][0])){
                                if(count($contents)<=$this->sen_max_num){
                                    foreach($contents as $ck=>&$cv){
                                        $userInfo = JoymeWikiUser::getUserIdByUserName(ucfirst($cv[0]));
                                        if( $userInfo ){
                                            $cv['user_id'] = $userInfo->user_id;
                                        }else{
                                            $cv['user_id'] = 0;
                                        }
                                    }
                                    //排序，名字不存在的排前列
                                    $sort = array(
                                        'direction' => 'SORT_ASC',
                                        'field'     => 'user_id',
                                    );
                                    $arrSort = array();
                                    foreach($contents AS $uniqid => &$row){
                                        foreach($row AS $key=>$value){
                                            $arrSort[$key][$uniqid] = $value;
                                        }
                                        //转换发送内容
                                        $content_message = str_replace('<用户名>',$row[0],$content_format);
                                        for($i=1;$i<=count($row)-2;$i++){
                                            $content_message = str_replace("<参数$i>",$row[$i],$content_message);
                                        }
                                        $row['content'] = $content_message;
                                    }
                                    if($sort['direction']){
                                        array_multisort($arrSort[$sort['field']], constant($sort['direction']), $contents);
                                    }

                                    $more_than = false;
                                    foreach($contents as $mk=>$mv){
                                        if(mb_strlen($mv['content'],'UTF8')>2000){
                                            $more_than = true;
                                        }
                                    }
                                    if($more_than){
                                        $output->addHTML(
                                            '<span class="view-status">' .
                                            $this->msg( 'quickmessage-send-more—then' )->plain() .
                                            '</span><br /><br />'
                                        );
                                        $this->getOutput()->addReturnTo( SpecialPage::getTitleFor( 'quickmessage' ) );
                                    }else{
                                        $this->buidCheckUserPage($contents,$theme,$content_format);
                                    }
                                }else{
                                    $output->addHTML(
                                        '<span class="view-status">' .
                                        $this->msg( 'quickmessage-send-max-num' )->plain() .
                                        '</span><br /><br />'
                                    );
                                    $this->getOutput()->addReturnTo( SpecialPage::getTitleFor( 'quickmessage' ) );
                                }
                            }else{
                                if($contents === 1){
                                    $this->getOutput()->addHTML(
                                        '<span class="view-status">' .
                                        $this->msg( 'quickmessage-templete-type' )->plain() .
                                        '</span><br /><br />'
                                    );
                                }else{
                                    $output->addHTML(
                                        '<span class="view-status">' .
                                        $this->msg( 'quickmessage-create-empty-tip' )->plain() .
                                        '</span><br /><br />'
                                    );
                                }
                                $this->getOutput()->addReturnTo( SpecialPage::getTitleFor( 'quickmessage' ) );
                            }
                        }else{
                            $output->addHTML(
                                '<span class="view-status">' .
                                $this->msg( 'quickmessage-quickmessage-error' )->plain() .
                                '</span><br /><br />'
                            );
                            $this->getOutput()->addReturnTo( SpecialPage::getTitleFor( 'quickmessage' ) );
                        }
                    }elseif($form_name && !$search_user_message && !$search_user_message_item){

                        $quick_message_user_ids = $req->getArray('quick-message-user-id');
                        $quick_message_contents = $req->getArray('quick-message-content');
                        $quick_message_username = $req->getArray('quick-message-username');
                        $quick_message_check_theme = $req->getVal('quick_message_check_theme');
                        $quick_message_check_content_format = $req->getVal('quick_message_check_content_format');

                        //开始发送消息
                        if($quick_message_user_ids && $quick_message_contents && $quick_message_username){
                            //先记录一键消息主表
                            $um_id = QuickUserMessageClass::add_User_Message($quick_message_check_theme,$quick_message_check_content_format,count($quick_message_user_ids),0,$wgUser->getName());
                            $success_num = 0;
                            foreach($quick_message_user_ids as $quk=>$quv){
                                //已知用户消息发送
                                if($quv != 0){
                                    if(SendSystemMessage::pullQuickMessage( $quv,$wgUser->mId,$wgUser->getName(),$quick_message_contents[$quk],$this->qkms)){
                                        $success_num++;
                                        $status = 1;
                                    }else{
                                        $status = 2;
                                    }
                                }else{
                                    $status = 3;
                                }
                                QuickUserMessageItemClass::add_User_Message_Item($um_id,$quick_message_username[$quk],$quick_message_user_ids[$quk],$quick_message_contents[$quk],time(),$status);
                            }
                            QuickUserMessageClass::Update_User_Message_Success_num($um_id,$success_num);
                            //发送完成
                            $this->search_array['um_id'] = $um_id;
                            $this->paras['suc_num'] = $success_num;
                            $this->buildSendMessageEndPage();
                        }else{
                            $output->addHTML(
                                '<span class="view-status">' .
                                $this->msg( 'quickmessage-create-post-empty-tip' )->plain() .
                                '</span><br /><br />'
                            );
                            $this->getOutput()->addReturnTo( SpecialPage::getTitleFor( 'quickmessage' ) );
                        }
                    }elseif($search_user_message && !$form_name && !$search_user_message_item){

                        $this->buildCreateHistoryList();
                    }else{
                        $this->buildUserMessageItemPage( );
                    }
                }else{
                    $item_id = $req->getVal( 'list_item');

                    if($req->getVal( 'qkms_action')){
                        //create page
                          $this->buidCreatePage();
                    }elseif($item_id){

                        $this->search_array['um_id'] = $item_id;
                        $this->buildUserMessageItemPage( );
                    }elseif($search_status){

                        $this->search_array['um_id'] = $req->getVal( 'list_item');
                        $this->buildUserMessageItemPage( );
                    }elseif($req->getVal( 'suc_num') && $req->getVal( 'um_id')){

                        $this->buildSendMessageEndPage();
                    }else{

                        $this->buildCreateHistoryList();
                    }
                }
            }else{
                $this->getOutput()->addModuleStyles( 'ext.QuickMessage.not.logged' );
                $this->getOutput()->addHTML($this->msg( 'create-not-logged' )->text());
            }
        }
    }

    //发送消息成功页面
    function buildSendMessageEndPage(){

        $data = $this->sendEndListData();

        $text = Xml::openElement( 'table', array( 'class' => 'wikitable mw-system-message-table' ) );
        $text.=Xml::openElement( 'tr' ) .
            Xml::tags( 'td',null,$this->msg( 'quickmessage-item-list-username' )->parse() ) .
            Xml::tags( 'td',null,$this->msg( 'quickmessage-item-list-content' )->parse() ) .
            Xml::closeElement( 'tr' );
        if($data['rs']==0){
            $this->getOutput()->addHTML(
                $this->msg( 'quickmessage-create-post-success-num', @$this->paras['suc_num'])->parse().'</br>'
            );
            $this->getOutput()->addHTML(
                $this->msg( 'quickmessage-create-post-error-tip' )->parse()
            );
            foreach($data['data'] as $k=>$v){
                $text.=Xml::openElement( 'tr' );
                if($v->user_id==0){
                    $text.=Xml::tags( 'td',array('style'=>'color:red'),$v->user_name ).
                    Xml::tags( 'td',null,$v->content );
                }else{
                    $text.=Xml::tags( 'td',null,$v->user_name ).
                    Xml::tags( 'td',null,$v->content );
                }
                Xml::closeElement( 'tr' );
            }
            $text.=Xml::openElement( 'tr' ) .
                Xml::tags( 'td', array( 'colspan' => '5','align'=> 'center') ,
                    $data['page']).
                Xml::closeElement( 'tr' );
        }else{
            $this->getOutput()->addHTML(
                '<span class="view-status">' .
                $this->msg( 'quickmessage-create-post-success-all' )->plain() .
                '</span><br /><br />'
            );
            $this->getOutput()->addReturnTo( SpecialPage::getTitleFor( 'quickmessage' ) );
        }
        $text .= Xml::closeElement( 'table' );
        $return = Xml::input(
            'create_action',
            false,
            $this->msg( 'quickmessage-send-return' )->text(),
            array(
                'type'=>'button',
                'onclick'=>"window.location.href='".$this->getPageTitle()->getLocalUrl()."'"
            )
        );

        $this->getOutput()->addHTML( $text .$return);
    }

    //消息详情列表
    function buildUserMessageItemPage( ){

        $data = $this->UserMessageItemData();
        $text = Xml::openElement( 'table', array( 'class' => 'wikitable mw-system-message-table' ) );
        $text.=Xml::openElement( 'tr' ) .
            Xml::tags( 'td',null,$this->msg( 'quickmessage-item-list-username' )->parse() ) .
            Xml::tags( 'td',null,$this->msg( 'quickmessage-item-list-content' )->parse() ) .
            Xml::tags( 'td',null,$this->msg( 'quickmessage-item-list-lastsend-time' )->parse()) .
            Xml::tags( 'td',null,$this->msg( 'quickmessage-item-list-status' )->parse()).
            Xml::tags( 'td',null,$this->msg( 'quickmessage-item-list-caozuo' )->parse()).
            Xml::closeElement( 'tr' );
        if($data['rs']==0){
            foreach($data['data'] as $k=>$v){

                if($v->status == 1){
                    $reason = $this->msg( 'quickmessage-item-list-status1' )->text();
                }elseif($v->status == 2){
                    $reason = $this->msg( 'quickmessage-item-list-status2' )->text();
                }else{
                    $reason = $this->msg( 'quickmessage-item-list-status3' )->text();
                }
                $text.=Xml::openElement( 'tr' ) .
                    Xml::tags( 'td',null,$v->user_name ) .
                    Xml::tags( 'td',null,$v->content) .
                    Xml::tags( 'td',array('id'=>$v->umi_id),date('Y-m-d H:i:s',$v->send_time)).
                    Xml::tags( 'td',array('id'=>'status'.$v->umi_id),$reason);
                if($v->status != 3){
                    $text.=Xml::tags( 'td',null,
                        Xml::input('quickmessage-item-agin-send',6,$this->msg( 'quickmessage-item-list-again' )->parse(),array('type'=>'button','class'=>'quickmessage_item_agin_send_button','item_data'=>$v->umi_id,'send_um_id'=>$v->um_id,'status_flag'=>$v->status))
                    );
                }else{
                    $text.=Xml::tags( 'td',null,false);
                }
                Xml::closeElement( 'tr' );
            }
            $text.=Xml::openElement( 'tr' ) .
                Xml::tags( 'td', array( 'colspan' => '5','align'=> 'center') ,
                    $data['page']).
                Xml::closeElement( 'tr' );
        }else{
            $text.=Xml::openElement( 'tr' ) .
                Xml::tags( 'td', array( 'colspan' => '5','align'=> 'center') ,
                    $this->msg( 'quickmessage-list-empty-tip' )->parse());
            Xml::closeElement( 'tr' );
        }
        $text .= Xml::closeElement( 'table' );
        $this->getOutput()->addHTML( $this->buildSearchItemForm(). $text );
    }


    //消息主列表页面
    function buildCreateHistoryList(){

        $data = $this->HistoryListData();
        $text = Xml::openElement( 'table', array( 'class' => 'wikitable mw-system-message-table' ) );
        $text.=Xml::openElement( 'tr' ) .
            Xml::tags( 'td',null,$this->msg( 'quickmessage-list-theme' )->parse() ) .
            Xml::tags( 'td',null,$this->msg( 'quickmessage-list-content' )->parse() ) .
            Xml::tags( 'td',null,$this->msg( 'quickmessage-list-send-time' )->parse()) .
            Xml::tags( 'td',null,$this->msg( 'quickmessage-list-send-success-num' )->parse()).
            Xml::tags( 'td',null,$this->msg( 'quickmessage-list-caozuo' )->parse()).
            Xml::closeElement( 'tr' );
        if($data['rs']==0){
            foreach($data['data'] as $k=>$v){
                $text.=Xml::openElement( 'tr' ) .
                    Xml::tags( 'td',null,$v->theme ) .
                    Xml::tags( 'td',null,$v->content_format) .
                    Xml::tags( 'td',null,date('Y-m-d H:i:s',$v->create_time)).
                    Xml::tags( 'td',null,$v->success_num) .
                    Xml::tags( 'td',null,"<a id='back_list' href=".$this->getPageTitle()->getLocalUrl('list_item='.$v->um_id).">".$this->msg( 'quickmessage-list-see' )->parse())."</a>".
                    Xml::closeElement( 'tr' );
            }
            $text.=Xml::openElement( 'tr' ) .
                Xml::tags( 'td', array( 'colspan' => '5','align'=> 'center') ,
                    $data['page']).
                Xml::closeElement( 'tr' );
        }else{
            $text.=Xml::openElement( 'tr' ) .
                   Xml::tags( 'td', array( 'colspan' => '5','align'=> 'center') ,
                       $this->msg( 'quickmessage-list-empty-tip' )->parse());
            Xml::closeElement( 'tr' );
        }
        $text .= Xml::closeElement( 'table' );
        $this->getOutput()->addHTML( $this->buildSearchForm(). $text );
    }

    //搜索表单
    function buildSearchForm(){

        return Xml::openElement( 'form', array( 'method' => 'post', 'action' => $this->getPageTitle()->getLocalUrl()) ) .
        Xml::openElement( 'fieldset' ) .
        Xml::openElement( 'table', array( 'id' => 'mw-createwiki-table' ) ) ."<tr><td class='TablePager_col_pr_page'>" .
        $this->msg( 'quickmessage-search-time' )->text()."</td><td>".
        Html::rawElement( 'div',
            array(
                'class' => 'input-group input-append date',
                'id' => 'quickmessageStartTime'
            ),
            Html::element( 'input',
                array(
                    'type' => 'text',
                    'class' => 'form-control',
                    'name' =>'searchStartTime',
                    'value' =>@$this->pagePaars['sTime']
                )
            ).
            Html::rawElement( 'span',
                array(
                    'class'=>'input-group-addon'
                )
            ).
            Html::element( 'span',
                array(
                    'class' => 'glyphicon glyphicon-calendar'
                )
            )
        ) .'</td><td>'.
        $this->msg( 'quickmessage-search-time2' )->text()."</td><td>".
        Html::rawElement( 'div',
            array(
                'class' => 'input-group input-append date',
                'id' => 'quickmessageEndTime'
            ),
            Html::element( 'input',
                array(
                    'type' => 'text',
                    'class' => 'form-control',
                    'name' =>'searchEndtTime',
                    'value' =>@$this->pagePaars['eTime']
                )
            ).
            Html::rawElement( 'span',
                array(
                    'class'=>'input-group-addon'
                )
            ).
            Html::element( 'span',
                array(
                    'class' => 'glyphicon glyphicon-calendar'
                )
            )
        ).'</td><td>'.
        $this->msg( 'quickmessage-search-theme' )->text()."</td><td>".
        Xml::input('search_theme',12,@$this->paras['theme']) .'</td><td>'.
        Xml::submitButton(
            $this->msg( 'search-wiki-value' )->text(),
            array(
                'name' => 'submit',
                'tabindex' => '4',
                'id' => 'submit-go'
            )
        ) .
        Xml::input(
            'create_action',
            false,
            $this->msg( 'quickmessage-create-action' )->text(),
            array(
                'type'=>'button',
                'onclick'=>"window.location.href='".$this->getPageTitle()->getLocalUrl('qkms_action=create')."'"
            )
        ) ."</td></tr>".
        Xml::closeElement( 'table' ) .
        Xml::closeElement( 'fieldset' ) .
        Xml::input('search_user_message',false, true,array('type'=>'hidden')).

        Xml::closeElement( 'form' ) . "\n";

    }

    //搜索详情表单
    function buildSearchItemForm(){

        $optionsReason = array();

        foreach($this->optionStatus as $k=>$v){
            if(key_exists('status',$this->search_array) && $this->search_array['status']!=0){
                if($this->search_array['status'] == $k){
                    $optionsReason[] = '<option value="'.$k.'" selected="selected">'.$v.'</option>';
                }else{
                    $optionsReason[] = '<option value="'.$k.'">'.$v.'</option>';
                }
            }else{
                if($k == 0){
                    $optionsReason[] = '<option value="">'.$v.'</option>';
                }else{
                    $optionsReason[] = '<option value="'.$k.'">'.$v.'</option>';
                }
            }
        }

        return Xml::openElement( 'form', array( 'method' => 'post', 'action' => $this->getPageTitle()->getLocalUrl()) ) .
        Xml::openElement( 'fieldset' ) .
        Xml::openElement( 'table', array( 'id' => 'mw-createwiki-table' ) ) ."<tr><td class='TablePager_col_pr_page'>" .
        $this->msg( 'quickmessage-item-list-search_username' )->text()."</td><td>".
        Xml::input('item-search_user_name',12,@$this->paras['item-search_user_name']) .'</td><td>'.
        $this->msg( 'quickmessage-item-list-search_status' )->text()."</td><td>".
        Xml::tags( 'select',array('name'=>'search_status'),implode( "\n", $optionsReason ) ).'</td><td>'.
        Xml::submitButton(
            $this->msg( 'search-wiki-value' )->text(),
            array(
                'name' => 'submit',
                'tabindex' => '4',
                'id' => 'submit-go'
            )
        ) .
        Xml::input(
            'create_action',
            false,
            $this->msg( 'quickmessage-send-return' )->text(),
            array(
                'type'=>'button',
                'onclick'=>"window.location.href='".$this->getPageTitle()->getLocalUrl()."'"
            )
        ) ."</td></tr>".
        Xml::closeElement( 'table' ) .
        Xml::closeElement( 'fieldset' ) .
        Xml::input('list_item',false, $this->search_array['um_id'],array('type'=>'hidden')).
        Xml::input('search_user_message_item',false, true,array('type'=>'hidden')).
        Xml::closeElement( 'form' ) . "\n";

    }

    //历史页面分页数据
    function HistoryListData(){

        $result = array(
            'rs'=>1,
        );
        $pb_page = Request::get('pb_page',1);
        $skip = ($pb_page-1)*self::perpage;
        $url = $this->getPageTitle()->getLocalUrl('conf=true');
        $condition = array( );
        //开始时间
        if( key_exists('sTime',$this->paras) ){
            $condition['searchStartTime'] = $this->paras['sTime'];
        }
        //结束时间
        if(key_exists('eTime',$this->paras)){
            $condition['searchEndtTime'] = $this->paras['eTime'];
        }
        //主题
        if(key_exists('theme',$this->paras)){
            $condition['search_theme'] = $this->paras['theme'];
        }

        $total = QuickUserMessageClass::select_Messages_Count( $this->search_array );
        $res = QuickUserMessageClass::select_Messages_List( self::perpage ,$skip ,$this->search_array);
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

    //详情列表数据
    function UserMessageItemData(){

        $result = array(
            'rs'=>1,
        );
        $pb_page = Request::get('pb_page',1);
        $skip = ($pb_page-1)*self::perpage;
        $url = $this->getPageTitle()->getLocalUrl('conf=true');
        $condition = array( );
        //用户名
        if(key_exists('item-search_user_name',$this->paras)){
            $condition['item-search_user_name'] =  $this->paras['item-search_user_name'];
        }
        //主题
        if(key_exists('status',$this->search_array)){
            $condition['search_status'] = $this->search_array['status'];
        }

        $condition['list_item'] = $this->search_array['um_id'];

        $total = QuickUserMessageItemClass::select_Messages_Item_Count( $this->search_array );
        $res = QuickUserMessageItemClass::select_Messages_List( self::perpage ,$skip ,$this->search_array);
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

    //发送完成页面数据
    function sendEndListData(){

        $result = array(
            'rs'=>1,
        );
        $pb_page = Request::get('pb_page',1);
        $skip = ($pb_page-1)*self::perpage;
        $url = $this->getPageTitle()->getLocalUrl('conf=true');
        $condition = array( );
        //主题
        if(key_exists('um_id',$this->search_array)){
            $condition['um_id'] = $this->search_array['um_id'];
        }

        if(key_exists('suc_num',$this->paras)){
            $condition['suc_num'] = $this->paras['suc_num'];
        }
        $this->search_array[] = 'status in (2,3)';

        $total = QuickUserMessageItemClass::select_Messages_Item_Count( $this->search_array );

        $res = QuickUserMessageItemClass::select_Messages_List( self::perpage ,$skip ,$this->search_array);

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

    //下一步页面
    function buidCheckUserPage( $data,$theme,$content_format ){

        $this->getOutput()->addHTML(
            $this->msg( 'quickmessage-check-tip' )->parse()
        );
        $text=Xml::openElement( 'form', array( 'method' => 'post', 'action' => $this->getPageTitle()->getLocalUrl()) );
        $text.= Xml::openElement( 'table', array( 'class' => 'wikitable mw-system-message-table' ) );
        $text.=Xml::openElement( 'tr' ) .
            Xml::tags( 'td',null,$this->msg( 'quickmessage-check-list-username' )->parse() ) .
            Xml::tags( 'td',null,$this->msg( 'quickmessage-check-list-content' )->parse() ) .
            Xml::closeElement( 'tr' );
        if($data){
            foreach($data as $k=>$v){
                $text.=Xml::openElement( 'tr' );
                if($v['user_id']==0){
                    $text.=Xml::tags( 'td',array('style'=>'color:red'),$v[0] );
                }else{
                    $text.=Xml::tags( 'td',null,$v[0] );
                }
                $text.=Xml::tags( 'td',null,$v['content']).
                Xml::input('quick-message-user-id[]',false,$v['user_id'],array('type'=>'hidden')).
                Xml::input('quick-message-username[]',false,$v[0],array('type'=>'hidden')).
                Xml::input('quick-message-content[]',false,$v['content'],array('type'=>'hidden')).
                Xml::closeElement( 'tr' ).'<td>';
            }
        }
        $text.=Xml::submitButton(
                $this->msg( 'quickmessage-send-message' )->text(),
                array(
                    'name' => 'submit',
                    'tabindex' => '4'
                )
            ) ."</td>";
        $text.=Xml::tags( 'td',null,Xml::input( 'quickmessage-send-return',false,$this->msg( 'quickmessage-send-return' )->text(),array( 'type'=>'button','id'=>'quickreturn',"onclick"=>"if(confirm('返回后填写内容将被清空')==true){window.location.href='".$this->getPageTitle()->getLocalUrl()."'};"))).'</tr>';
        $text.=Xml::input('quick_message_check_list',false,true,array('type'=>'hidden'));
        $text.=Xml::input('quick_message_check_content_format',false,$content_format,array('type'=>'hidden'));
        $text.=Xml::input('quick_message_check_theme',false,$theme,array('type'=>'hidden'));
        $text.=Xml::input('createwiki-wiki-return',false, $this->getPageTitle()->getLocalUrl(),array('type'=>'hidden','id'=>'quickmessage-wiki-return'));
        $text.= Xml::closeElement( 'table' ).Xml::closeElement( 'form' ) . "\n";
        $this->getOutput()->addHTML( $text );
    }

    //创建页面
    function buidCreatePage(){

        global $wgWikiname;
        $this->getOutput()->addHTML(
            Xml::openElement( 'form', array( 'method' => 'post', 'action' => $this->getPageTitle()->getLocalUrl(), 'id'=>'quickmessage','enctype'=>"multipart/form-data" ) ) .
            Xml::openElement( 'fieldset' ) .
            $this->msg( 'quickmessage-download-template' )->text().'<a  target="_blank" href="'.$this->getPageTitle()->getLocalUrl( 'downCsv=true' ).'">&nbsp;&nbsp;&nbsp;csv</a>'.'<a  target="_blank" href="'.$this->getPageTitle()->getLocalUrl( 'downXlsx=true' ).'">&nbsp;&nbsp;&nbsp;excel</a>'.
            Xml::openElement( 'table', array( 'id' => 'mw-quickmessage-table' ) ) ."<tr><td class='mw-input'>" .
            $this->msg( 'quickmessage-templete-instructions' )->text()."</td><td>&nbsp;&nbsp;".$this->msg( 'quickmessage-templete-tip' )->text()."</td></td><td></tr><tr><td>".
            $this->msg( 'quickmessage-upload-file' )->text()."</td><td>".
            Xml::input( 'quickmessage_upload_file', false, false, array( 'type' => 'file','accept'=>".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel") ).
            $this->msg( 'quickmessage-upload-tip' )->text()."</td></td><td></tr><tr><td>".
            $this->msg( 'quickmessage-user-message-theme' )->text()."</td><td>".
            Xml::input( 'user_message_theme', false, false, array( 'type' => 'text','for'=>'user_message_theme','id'=>'user_message_theme') ).'</br>'.
            $this->msg( 'quickmessage-user-message-theme-tip' )->text()."</td></td><td></tr><tr><td>".
            $this->msg( 'quickmessage-user-message-content' )->text()."</td><td>".
            Xml::textarea( 'quickmessage_content_format',false).
            $this->msg( 'quickmessage-user-message-content-tip' )->text()."</td></td><td></tr><tr><td>".
            Xml::submitButton(
                $this->msg( 'quickmessage-next-operation' )->text(),
                array(
                    'name' => 'submit',
                    'tabindex' => '4'
                )
            ) ."</td><td>".
            Xml::input( 'createwiki-wiki-return',false,$this->msg( 'quickmessage-return' )->text(),array( 'type'=>'button','id'=>'createwikireturn' )).
            "</td></tr>".
            Xml::closeElement( 'table' ) .
            Xml::closeElement( 'fieldset' ) .
            Xml::input('url',false, $this->getPageTitle()->getLocalUrl(),array('type'=>'hidden','id'=>'url')).
            Xml::input('createwiki-wiki-return',false, $this->getPageTitle()->getLocalUrl( 'create_history=true' ),array('type'=>'hidden','id'=>'createwiki-wiki-return')).
            Xml::closeElement( 'form' ) . "\n"
        );
    }


    //获取excel表格内容
    function getExcelContents( $filePath ){

        global $IP;
        $file_temp = "/extensions/QuickMessage/public/lib/PHPExcel/IOFactory.php";
        include_once $IP.$file_temp;
        $PHPReader = new PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                return 1;
            }
        }else{
            $PHPExcel = $PHPReader->load($filePath);
            $currentSheet = $PHPExcel->getSheet(0);
            /**取得一共有多少列*/
            $allColumn = $currentSheet->getHighestColumn();
            /**取得一共有多少行*/
            $allRow = $currentSheet->getHighestRow();
            $all = array();
            for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
                $flag = 0;
                $col = array();
                for ($currentColumn = 'A'; $this->getascii($currentColumn) <= $this->getascii($allColumn); $currentColumn++) {

                    $address = $currentColumn . $currentRow;

                    $string = $currentSheet->getCell($address)->getValue();

                    $col[$flag] = $string;

                    $flag++;
                }
                if(!self::array_is_null($col)){
                    $all[] = $col;
                }
            }
            return $all;
        }
    }

    public static function array_is_null($arr = null){

        if(is_array($arr)){
            foreach($arr as $k=>$v){
                if($v&&!is_array($v)){
                    return false;
                }
                $t = self::array_is_null($v);
                if(!$t){
                    return false;
                }
            }
            return true;
        }elseif(!$arr){
            return true;
        }else{
            return false;
        }
    }


    function getascii( $ch) {  //读取字符串的ASCII码
        if( strlen( $ch) == 1)
            return ord( $ch)-65;
        return ord($ch[1])-38;
    }

    //get csv content
    function getFileContent( $path = '' ){

        if(empty($path)){
            return false;
        }
        $goods_list = array();
        $file = fopen($path,'r');
        while ($data = fgetcsv($file)) {
            if(!self::array_is_null($data)){
                $goods_list[] = $data;
            }
        }
        fclose($file);
        return $goods_list;
    }

    //下载csv()
    function downCsv(){

        $this->downHeaser( 'template.csv');
        exit();
    }

    //下载csv()
    function downXlsx(){

        $this->downHeaser( 'template.xlsx');
        exit();
    }


    //模版下载header头
    function downHeaser( $filename ){

        global $IP;
        $file_path = $IP."/extensions/QuickMessage/public/template/".$filename;

        header("Content-type:text/html;charset=utf-8");
        $file_name=iconv("utf-8","gb2312",$filename);
        if(!file_exists($file_path))
        {
            echo "没有该文件文件";
            return ;
        }
        $fp=fopen($file_path,"r");
        $file_size=filesize($file_path);
        //下载文件需要用到的头
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:".$file_size);
        Header("Content-Disposition: attachment; filename=".$file_name);
        $buffer=1024;
        $file_count=0;
        while(!feof($fp) && $file_count<$file_size)
        {
            $file_con=fread($fp,$buffer);
            $file_count+=$buffer;
            echo $file_con;
        }
        fclose($fp);
    }

    protected function getGroupName() {

        return 'wiki';
    }
}