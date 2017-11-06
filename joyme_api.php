<?php

/**
 * This is the main web entry point for MediaWiki.
 *
 * If you are reading this in your web browser, your server is probably
 * not configured correctly to run PHP applications!
 *
 * See the README, INSTALL, and UPGRADE files for basic setup instructions
 * and pointers to the online documentation.
 *
 * https://www.mediawiki.org/
 *
 * ----------
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */
# Bail on old versions of PHP.  Pretty much every other file in the codebase
# has structures (try/catch, foo()->bar(), etc etc) which throw parse errors in
# PHP 4. Setup.php and ObjectCache.php have structures invalid in PHP 5.0 and
# 5.1, respectively.

if (!function_exists('version_compare') || version_compare(phpversion(), '5.3.2') < 0) {
    // We need to use dirname( __FILE__ ) here cause __DIR__ is PHP5.3+
    require dirname(__FILE__) . '/includes/PHPVersionError.php';
    wfPHPVersionError('index.php');
}
//header("Content-type:text/html;charset=utf-8");

require __DIR__ . '/includes/WebStart.php';
require_once('./extensions/JoymePlugin/FileUpload.class.php');

use Joyme\core\Request;

//$action = empty($_GET['action']) ? '' : $_GET['action'];
//$vals = empty($_GET['vals'])?'':$_GET['vals'];
//$len = empty($_GET['len'])?'':$_GET['len'];

$action = Request::getParam('action');
$vals = Request::get('vals');
$wiki = Request::get('wiki');
$len = Request::get('len');
$time = Request::get('time');

if($action == 'userwikiinfo'){
	header("Content-type:application/javascript;charset=utf-8");
}else{
	header("Content-type:text/html;charset=utf-8");
}


//默认输出数组
$data = array(
	'rs' => '',
	'msg' => '',
	'result' => '',
);

if( $action == 'sysdata' && isset($wiki) ){
	$model = new SynchronousData();
	$model->index( $wiki ,$time);
	exit();
}

$joyme = new Joyme();
if($action == 'info'){
	$data = $joyme->wiki_info();
	if($data){
		$data = array('result'=>$data,'msg'=>'success','rs'=>'1');
	}else{
		$data = array('result'=>'暂无数据','msg'=>'false','rs'=>'-1');
	}
}elseif ($action == 'pagelist'){
	$data = $joyme->pagelist();
	$data = array('result'=>$data,'msg'=>'success','rs'=>'1');
}elseif ($action == 'piclist'){
	$data = $joyme->piclist();
	$data = array('result'=>$data,'msg'=>'success','rs'=>'1');
}elseif($action == 'picinfo'){
	$data = $joyme->picinfo();
	if($data == -1){
		$data = array('result'=>'img name is null','msg'=>'false','rs'=>'-1');
	}else{
		$data = array('result'=>$data,'msg'=>'success','rs'=>'1');
	}
}elseif($action == 'commentUpload'){
	$data = $joyme->commentUpload();
	echo $data;exit;
}elseif($action == 'actions'){
	$user = RequestContext::getMain()->getUser();
	// var_dump($user);
	exit;
}elseif($action == 'InterestTesting') {
	$model = new InterestTesting();
	$resul = $model->setTestingValue($vals, $len);
	if ($resul !== false && $resul >= 0) {
		$data = array('result' => $resul, 'msg' => '', 'rs' => '1');
	} else {
		$data = array('result' => 'error', 'msg' => '', 'rs' => '-1');
	}
}
elseif ($action == 'userwikiinfo'){

	$callback = Request::getParam('callback');
	$wtype = Request::getParam('wtype');
	$userid = Request::getParam('userid');
	$page = Request::getParam('page');
	if(empty($wtype)){
		$data = JoymeSite::UserWikiInfo($userid);
	}else{
		$data = JoymeSite::ajaxUserWikiInfo($wtype,$userid,$page);
	}
	
	if(empty($callback)){
		echo json_encode($data);
	}else{
		echo $callback . '(' . json_encode($data) . ')';  //返回格式，必需
	}

	
	exit();
}
elseif ($action == 'opcachereset'){
	$ret = opcache_reset();
	if($ret){
		echo "succeed";
	}else{
		echo "failed";
	}
	exit();
}
else{
	$data = array('result'=>'no action','msg'=>'','rs'=>'-1');
}
echo json_encode($data);



