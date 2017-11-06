<?php
/**
 * 百度哥伦布新模板处理程序
 *
 * @author 石鑫
 * @url http://wiki.joyme.com/
 */
if (!function_exists('version_compare') || version_compare(phpversion(), '5.3.2') < 0) {
    // We need to use dirname( __FILE__ ) here cause __DIR__ is PHP5.3+
    require dirname(__FILE__) . '/includes/PHPVersionError.php';
    wfPHPVersionError('index.php');
}
header("Content-type:text/html;charset=utf-8");

date_default_timezone_set('PRC');

$pars = $_SERVER['REQUEST_URI'];
$urlarr = explode('/' ,$pars);

$_SERVER['REQUEST_URI'] = '/'.$urlarr[2].'/';

require __DIR__ . '/includes/WebStart.php';

require __DIR__ . '/extensions/JoymeTemplate/glb/glb_templite.php';

require __DIR__ . '/extensions/JoymeTemplate/glb/glbClass.php';

if(isset($_SERVER["QUERY_STRING"])){


    $paras = explode('title=',$_SERVER["QUERY_STRING"]);
    if(count($paras)>=2){
        $pageTitle = htmlspecialchars($paras[1]);
        $GlbTemplate1 = new GlbTemplate1();
        $GlbTemplate1->execute(urldecode($pageTitle));
    }
}