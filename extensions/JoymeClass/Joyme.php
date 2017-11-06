<?php

use Joyme\qiniu\Qiniu_RS_PutPolicy;
use Joyme\qiniu\Qiniu_PutExtra;
use Joyme\qiniu\Qiniu_ImageView;
use Joyme\qiniu\Qiniu_Utils;

class Joyme {
    //image source from qiniu
    static function imgFormQiNiu($url,$info=null) {

        global $wgQiNiuPath;
        $vals = explode('/', str_replace('//', '/', $url));

        if (in_array('thumb',$vals)) {
            if (in_array('archive',$vals)) {
            	$key1 = $vals[1].'/'.$vals[2].'/'.$vals[3].'/'.$vals[5].'/'.$vals[6].'/'.$vals[7].'/'.urldecode($vals[8]);
            	$size = intval($vals[9]);
            }else{
            	$key1 = $vals[1].'/'.$vals[2].'/'.$vals[3].'/'.$vals[5].'/'.$vals[6].'/'.urldecode($vals[7]);
            	$size = intval($vals[8]);
            }
            //生成图片预览 ,缩略图
            $baseUrl = Qiniu_Utils::Qiniu_RS_MakeBaseUrl($wgQiNiuPath, $key1);

            //生成fopUrl
            $imgView = new Qiniu_ImageView;
            $imgView->Mode = 1;
            $imgView->Width = $size;

            if(!empty($info)){
                $imgView->Height = ceil($size/$info->img_width*$info->img_height);
            }
            $imgViewUrl = $imgView->MakeRequest($baseUrl).'/v='.date('YmdHi');

        } elseif(substr($url,0,7) == 'http://') {
            $imgViewUrl = $url;
        }else{
            $imgViewUrl = 'http://' . $wgQiNiuPath .$url.'?v='.date('YmdHi');
        }
        return $imgViewUrl;
    }

    //upload for qiniu
    static function upload($operations) {

        global $wgQiNiuBucket,$wgWikiname;

        //Check whether there is a history
        $archivedst = explode("!",$operations[0]['dst']);
        $img_name = $archivedst[1];
        $dbr = wfGetDB( DB_MASTER );
        $conds = array( 'img_name' => $img_name );
        $info = $dbr->selectRow( 'image', array('img_name','img_size'), $conds, __METHOD__ );

        $key1 = str_replace('mwstore://local-backend/local-public', 'wiki/images/'.$wgWikiname, $operations[1]['dst']);
        $key = str_replace('mwstore://local-backend/local-public', 'wiki/images/'.$wgWikiname,$operations[0]['dst']);
        //If there are historical records
        if($info){
            //如果是恢复
            if($operations[1]['op']=="copy"){
                //1.把原图移动到$operations[0]['src']目录
                $oldimg = str_replace('mwstore://local-backend/local-public', 'wiki/images/'.$wgWikiname,$operations[0]['dst']);
                $newach = str_replace('mwstore://local-backend/local-public', 'wiki/images/'.$wgWikiname,$operations[0]['src']);

                Qiniu_Utils::Qiniu_MoveFile($wgQiNiuBucket, $newach, $wgQiNiuBucket,$oldimg );

                $operations1 = str_replace('mwstore://local-backend/local-public', 'wiki/images/'.$wgWikiname,$operations[1]['src']);

                Qiniu_Utils::Qiniu_CopyFile($wgQiNiuBucket, $operations1, $wgQiNiuBucket, $key1);
            }else{
                //如果有历史版本
                //1.把旧图移到新图位置
                //2.上传新图到当前位置
                Qiniu_Utils::Qiniu_MoveFile($wgQiNiuBucket, $key1, $wgQiNiuBucket, $key);
            }
            //删除图片缓存文件
            $path = './cache/'.$wgWikiname.'/imgname';
            $filename = $path.'/'.urlencode($img_name);
            if(file_exists($filename)){
                unlink($filename);
            }
        }
        if($operations[1]['op']!="copy"){
            //这里是上传新图
            list($ret, $err) = Qiniu_Utils::Qiniu_SaveFile($wgQiNiuBucket, $key1, $operations[1]['src']);
        }
        $status = Status::newGood();
        $status->successCount = 2;
        $status->ok = true;
        $status->failCount = 0;
        $status->errors=0;

        return $status;
    }

    //获取缩略图url
    function getImageThumbUrl($baseUrl,$width,$height){
        //生成fopUrl
        $imgView = new Qiniu_ImageView;
        $imgView->Mode = 1;
        $imgView->Width = $width;
        $imgView->Height = $height;
        $imgViewUrl = $imgView->MakeRequest($baseUrl);
        return $imgViewUrl;
    }
    /**
     * 删除七牛图片
     * @Author shixin
     * @return array
     */
    function  delImage($image_name,$achevname,$belogn){

        global $wgQiNiuBucket;
        //判断是否删除当前图片
        if($achevname){
            //获取文件的七牛路径
            $key = "wiki/images/".$belogn."/archive/".$this->jget_save_path($image_name).$achevname;
        }else{
            $key = "wiki/images/".$belogn."/".$this->jget_save_path($image_name).$image_name;
        }
        $err = Qiniu_Utils::Qiniu_DeleteFile($wgQiNiuBucket, $key);
        //如果删除
        if(empty($err)){
            if($achevname){
                $dbr = wfGetDB( DB_MASTER );
                $conds = array( 'oi_archive_name' => $achevname );
                $dbr->delete('oldimage', $conds, __METHOD__);
                $dbr->commit();
            }
            $result = array('rs'=>1,'msg'=>"success");
        }else{
            $result = array('rs'=>0,'msg'=>"fail");
        }
        return $result;
    }
    /**
     * 查询wiki信息
     * @Author zhangpeng
     * @return array
     */
    function wiki_info(){
    	 
    	$dbr = wfGetDB( DB_MASTER );
    	$res = $dbr->selectRow( 'site_stats', array('ss_total_views','ss_total_edits','ss_good_articles'), array(), __METHOD__);
    	$dbr->commit( __METHOD__ );
    	if ($res){
    		return array('total_views'=>$res->ss_total_views,'total_edits'=>$res->ss_total_edits,'total_articles'=>$res->ss_good_articles);
    	}else{
    		return '';
    	}
    }
    
    function jget_save_path($file_name,$lvl=2){
    	$name =$file_name;
    	$levels = $lvl;
    	if ( $levels == 0 ) {
    		return '';
    	} else {
    		$hash = md5( $name );
    		$path = '';
    		for ( $i = 1; $i <= $levels; $i++ ) {
    			$path .= substr( $hash, 0, $i ) . '/';
    		}
    		return $path;
    	}
    }
    /**
     * pagelist 最近修改记录
     * @Author zhangpeng
     * @return array
     */
    function pagelist(){
    	
    	//SELECT rc_timestamp,rc_user,rc_user_text,rc_namespace,rc_title 
    	//FROM `recentchanges` 
    	//WHERE rc_bot = '0' AND rc_namespace=0 AND rc_new IN('0','1')
    	$dbr = wfGetDB( DB_MASTER );
    	
    	$number = 30;
    	$page = empty($_GET['page'])?1:intval($_GET['page']);
    	$conds = array('rc_namespace'=>'0');
    	$res = $dbr->select( 'recentchanges', array('rc_timestamp','rc_user','rc_user_text','rc_title','rc_new'), $conds, __METHOD__ ,array(
                    'OFFSET' =>($page-1)*$number,
    				'ORDER BY'=>'rc_timestamp DESC',
                    'LIMIT'  =>$number,
                ));
    	$data = array();
    	while($row = $dbr->fetchRow( $res )){
    		$data[] = array(
    				'rc_timestamp'=>$row['rc_timestamp'],
    				'rc_user'=>$row['rc_user'],
    				'rc_user_text'=>$row['rc_user_text'],
    				'rc_title'=>$row['rc_title'],
    				'rc_new'=>$row['rc_new']
    				);
    	}
    	$res2 = $dbr->select( 'recentchanges', array('count(1) as num'), $conds, __METHOD__ );
    	$row2 = $dbr->fetchRow( $res2 );
    	$pages = array('pageSize'=>$number,'curPage'=>$page,'maxPage'=>ceil($row2['num']/$number));
    	return array('row'=>$data,'page'=>$pages);
    }
    
    /**
     * pagelist 图片列表
     * @Author zhangpeng
     * @return array
     */
    function piclist(){
    	 global $wgQiNiuPath,$wgWikiname;
    	//SELECT img_user_text,img_timestamp,img_name 
    	//FROM image ORDER BY img_timestamp DESC LIMIT 0,30;
    	$dbr = wfGetDB( DB_MASTER );
    	 
    	$number = 30;
    	$page = empty($_GET['page'])?1:intval($_GET['page']);
    	$conds = array();
    	$res = $dbr->select( 'image', array('img_user_text','img_timestamp','img_name'), $conds, __METHOD__ ,array(
    			'OFFSET' =>($page-1)*$number,
    			'ORDER BY' => 'img_timestamp DESC',
    			'LIMIT'  =>$number,
    	));
    	$data = array();
    	while($row = $dbr->fetchRow( $res )){
    		$img_src = 'http://'.$wgQiNiuPath.'/wiki/images/'.$wgWikiname.'/'.$this->jget_save_path($row['img_name']).$row['img_name'];
    		$data[] = array(
    				'img_user_text'=>$row['img_user_text'],
    				'img_timestamp'=>$row['img_timestamp'],
    				'img_name'=>$row['img_name'],
    				'img_src'=>$img_src
    		);
    	}
    	$res2 = $dbr->select( 'image', array('count(1) as num'), $conds, __METHOD__ );
    	$row2 = $dbr->fetchRow( $res2 );
    	$pages = array('pageSize'=>$number,'curPage'=>$page,'maxPage'=>ceil($row2['num']/$number));
    	return array('row'=>$data,'page'=>$pages);
    }
    
    /**
     * pagelist 图片调用page列表
     * @Author zhangpeng
     * @return array
     */
    function picinfo(){
    	global $wgQiNiuPath,$wgWikiname;
    	//SELECT  page_title  FROM `imagelinks`,`page`   WHERE il_to = '310.jpg' 
    	//AND page_namespace=0 AND (il_from = page_id)  ORDER BY il_from LIMIT 5
    	$dbr = wfGetDB( DB_MASTER );
    
    	$number = 5;
    	
    	$img_name = empty($_GET['img_name'])?'':addslashes($_GET['img_name']);
    	
    	if($img_name == ''){
    		return -1;
    	}
    	
    	$conds = array('il_to'=>$img_name,'page_namespace'=>0,'il_from = page_id');
    	$res = $dbr->select( array('imagelinks','page'), array('page_title'), $conds, __METHOD__ ,array(
    			'LIMIT'  =>$number
    	));
    	$data = array();
    	while($row = $dbr->fetchRow( $res )){
    		$data[] = array(
    				'page_title'=>$row['page_title'],
    		);
    	}
    	return array('row'=>$data);
    }

	function commentUpload(){

        global $wgQiNiuBucket,$wgQiNiuPath,$wgWikiname,$wgEnv;
		if(!empty($_FILES['commentImg'])){

            $key = 'wiki/images/'.$wgWikiname.'/'.$this->jget_save_path($_FILES['commentImg']['name']).'/'.$_FILES['commentImg']['name'];

            list($ret, $err) = Qiniu_Utils::Qiniu_SaveFile($wgQiNiuBucket , $key, $_FILES['commentImg']['tmp_name']);

			if($err !== null){
//				$msg = $err->Err ? $err->Err : '图片上传失败';
				$res = array("result"=>array("rs"=>"0", "msg"=>'图片上传失败'));
			}else{
                $http_url = 'http://'.$wgQiNiuPath.'/'.$ret['key'];
                $res = array('http_url'=>$http_url);
            }
			return "<script>document.domain='wiki.joyme.".$wgEnv."';parent.upImgCallback('".json_encode($res)."')</script>";
		}else{
			$res = array("result"=>array("rs"=>"0", "msg"=>"没有图片上传"));
			return "<script>document.domain='wiki.joyme.".$wgEnv."';parent.upImgCallback('".json_encode($res)."')</script>";
		}
	}
}
