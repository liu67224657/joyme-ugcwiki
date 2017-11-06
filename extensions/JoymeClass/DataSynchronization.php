<?php
/**
 * Created by JetBrains PhpStorm.
 * User: xinshi
 * Date: 15-4-10
 * Time: 下午2:31
 * To change this template use File | Settings | File Templates.
 */
class DataSynchronization{
	public $namespaceArr = array(0,1000); // 0普通文章页， 1000讨论区帖子

    public $Table_Name = 'category';

    public $Show_Number = 4;
    //添加词条数据
    static function addWordsData($data,$user){

        if($data->mTitle->mNamespace !=6){

            $page_id = $data->mTitle->mArticleID;
            $domain = explode(".",$_SERVER['HTTP_HOST']);
            $dbr = wfGetDB( DB_MASTER );
            $conds = array( 'rev_page' => $page_id );
            $revisioninfo = $dbr->select( 'revision', array('rev_id'), $conds , __METHOD__ ,array('LIMIT'  =>2));

            if($revisioninfo->numRows() == 2){
                $newdata['rev_is_new'] = 0;
            }else{
                $newdata['rev_is_new'] = 1;
            }
            $last_id = $data->mLatest;

            $conds = array( 'rev_id' => $last_id );
            $revision = $dbr->selectRow( 'revision', array('rev_text_id','rev_comment','rev_minor_edit','rev_deleted','rev_parent_id','rev_sha1'), $conds , __METHOD__ );

            $newdata['rev_text_id'] = $revision->rev_text_id;
            $newdata['rev_comment'] = $revision->rev_comment;
            $newdata['rev_minor_edit'] = $revision->rev_minor_edit;
            $newdata['rev_parent_id'] = $revision->rev_parent_id;
            $newdata['rev_sha1'] = $revision->rev_sha1;
            $newdata['rev_deleted'] = $revision->rev_deleted;

            $newdata['rev_len'] = 0;
            $newdata['rev_user'] = $user->mId;
            $newdata['rev_user_text'] = $user->mName;
            $newdata['rev_page']  = $page_id;
            $newdata['rev_id'] = $last_id;
            $newdata['rev_timestamp'] = time();
            $newdata['rev_page_namespace'] = $data->mTitle->mNamespace;
            $newdata['rev_belong'] = $domain[0];
            $newdata['rev_title'] = urlencode($data->mTitle->mTextform);
            $newdata['rev_status'] = 'N';
            $newdata['rev_operator'] = '';
            $newdata['operation_time'] = 0;

            $model = new DataSynchronization();

            $str = $model->Protection($newdata['rev_title']);
            $newdata['token'] = $str;

            $model = new DataSynchronization();
            $url = "joymewiki.joyme.".$domain[2]."?c=data_api&a=verision";

            $model->getRequest($url,$newdata);
        }
    }

    //添加图片数据
    static function addImagesData($filename,$archivename,$uid,$uname,$time){

        $archivename = str_replace("'",'',$archivename);
        $newdata = array();
        $domain = explode(".",$_SERVER['HTTP_HOST']);
        $newdata['rev_user'] = $uid;
        $newdata['rev_user_text'] = $uname;
        $newdata['belong'] = $domain[0];
        $newdata['image_name'] = $filename;
        $newdata['archive_name'] = $archivename;
        $time = str_split($time,'2');
        $newtime = strtotime($time[0].$time[1].'-'.$time[2].'-'.$time[3]." ".$time[4].":".$time[5].":".$time[6]);
        $newdata['create_time'] = $newtime;
        $newdata['status'] = 'N';
        $newdata['operation_time'] = '';
        $newdata['operator'] = '';

        $model = new DataSynchronization();
        $str = $model->Protection($newdata['image_name']);
        $newdata['token'] = $str;

        $model = new DataSynchronization();
        $url = "joymewiki.joyme.".$domain[2]."?c=data_api&a=image";
        $model->getRequest($url,$newdata);
    }
	
	static function addPostsData($articleData,$user){
		global $wgWikiname, $wgQiNiuPath, $wgRequest, $com;
		$DataSynchronization = new DataSynchronization();
		$pageNamespace = $articleData->mTitle->mNamespace;
		if(!in_array($pageNamespace, $DataSynchronization->namespaceArr)){
			return true;
		}
		$data = array();
		$data['page_namespace']	= $articleData->mTitle->mNamespace;
		$data['wiki_key']		= $wgWikiname;
		$data['wiki_title']		= urlencode($articleData->mTitle->mTextform);
		$data['create_time']	= time();
		$data['page_namespace']	= $articleData->mTitle->mNamespace;
		$data['user_id']		= $user->mId;
		$data['user_name']		= $user->mName;
		$imgNames = $articleData->mPreparedEdit->output->mImages;
		$imgUrls = array();
		$Joyme = new Joyme();
		foreach($imgNames as $key=>$val){
			$size = $DataSynchronization->getImgInfo($key);
			$width = isset($size->img_width) ? $size->img_width : '';
			$height = isset($size->img_height) ? $size->img_height : '';
			if($width != '' && $height != ''){
				if(($height/$width)>2 || ($height/$width)<0.5){
					continue;
				}else{
					$imgs[] = $key;
				}
			}else{
				$imgs[] = $key;
			}

			if(count($imgs)==3){
				break;
			}
		}
		if(!empty($imgs)){
			$data['descrip_image'] = count($imgs)>1 ? implode(',', $imgs) : $imgs[0];
		}else{
			$data['descrip_image'] = '';
		}
		
		$text = preg_replace('/\s/', '', strip_tags($articleData->mPreparedEdit->output->mText));
		$desc = mb_substr($text, 0, 25, 'utf-8');
		$data['descrip_page'] = mb_strlen($text, 'utf-8')>25 ? $desc.'...' : $desc;
		$model = new DataSynchronization();
        $str = $model->Protection($data['wiki_key'].$data['wiki_title']);
        $data['token'] = $str;
		$url = 'http://joymewiki.joyme.'.$com.'/?c=data_api&a=posts';
		$res = $model->getRequest($url, $data);
	}
	
	public function getImgInfo( $img_name = '' ){
		global $wgImageCachePath;
		if($img_name == '') return false;
		$filename = $wgImageCachePath.'/'.urlencode($img_name);
		if(file_exists($filename)){
			$info = json_decode(file_get_contents($filename));
		}else{
			$dbr = wfGetDB( DB_MASTER );
			$conds = array( 'img_name' => $img_name );
			$info = $dbr->selectRow( 'image', array('img_width','img_height'), $conds, __METHOD__ );
			if(!file_exists($wgImageCachePath)){
				mkdir($wgImageCachePath);
			}
			if($info){
				@file_put_contents($filename, json_encode($info));
			}
		}
		
		return $info;
	}

    //获取图片路径
    static function imagePath($imageName,$width,$height,$flag=false){
        global $wgWikiname, $wgQiNiuPath;
        $Joyme = new Joyme();
        if(!$flag){
            $url = 'http://'.$wgQiNiuPath.'/wiki/images/'.$wgWikiname.'/'.$Joyme->jget_save_path($imageName).$imageName;
        }else{
            $url = $imageName;
        }
        $thubUrl = $Joyme->getImageThumbUrl($url,$width,$height);
        return $thubUrl."/".date('Ymd');  //讨论区图片业务需求，图片按天刷新
    }


    static function getImagePath( $imageName ){

        global $wgWikiname, $wgQiNiuPath;
        if( empty($imageName)){
            return false;
        }
        $Joyme = new Joyme();
        return $url = 'http://'.$wgQiNiuPath.'/wiki/images/'.$wgWikiname.'/'.$Joyme->jget_save_path($imageName).$imageName;
    }

    //UGC wiki 地图BUG
    function selectPageInfo(){

        $dbr = wfGetDB( DB_MASTER );
        $conds = array( 'page_namespace' => 0);
        $pageinfo = $dbr->select( 'page', array('page_title'), $conds , __METHOD__ ,array('LIMIT'  =>10000));
        return $pageinfo;
    }

    //标签库
    public function tagLibrary(){

        $lableContent = $this->selectLableAllInfo();
        //如果标签不为空
        if($lableContent->result->num_rows!=0){
            $lableHtml = '<table><tr>';
            foreach($lableContent as $k=>$v){
                if($k%$this->Show_Number==0){
                    $lableHtml.='<td style="cursor:pointer;" code="'.$v->cat_title.'" class="lable-'.$v->cat_title.'">'.$v->cat_title.'</td></tr><tr>';
                }else{
                    $lableHtml.='<td style="cursor:pointer;" code="'.$v->cat_title.'" class="lable-'.$v->cat_title.'">'.$v->cat_title.'</td>';
                }
            }
            $lableHtml.='</tr></table>';
        }else{
            $lableHtml = '暂无标签内容';
        }
        return $lableHtml;
    }

    //查询全部
    public function selectLableAllInfo(){

        $dbr = wfGetDB( DB_MASTER );
        $conds = array();
        $filds = array(
            'cat_id',
            'cat_title'
        );
        return $dbr->select( $this->Table_Name, $filds, $conds , __METHOD__ ,array('LIMIT'  =>200));
    }

    //发送请求
    function getRequest($url,$data){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $res = trim(curl_exec($ch));
        curl_close($ch);
    }

    //加密验证
    static function Protection($str){
        //约定秘钥
        $key = "zm^!-tb";
        $token = md5(md5($str.$key));
        return $token;
    }

    /**
     * 字符串截取，支持中文和其他编码
     * @static
     * @access public
     * @param string $str 需要转换的字符串
     * @param string $start 开始位置
     * @param string $length 截取长度
     * @param string $charset 编码格式
     * @param string $suffix 截断显示字符
     * @return string
     */
    function msubstr($str, $start, $length, $charset="utf-8", $suffix="") {
        $str =  trim($str);
        if ($start<0) {
            $len = mb_strlen($str,$charset);
            $start = $len+$start;
            if (empty($length))
            {
                $length = abs($start);
            }
        }

        if(function_exists("mb_substr")){
            $slice = mb_substr($str, $start, $length, $charset);
        }
        elseif(function_exists('iconv_substr')) {
            $slice = iconv_substr($str,$start,$length,$charset);
            if(false === $slice) {
                $slice = '';
            }
        }else{
            $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("",array_slice($match[0], $start, $length));
        }
        return $suffix ? $slice.$suffix : $slice;
    }
}