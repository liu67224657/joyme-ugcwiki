<?php
header('Content-Type: text/xml');
ob_start();
if (!function_exists('version_compare') || version_compare(phpversion(), '5.3.2') < 0) {
    // We need to use dirname( __FILE__ ) here cause __DIR__ is PHP5.3+
    require dirname(__FILE__) . '/includes/PHPVersionError.php';
    wfPHPVersionError('index.php');
}
require __DIR__ . '/includes/WebStart.php';

global $wgWikiname;

$host = 'http://' . $_SERVER['HTTP_HOST'] . '/';

$path = dirname(__FILE__) . '/cache/sitemap';

if (!file_exists($path)) {
    mkdir($path);
}

ob_clean();

if (isset($_SERVER["HTTP_WIKITYPE"])) {
    if($_SERVER["HTTP_WIKITYPE"]=='mwiki')
        $wikitype = 'm.';
    else
        $wikitype = '';        
} else {
    $wikitype = '';
}

$filename = $path . '/' . $wikitype . $wgWikiname . '.xml';

global $wgWikiname, $wgUGCWikis, $wgEnv;
//if (in_array($wgWikiname, $wgUGCWikis)) {
    $sitedomain = "http://" . $wikitype . "wiki.joyme." . $wgEnv . "/" . $wgWikiname;
//} else {
//    $sitedomain = "http://" . $wikitype . "$wgWikiname.joyme." . $wgWikiCom . "/wiki";
//}


//缓存文件 存在大于1天时需要重新生成文件 
if (file_exists($filename)) {
    $now = time();
    $mtime = @filemtime($filename);
    if (($now - $mtime) < 86400) {
        echo file_get_contents($filename);
        exit;
    }
}

$dom = new DomDocument('1.0', 'utf-8');
$dom->formatOutput = false;
//  创建根节点
$urlset = $dom->createElement('urlset');
$dom->appendchild($urlset);

$url = $dom->createElement('url');
$urlset->appendchild($url);

$loc = $dom->createElement('loc');
$url->appendchild($loc);
$text = $dom->createTextNode($sitedomain . "/");
$loc->appendChild($text);

$loc = $dom->createElement('priority');
$url->appendchild($loc);
$text = $dom->createTextNode('1.0');
$loc->appendChild($text);

$model = new DataSynchronization();
$pageinfo = $model->selectPageInfo();

foreach ($pageinfo as $k => $val) {

    $url = $dom->createElement('url');
    $urlset->appendchild($url);

    $loc = $dom->createElement('loc');
    $url->appendchild($loc);
    $text = $dom->createTextNode($sitedomain . '/' . $val->page_title);
    $loc->appendChild($text);

    $loc = $dom->createElement('priority');
    $url->appendchild($loc);
    $text = $dom->createTextNode('0.9');
    $loc->appendChild($text);
}
$dom->save($filename);
echo file_get_contents($filename);
?>