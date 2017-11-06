<?php
use Joyme\core\Request;
use Joyme\page\Page;
class SpecialAboutMe extends UnlistedSpecialPage{

    //定义类型
    public $about_type = false;

    //定义总数
    public $cite_my_count = 0;
    public $thumb_up_count = 0;
    public $consider_me_count = 0;
    public $message_count = 0;
    public $commect_count = 0;

    //每页显示条数
    public $perpage = 10;

    //返回
    public $return = array(
        'rs'=>0,
        'result'=>array(

        )
    );

    public function __construct(){

        if(isMobile()){
            $this->perpage = 15;
        }

        parent::__construct('AboutMe');
    }

    public function execute($par) {

        global $wgUser,$wgWikiname,$wgEnv;

        $output = $this->getOutput();

        $this->setHeaders();
        
        $output->redirectHome('Special:ViewFollows');
        $output->redirect('http://uc.joyme.'.$wgEnv.'/usercenter/home');
        return false;

        if (!$wgUser->isAllowed( 'aboutme' ) ) {
            throw new PermissionsError( 'aboutme' );
        }
        if ( wfReadOnly() ) {
            throw new ReadOnlyError;
        }
        if ( $wgUser->isBlocked() ) {
            throw new UserBlockedError( $this->getUser()->mBlock );
        }

        $output->addModuleStyles( array(
        		'ext.socialprofile.userprofile.usercentercommon.css',
        		'ext.AboutMe.css' 
        		)
        );

        $output->addModules( 'ext.AboutMe' );

        $this->about_type = $this->getRequest()->getVal( 'about_type' , 'article-cite-my' );

        if($this->getRequest()->wasPosted() && $this->getRequest()->getVal( 'confirm_delete' ) == true){

            if($this->getRequest()->getInt('user_id')==$wgUser->mId){

                $type = addslashes($this->getRequest()->getVal( 'even_type' ));
                if(SystemMessageClass::deleteByUserEventOffset( $wgUser->mId,$type )){
                    $data = array( 'rs'=>0,'result'=>'succe' );
                }else{
                    $data = array( 'rs'=>1,'result'=>'error' );
                }
            }else{
                $data = array( 'rs'=>1,'result'=>'error' );
            }
            echo json_encode($data);
            exit;
        }

        if($wgWikiname !='home'){
            $output->redirectHome('Special:AboutMe');
            return false;
        }

        $total = $this->count();

        $this->toReadData( $total , $wgUser);

        $this->getRightSideGroupData();

        $url = $this->getPageTitle()->getLocalUrl('about_type='.$this->about_type);

        $pb_page = $this->getRequest()->getVal( 'pb_page' , 1 );

        $skip = ($pb_page-1)*$this->perpage;

        $notif = array();

        $notifications = $this->getUserEvenNotification( $this->perpage , $skip ,$this->about_type);
        foreach ( $notifications as $notification ) {
            $notif[] = EchoDataOutputFormatter::formatOutput( $notification, 'json', $wgUser );
        }

        if( $notif ){

            $page_str = '';
            if($total > $this->perpage){
                $_page = new Page(array('total' => $total,'perpage'=>$this->perpage,'nowindex'=>$pb_page,'pagebarnum'=>8,'url'=>$url,'classname'=>array( 'main_page'=>'paging ','active'=>'on')));
                $page_str = $_page->show(2);
            }
            $a = array_column($notif,'event_extra');
            $b = array_column($a,'user_id');
            $c = $this->getUserHeadPortrait( array_unique($b) );

            foreach( $notif as $k=>&$v ){

                $lines_string = preg_replace("(<a[^>]*>(.+?)<\/a>)","$1",@$v['event_extra']['synopsis']);
                foreach($c as $uk=>$uv){

                    $back_link = Title::makeTitle( NS_USER, $uv['nick'] );
                    $user_link = htmlspecialchars( $back_link->getFullURL() );
                    if($uv['uid'] == @$v['event_extra']['user_id']){
                        $v['event_extra']['icon'] = $uv['icon'];
                        $v['event_extra']['nick'] = $uv['nick'];
                        $v['event_extra']['user_home_url'] = $user_link;
                        break;
                    }
                    continue;
                }
                $v['event_extra']['synopsis'] = $lines_string;
                $v['event_extra']['action_link'] = '/'.@$v['event_extra']['wikikey'].'/'.@$v['event_extra']['article'];
                $v['event_extra']['content_link'] = '/'.@$v['event_extra']['wikikey'].'/'.@$v['event_extra']['article'].'?plid='.@$v['event_extra']['action_id'];
                $v['timestamp']['time'] = date('Y-m-d H:i',$v['timestamp']['unix']);


            }
            $this->return = array(
                'rs'=>1,
                'result'=>array(
                    'data'=>$notif,
                    'page'=>$page_str,
                    'max_page'=>ceil($total / $this->perpage)
                )
            );
        }
        if( $this->getRequest()->getVal( 'ajax' ) && !$this->getRequest()->wasPosted()){
            echo json_encode($this->return);
            exit;
        }

        $this->toBuildHtml( $this->return );
    }

    //拼接页面
    public function toBuildHtml( $return ){

        $output = $this->getOutput();
        $this->leftSideHtml( $output , $return);
        $this->rightSideHtml( $output );
    }


    //左侧开始
    public function leftSideHtml( $output,$return ){

        $output->addHTML('<div class="col-md-9">
                            <div id="main">
                               <div class="notice-list-box ">
                                 <h1 class="page-h1 pag-hor-20 fn-clear">'.$this->belongToGroup().'<span class="del-all fn-right"><i class="fa fa-trash-o"></i>清空所有</span></h1>
                                    '.$this->toContentHtml( $return )
                                .'</div>'.$this->toPagingHtml( $return ).'
                            </div>
                         </div>');
    }

    //右侧开始
    public function rightSideHtml( $output ){

        global $wgUser;
        $info = $this->getUserHeadPortrait( $wgUser->mId );

        if($info[0]['sex'] == 1){
            $sex = 'user-sex man';
        }elseif (!is_null($info[0]['sex']) && $info[0]['sex'] != '' && $info[0]['sex'] == 0){
            $sex = 'user-sex female';
        }else{
            $sex = '';
        }
        $back_link = Title::makeTitle( NS_USER, $wgUser->mName );
        $user_link = htmlspecialchars( $back_link->getFullURL() );
        $output->addHTML('
                    <div class="col-md-3 web-hide ">
                        <div id="sidebar">
                            <div class="user-mess-box">
                                <div class="user-int-mess">
                                    <a href="'.$user_link.'"><img src="'.$info[0]['icon'].'"></a>
                                    <font class="nickname">'.$wgUser->mName.'</font>
                                    <i class="'.$sex.'"></i>
                                </div>'.$this->toRightSideHtml().'</div>
                        </div>
                    </div>');

    }

    //内容列表
    public function toContentHtml( $return ){

        $toContentHtmlLi = '';
        if ( $return['rs'] == 1 ) {
            switch ($this->about_type){
                case 'article-thumb-up':
                    $toContentHtmlLi .= '<ul class="list-item ">';
                    foreach( $return['result']['data'] as $k=>$v ){
                        $comments_type = $v['event_extra']['type']==1?'评论':'内容';
                        $toContentHtmlLi.= '<li>
                                                <div class="list-item-l">
                                                    <cite><img src="'.$v['event_extra']['icon'].'"></cite>
                                                </div>
                                                <div class="list-item-r situatio-one">
                                                    <div class="item-r-name ">
                                                        <a target="_blank" href="'.$v['event_extra']['user_home_url'].'">'.$v['event_extra']['username'].'</a>赞了我的'.$comments_type.'
                                                    </div>
                                                    <div class="item-r-text">
                                                        <a target="_blank" href="'.$v['event_extra']['content_link'].'">
                                                        '.$v['event_extra']['synopsis'].'
                                                        </a>
                                                    </div>
                                                    <div class="item-r-other fn-clear">
                                                        <b class="from-wiki">出自：'.$v['event_extra']['from'].'</b>
                                                        <b class="time-stamp">'.$v['timestamp']['time'].'</b>
                                                    </div>
                                                </div>
                                            </li>';
                    }
                    $toContentHtmlLi .= '</ul>';
                    break;
                case 'article-comments':
                    $toContentHtmlLi .= '<ul class="list-item">';
                    foreach( $return['result']['data'] as $k=>$v ){

                        if($v['event_extra']['type'] == 1){
                            $comments_type = '发表了评论';
                        }elseif($v['event_extra']['type'] == 2){
                            $comments_type = '回复了你';
                        }else{
                            $comments_type = '回复了:'.$v['event_extra']['othername'];
                        }
                        $toContentHtmlLi.='<li>
                                                <div class="list-item-l">
                                                    <cite><img src="'.$v['event_extra']['icon'].'"></cite>
                                                </div>
                                                <div class="list-item-r situatio-one">
                                                    <div class="item-r-name ">
                                                        <a target="_blank" href="'.$v['event_extra']['user_home_url'].'">'.$v['event_extra']['username'].'</a>在“<a target="_blank" href="'.$v['event_extra']['action_link'].'">'.$v['event_extra']['article'].'</a>”中'.$comments_type.'：
                                                    </div>
                                                    <div class="item-r-text">
                                                        <a target="_blank" href="'.$v['event_extra']['content_link'].'">'.$v['event_extra']['synopsis'].'</a>
                                                    </div>
                                                    <div class="item-r-other fn-clear">
                                                        <b class="from-wiki">出自：'.$v['event_extra']['from'].'</b>
                                                        <b class="time-stamp">'.$v['timestamp']['time'].'</b>
                                                    </div>
                                                </div>
                                           </li>';
                    }
                    $toContentHtmlLi .= '</ul>';
                    break;
                case 'article-cite-my':
                    $toContentHtmlLi .= '<ul class="list-item">';
                    foreach( $return['result']['data'] as $k=>$v ){
                        $toContentHtmlLi.='<li>
                                                <div class="list-item-l">
                                                    <cite><img src="'.$v['event_extra']['icon'].'"></cite>
                                                </div>
                                                <div class="list-item-r situatio-one">
                                                    <div class="item-r-name ">
                                                        <a target="_blank" href="'.$v['event_extra']['user_home_url'].'">'.$v['event_extra']['username'].'</a>在“<a target="_blank" href="'.$v['event_extra']['action_link'].'">'.$v['event_extra']['article'].'</a>”中@了你：
                                                    </div>
                                                    <div class="item-r-text">
                                                        <a target="_blank" href="'.$v['event_extra']['content_link'].'">'.$v['event_extra']['synopsis'].'</a>
                                                    </div>
                                                    <div class="item-r-other fn-clear">
                                                        <b class="from-wiki">出自：'.$v['event_extra']['from'].'</b>
                                                        <b class="time-stamp">'.$v['timestamp']['time'].'</b>
                                                    </div>
                                                </div>
                                           </li>';
                    }
                    $toContentHtmlLi .= '</ul>';
                    break;
                case 'article-consider-me':
                    $toContentHtmlLi .= '<ul class="list-item">';
                    foreach( $return['result']['data'] as $k=>$v ){
                        $flag = $v['event_extra']['type']?'关注了你，去他的<a target="_blank" href="'.$v['event_extra']['user_home_url'].'">个人中心</a>看看':'取消了对你的关注';
                        $toContentHtmlLi.= '<li>
                                                <div class="list-item-l">
                                                    <cite><img src="'.$v['event_extra']['icon'].'"></cite>
                                                </div>
                                                <div class="list-item-r situatio-one">
                                                    <div class="item-r-name ">
                                                        <a target="_blank" href="'.$v['event_extra']['user_home_url'].'">'.$v['event_extra']['username'].'</a>'.$flag.'
                                                    </div>
                                                    <div class="item-r-other fn-clear">
                                                        <b class="time-stamp">'.$v['timestamp']['time'].'</b>
                                                    </div>
                                                </div>
                                             </li>';
                    }
                    $toContentHtmlLi .= '</ul>';
                    break;
                case 'echo-system-message':
                    $toContentHtmlLi .= '<ul class="sixin-list list-item ">';
                    foreach( $return['result']['data'] as $k=>$v ){
                        $toContentHtmlLi.= '<li>
                                                <div class="list-item-l">
                                                   <cite>
                                                      <img src="'.$v['event_extra']['icon'].'">
                                                   </cite>
                                                </div>
                                            <div class="list-item-r">
                                                <div class="item-r-name fn-clear">
                                                     <span class="fn-left">'.$v['event_extra']['username'].'</span>
                                                     <b class="time-stamp fn-right">'.$v['timestamp']['time'].'</b>
                                                </div>
                                                <div class="item-r-text">'.$v['event_extra']['content'].'</div>
                                            </div>
                                            </li>';
                    }
                    $toContentHtmlLi .= '</ul>';
                    break;
            }
        }else{
            $toContentHtmlLi = '<div class="no-data">
                                    <cite class="no-data-img"></cite>
                                    <p></p>
                                </div>';
        }
        return $toContentHtmlLi;
    }


    //获取分页结果
    public function getUserEvenNotification( $limit ,$skip = false ,$about_type = false){

        global $wgUser;

        $notificationMapper = new EchoNotificationMapper();
        $continue = $this->getRequest()->getVal( 'continue' );

        $attributeManager = EchoAttributeManager::newFromGlobalVars();
        $notifications = $notificationMapper->fetchByUser(
            $wgUser,
            $limit,
            $continue,
            $attributeManager->getUserEnabledEvents( $wgUser, 'web' ),
            array(),
            $skip,
            $about_type
        );
        return $notifications;
    }


    //获取右侧
    public function toRightSideHtml(){

        $morenum = '99';
        if($this->cite_my_count>0 && $this->cite_my_count<100){
            $cite_my_count = '<i>'.$this->cite_my_count.'</i>';
        }elseif($this->cite_my_count>99){
            $cite_my_count = '<i class="on">'.$morenum.'</i>';
        }else{
            $cite_my_count = '';
        }
        if($this->commect_count>0 && $this->commect_count<100){
            $commect_count = '<i>'.$this->commect_count.'</i>';
        }elseif($this->commect_count>99){
            $commect_count = '<i class="on">'.$morenum.'</i>';
        }else{
            $commect_count = '';
        }
        if($this->thumb_up_count>0 && $this->thumb_up_count<100){
            $thumb_up_count = '<i>'.$this->thumb_up_count.'</i>';
        }elseif($this->thumb_up_count>99){
            $thumb_up_count = '<i class="on">'.$morenum.'</i>';
        }else{
            $thumb_up_count = '';
        }
        if($this->consider_me_count>0 && $this->consider_me_count<100){
            $consider_me_count = '<i>'.$this->consider_me_count.'</i>';
        }elseif($this->consider_me_count>99){
            $consider_me_count = '<i class="on">'.$morenum.'</i>';
        }else{
            $consider_me_count = '';
        }
        if($this->message_count>0 && $this->message_count<100){
            $message_count = '<i>'.$this->message_count.'</i>';
        }elseif($this->message_count>99){
            $message_count = '<i class="on">'.$morenum.'</i>';
        }else{
            $message_count = '';
        }
        $toRightSideHtml = '<div class="user-messing">
                                <input type="hidden" value="'.$this->getPageTitle()->getLocalUrl('flag=1').'" id="local_url">
                                <input type="hidden" value="'.$this->about_type.'" id="even_type">
                                <a href="'.$this->getPageTitle()->getLocalUrl('about_type=article-cite-my').'" id="article-cite-my" class="ding ">@我的'.$cite_my_count.'</a>
                                <a href="'.$this->getPageTitle()->getLocalUrl('about_type=article-comments').'" id="article-comments" class="discuss">评论'.$commect_count.'</a>
                                <a href="'.$this->getPageTitle()->getLocalUrl('about_type=article-thumb-up').'" id="article-thumb-up" class="zan ">点赞'.$thumb_up_count.'</a>
                                <a href="'.$this->getPageTitle()->getLocalUrl('about_type=article-consider-me').'" id="article-consider-me" class="follow">关注'.$consider_me_count.'</a>
                                <a href="'.$this->getPageTitle()->getLocalUrl('about_type=echo-system-message').'" id="echo-system-message" class="notice">系统'.$message_count.'</a>
                             </div>';
        return $toRightSideHtml;
    }

    //右侧数据
    public function getRightSideGroupData(){

        $groupData = $this->getUserEvenNotification( false );

        if($this->about_type){
            $groupData[$this->about_type] = 0;
        }
        $this->cite_my_count = @$groupData["article-cite-my"]?@$groupData["article-cite-my"]:0;;
        $this->thumb_up_count = @$groupData["article-thumb-up"]?@$groupData["article-thumb-up"]:0;
        $this->consider_me_count = @$groupData["article-consider-me"]?@$groupData["article-consider-me"]:0;
        $this->message_count = @$groupData["echo-system-message"]?@$groupData["echo-system-message"]:0;
        $this->commect_count = @$groupData["article-comments"]?@$groupData["article-comments"]:0;
    }

    //获取总数
    public function count( ){

        global $wgUser;

        if(!$this->about_type){
            return false;
        }

        $dbw = wfGetDB( DB_MASTER );
        $result = $dbw->select(
            array( 'echo_notification', 'echo_event'),
            '*',
            array(
                'event_type'=>$this->about_type,
                'notification_user' =>$wgUser->mId
            ),
            __METHOD__,
            array(

            ),
            array(
                'echo_event' => array( 'LEFT JOIN', 'notification_event=event_id' ),
            )
        );
        return $result->numRows();
    }


    //所属分组
    public function belongToGroup(){

        $group = array(
            'article-thumb-up'=>'点赞',
            'article-comments'=> '评论',
            'article-cite-my'=> '@我的',
            'article-consider-me' => '关注',
            'echo-system-message' =>'系统'
        );
        if(!$this->about_type){
            return false;
        }
        return $group[$this->about_type];
    }


    //分页
    public function toPagingHtml( $return ){

        if($return['rs'] == 1){
            return $return['result']['page'];
        }
    }


    public function toReadData( $total , $wgUser){

        $notifications = $this->getUserEvenNotification( $total , false , $this->about_type);

        foreach ( $notifications as $notification ) {
            EchoDataOutputFormatter::formatOutput( $notification, 'json', $wgUser );
        }
    }

    //根据uid获取头像
    public function getUserHeadPortrait( $user_id ,$flag = false){

        if( !$user_id ){
            return '';
        }
        $model = new JoymeWikiUser();
        $res = $model->getProfile( $user_id );
        if( $flag ){
            return $res[0]['icon'];
        }
        return $res;
    }


    public  function getGroupData(){

        $groupData = $this->getUserEvenNotification( false );
        $array = array();
        if($_SERVER["QUERY_STRING"]){
            $params = explode('&',$_SERVER["QUERY_STRING"]);
            $end = end($params);
            if( $end ){
                $ends = explode('=',$end);
                if(isset($ends[0]) && $ends[0]== 'about_type'){
                    $groupData[$ends[1]] = 0;
                }
            }
        }
        $array["article-cite-my"] =  @$groupData["article-cite-my"]?@$groupData["article-cite-my"]:0;;
        $array["article-thumb-up"] = @$groupData["article-thumb-up"]?@$groupData["article-thumb-up"]:0;
        $array["article-consider-me"] = $this->consider_me_count = @$groupData["article-consider-me"]?@$groupData["article-consider-me"]:0;
        $array['echo-system-message'] = $this->message_count = @$groupData["echo-system-message"]?@$groupData["echo-system-message"]:0;
        $array['article-comments'] = $this->commect_count = @$groupData["article-comments"]?@$groupData["article-comments"]:0;

        if( $array["article-cite-my"] >0 || $array["article-thumb-up"]>0 || $array["article-consider-me"]>0 || $array['echo-system-message']>0 || $array['article-comments']>0){
            $array['is_new_remind'] = true;
        }

        return $array;
    }

}