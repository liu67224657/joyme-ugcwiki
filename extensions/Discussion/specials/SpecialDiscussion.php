<?php
use Joyme\core\Request;
class SpecialDiscussion extends SpecialPage{

    static $url;
    static $name;
    static $content_length = 18;
    static $width = 74;
    static $height = 74;
    static $details;
    static $pb_page;
    static $type;
    static $recycle;
    static $operation;
    static $jumpurl;
    static $top_file_name = 'wiki_posts_top_data.html';
    static $link_file_name = 'wiki_posts_Link_data.html';

    public function __construct(){
        parent::__construct('Discussion', 'discussion');
    }

    protected function getGroupName() {

        return 'pages';
    }

    public function execute($par) {

        if(isMobile()){
            $this->getOutput()->addHTML(
                '<span class="view-status">' .
                $this->msg( 'systemmessage_facility_error' )->plain() .
                '</span><br /><br />'
            );
            $this->getOutput()->addReturnTo( SpecialPage::getTitleFor( 'Specialpages' ) );
        }else{
            $this->setHeaders();
            $details = Request::getParam('details');
            $pb_page = Request::getParam('pb_page',1);
            $type = Request::getParam('type');
            $recycle = Request::getParam('recycle');
            $operation = Request::getParam('operation');
            $flag = Request::getParam('del');
            $callback = Request::getParam('callback');
            $this->init($details,$pb_page,$type,$recycle,$operation);
            if(!empty($details)){
                $this->details();
            }elseif(!empty($recycle)){
                $this->decovery();
            }else{
                if($flag==1){
                    //删除
                    $this->operationDelete($callback);
                }elseif($flag==2){
                    //加精
                    $this->operationEssence($callback);
                }elseif($flag==3){
                    //置顶
                    $this->operationTop($callback);
                }elseif($flag==4){
                    //设置友情链接
                    $this->setUpFriendshipLinks($callback);
                }elseif($flag==5){
                    //删除友情链接
                    $this->delFriendshipLinks($callback);
                }elseif($flag==6){
                    $this->updateIsDelete($callback);
                }else{
                    $topdatapath = $this->checkCacheFileExists(self::$top_file_name);
                    $linkdatapath = $this->checkCacheFileExists(self::$link_file_name,true,true);
                    $wikiPostsInfo = $this->getWikiPostsInfo();
                    $template = new DiscussionListTemplate($wikiPostsInfo,$topdatapath,$linkdatapath,self::$jumpurl);
                    $this->getOutput()->addTemplate($template);
                }
            }
        }
    }

    public function init($details,$pb_page,$type,$recycle,$operation){

        self::$details = $details;
        self::$pb_page = $pb_page;
        self::$type = $type;
        self::$recycle = $recycle;
        self::$operation = $operation;
        self::$name = SpecialPage::getTitleFor( 'discussion' )->mTextform;
        if(!empty($operation)){
            self::$url = $this->getPageTitle()->getFullURL().'?operation=management';
        }else{
            self::$url = $this->getPageTitle()->getFullURL();
        }
        self::$jumpurl = $this->getPageTitle()->getFullURL();
    }

    public function decovery(){

        $wikiPostsInfo = $this->getWikiDelPostsInfo(self::$pb_page,self::$recycle);
        $topdatapath = $this->checkCacheFileExists(self::$top_file_name);
        $linkdatapath = $this->checkCacheFileExists(self::$link_file_name,true,true);
        $template = new RecycleTemplate($wikiPostsInfo,$topdatapath,$linkdatapath,self::$jumpurl);
        $this->getOutput()->addTemplate($template);
    }

    public function details(){

        global $wgRequestInterfaceUrl, $wgWikiname;
        $request = $this->getRequest();
        $title = $request->getVal( 'details' );
        $namespace = $request->getVal( 'namespace' );
        $namespace = $namespace ? $namespace : 0;
        $wgtitle = $namespace == 1000 ? 'THREAD:'.$title : $title;
        $articleStr = file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/api.php?action=parse&format=json&page='.$wgtitle);
        $article = json_decode($articleStr, true);
        if($namespace == 1000){
            $tmp = @explode(':', $article['parse']['title']);
            $title = array_pop($tmp);
            if(empty($title)){
                $title = $request->getVal( 'details' );
            }
        }else{
            $title = $article['parse']['title'];
        }
        $title = trim($title);
        $token = DataSynchronization::Protection($wgWikiname);
        $url = $wgRequestInterfaceUrl.'?c=wikiPosts&a=selectWikiPostsDetail&wikikey='.$wgWikiname.'&wiki_title='.urlencode($title).'&token='.$token.'&namespace='.$namespace;

        $author = $this->senRequest($url);
        if($author && is_array($author) && $author['rs'] == 0){
            $article = array_merge($article, $author['result']);
            $article['error'] = 0;
        }else{
            $article['error'] = 1;
        }
        $article['mytoken'] = DataSynchronization::Protection(@$article['page_id']);
        $article['power'] = $this->userPermissions();
        $article['namespace'] = $namespace;
        $topdatapath = $this->checkCacheFileExists(self::$top_file_name);
        $linkdatapath = $this->checkCacheFileExists(self::$link_file_name,true,true);
        $template = new DetailTemplate($article,$topdatapath,$linkdatapath,self::$jumpurl);
        $this->getOutput()->addTemplate($template);
    }


    public function getWikiPostsInfo(){

        global $wgRequestInterfaceUrl,$wgWikiname;
        $token = DataSynchronization::Protection($wgWikiname);
        $url = $wgRequestInterfaceUrl."?c=wikiPosts&a=selectWikiPostsInfo&wikikey=".$wgWikiname."&token=".$token."&pb_page=".self::$pb_page."&pburl=".urlencode(self::$url)."&reco=".self::$recycle."&type=".self::$type.'&parame='.self::$operation;
        return $this->senRequest($url);
    }

    function  getWikiDelPostsInfo($pb_page,$reco=false){

        global $wgRequestInterfaceUrl,$wgWikiname;
        $pburl = self::$url."?&recycle=$reco";
        $pburl = urlencode($pburl);
        $flag = self::$pb_page==1?false:1;
        $token = DataSynchronization::Protection($wgWikiname);
        $url = $wgRequestInterfaceUrl."?c=wikiPosts&a=selectWikiPostsInfo&wikikey=".$wgWikiname."&token=".$token."&pb_page=".$pb_page."&pburl=".$pburl."&reco=".$reco.'&parame='.$flag;
        return $this->senRequest($url);
    }

    function checkCacheFileExists($filename,$flag=false,$del=false){

        global $wgPostsCachePath,$wgHotDataCacheExpiredTime;
        $filepath = $wgPostsCachePath.'/'.urlencode($filename);
        if($flag){
            if($del){
                unlink($filepath);
                $this->checkCacheFileExists(self::$link_file_name,true);
            }else{
                if(file_exists($filepath)){
                    if((time()-filemtime($filepath) > $wgHotDataCacheExpiredTime)){
                        unlink($filepath);
                        $this->checkCacheFileExists(self::$link_file_name,true);
                    }
                }else{
                    if($this->mk_dir($wgPostsCachePath,$filepath)){
                        if(fopen($filepath,"w+")){
                            $this->writeLinkInfoToFile($filepath);
                        }
                    }
                }
            }
        }else{
            if(file_exists($filepath)){
                if((time()-filemtime($filepath) > $wgHotDataCacheExpiredTime)){
                    unlink($filepath);
                    $this->checkCacheFileExists(self::$top_file_name);
                }
            }else{
                if($this->mk_dir($wgPostsCachePath,$filepath)){
                    if(fopen($filepath,"w+")){
                        $this->writetopInfoToFile($filepath);
                    }
                }
            }
        }
        return $filepath;
    }

    public function writetopInfoToFile($filepath){

        $result = $this->getTopData();
        if($result['rs'] == 0){
            $html = "<div class='hot-tit'>本周热帖 top5</div><div class='hot-cont'>";
            foreach($result['result'] as $k=>$v){
                $namespace = $v['page_namespace'];
                if(strlen($v['wiki_title'])>63){
                    $title = mb_substr($v['wiki_title'],0,18,'utf-8');
                    $html.='<a href="'.self::$jumpurl.'?details='.$v['wiki_title'].'&namespace='.$namespace.'" target="_blank">'.$title.'...</a>';
                }else{
                    $html.='<a href="'.self::$jumpurl.'?details='.$v['wiki_title'].'&namespace='.$namespace.'" target="_blank">'.$v['wiki_title'].'</a>';
                }
            }
            $html.="</div>";
        }else{
            $html = "<div class='hot-tit'>本周热帖 top5</div><div class='hot-cont'>当前暂无热帖</div>";
        }
        file_put_contents($filepath,$html);
    }

    public function writeLinkInfoToFile($filepath){

        $result = $this->getLinkData();
        if($result['rs'] == 0){
            if(!empty($result['result'])){
                $html = "<ul>";
                foreach($result['result'] as $k=>$v){
                    $thubImage = DataSynchronization::imagePath($v['image'],self::$width,self::$height,true);
                    $html.='<li><a target="_blank" href="/'.$v['wiki_key'].'/特殊:'.self::$name.'"><img width=64px; heigth=64px; src="'.$thubImage.'"/><span id="linkF">'.$v['wiki_name'].'</span> <input type="hidden" value="'.$v['id'].'"></a></li>';
                }
                $html.="</ul>";
                file_put_contents($filepath,$html);
            }
        }
    }

    public function getLinkData(){

        global $wgRequestInterfaceUrl,$wgWikiname;
        $token = DataSynchronization::Protection($wgWikiname);
        $url = $wgRequestInterfaceUrl."?c=wikiPosts&a=setUpFriendshipLinks&wikikey=".$wgWikiname."&token=".$token."&id=&type=2&belong_wiki=".$wgWikiname;
        return $this->senRequest($url);
    }

    public function getTopData(){

        global $wgRequestInterfaceUrl,$wgWikiname;
        $token = DataSynchronization::Protection($wgWikiname);
        $url = $wgRequestInterfaceUrl."?c=wikiPosts&a=selectHotPostsInfo&wikikey=".$wgWikiname."&token=".$token;
        return $this->senRequest($url);
    }

    function mk_dir($dir, $mode = 0777)
    {
        if (is_dir($dir) || mkdir($dir,0777)) return true;
        if (!$this->mk_dir(dirname($dir),0777)) return false;
        return mkdir($dir,$mode);
    }

    //删除友情链接
    function delFriendshipLinks($callback){

        global $wgRequestInterfaceUrl,$wgWikiname;
        $id = Request::getParam('id');
        if(!empty($id)){
            if(!empty($this->userPermissions())){
                $token = DataSynchronization::Protection($wgWikiname);
                $url = $wgRequestInterfaceUrl."?c=wikiPosts&a=setUpFriendshipLinks&id=".$id."&token=".$token."&belong_wiki=".$wgWikiname."&type=3&wikikey=".$wgWikiname;
                $result = $this->senRequest($url);
                if($result['rs']==0){
                    if($this->checkCacheFileExists(self::$link_file_name,true,true)){
                        $result = 1;
                    }else{
                        $result = 2;
                    }
                }else{
                    $result = 2;
                }
                echo $callback . "([" . json_encode($result) . "])";
                exit;
            }
        }
    }

    //设置友情连接
    function setUpFriendshipLinks($callback){

        global $wgRequestInterfaceUrl,$wgWikiname,$wgUser,$wgStaticUrl;
        //进行图片上传
        $wikiname = Request::getParam('text_wiki');
        $text_name = Request::getParam('text_name');
        $filename = Request::getParam('filename');
        if(empty($filename)){
            $filename = $wgStaticUrl.'/pc/wiki/discuss/images/wiki.jpg';
        }
        if(!empty($this->userPermissions())){
            $token = DataSynchronization::Protection($wikiname);
            $url = $wgRequestInterfaceUrl."?c=wikiPosts&a=setUpFriendshipLinks&wikikey=".$wikiname."&token=".$token."&wikiimage=".$filename."&wikiname=".$text_name."&id=&type=1&operator=".$wgUser->mName."&belong_wiki=".$wgWikiname;
            $result = $this->senRequest($url);
            if($result['rs']==0){
                if($this->checkCacheFileExists(self::$link_file_name,true,true)){
                    $result = 1;
                }else{
                    $result = 2;
                }
            }else{
                $result = 2;
            }
            echo $callback . "([" . json_encode($result) . "])";
            exit;
        }
    }


    //回收站的恢复功能
    function updateIsDelete($callback){
        global $wgRequestInterfaceUrl,$wgWikiname;
        $id = Request::getParam('id');
        $str = '';
        if(!empty($id)){
            foreach($id as $v){
                $str .= $v."-";
            }
            $arr = explode("-",$str);
            if(!empty($this->userPermissions())){
                $token = DataSynchronization::Protection($arr[0]);
                $url = $wgRequestInterfaceUrl."?c=wikiPosts&a=updatePostsDelete&id=".$str."&token=".$token."&page_id=".$arr[0]."&wikikey=".$wgWikiname."&type=1";
                $result = $this->senRequest($url);
                if($result['rs'] == 0){
                    $result = 1;
                }else{
                    $result = 2;
                }
                echo $callback . "([" . json_encode($result) . "])";
                exit;
            }
        }
    }


    //管理页的置顶操作
    function operationTop($callback){
        global $wgRequestInterfaceUrl,$wgWikiname;
        $id = Request::getParam('id');
        $str = '';
        if(!empty($id)){
            $id = array_unique($id);
            foreach($id as $v){
                $str .= $v."-";
            }
            $arr = explode("-",$str);
            if(!empty($this->userPermissions())){
                $token = DataSynchronization::Protection($arr[0]);
                $url = $wgRequestInterfaceUrl."?c=wikiPosts&a=updateIsTop&page_id=".$arr[0]."&id=".$str."&token=".$token."&wikikey=".$wgWikiname."&type=1";

                $result = $this->senRequest($url);
                if($result['rs'] == 0){
                    $result = 1;
                }else{
                    $result = 2;
                }
                echo $callback . "([" . json_encode($result) . "])";
                exit;
            }
        }
    }

    //管理页的加精操作
    function operationEssence($callback){

        global $wgRequestInterfaceUrl,$wgWikiname;
        $id = Request::getParam('id');
        $str = '';
        if(!empty($id)){
            foreach($id as $v){
                $str .= $v."-";
            }
            $arr = explode("-",$str);
            if(!empty($this->userPermissions())){
                $token = DataSynchronization::Protection($arr[0]);
                $url = $wgRequestInterfaceUrl."?c=wikiPosts&a=updateIsEssence&page_id=".$arr[0]."&id=".$str."&token=".$token."&wikikey=".$wgWikiname;
                $result = $this->senRequest($url);
                if($result['rs'] == 0){
                    $result = 1;
                }else{
                    $result =2;
                }
                echo $callback . "([" . json_encode($result) . "])";
                exit;
            }
        }
    }

    //帖子管理页的删除功能
    function operationDelete($callback){

        global $wgRequestInterfaceUrl,$wgWikiname;
        $id = Request::getParam('id');
        $str = '';
        if(!empty($id)){
            foreach($id as $v){
                $str .= $v."-";
            }
            $arr = explode("-",$str);
            if(!empty($this->userPermissions())){
                $token = DataSynchronization::Protection($arr[0]);
                $url = $wgRequestInterfaceUrl."?c=wikiPosts&a=updatePostsDelete&id=".$str."&page_id=".$arr[0]."&token=".$token."&wikikey=".$wgWikiname;
                $result = $this->senRequest($url);
                if($result['rs'] == 0){
                    $result = 1;
                }else{
                    $result =2;
                }
                echo $callback . "([" . json_encode($result) . "])";
                exit;
            }
        }
    }

    function senRequest($url){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = trim(curl_exec($ch));
        curl_close($ch);
        return json_decode($res,true);
    }

    function userPermissions(){

        global $wgUser;
        if (!$wgUser->isAllowed( 'discussion' ) ) {
            return  false;
        }
        if(self::$operation == 'management'){
            $str =  'management';
        }else{
            $str =  'handle';
        }
        return $str;
    }
}