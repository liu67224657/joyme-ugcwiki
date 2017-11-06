<?php
class GlbTemplate1{

    public function execute( $pageTitle ) {

        global $wgWikiname,$wgEnv;

        $info = glbClass::getContent( $pageTitle );
        ?>
        <!DOCTYPE html>
        <html class="pixel-ratio-1" lang="zh-CN">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <title></title>
                <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
                <meta name="apple-mobile-web-app-capable" content="yes">
                <meta name="apple-mobile-web-app-status-bar-style" content="black">
                <meta name="format-detection" content="telephone=no">
                <meta name="keywords" content="">
                <meta name="description" content="">
                <link rel="stylesheet" href="http://static.joyme.com/mobile/baidu/css/bdtg.css">
                <script src="http://reswiki3.joyme.com/js/jquery-1.9.1.min.js" language="javascript"></script>
                <script src="http://static.joyme.com/mobile/baidu/js/gbl.js"></script>
        </head>
        <body>
        <div class="main content">
            <div class="main-content">
                <div class="news-title"><?php echo $info['body']['title']?></div>
                <div class="news-info">
                    <div class="news-time">更新时间：<?php echo $info['body']['update_time'];?></div>
                    <div class="news-from"> 来源：着迷网</div>
                </div>
                <div class="news-content">
                    <p style="text-align: center;">
                        <?php echo $info['body']['content']?>
                    </p>
                </div>
                <div class="news-footer">
<!--                    <div class="news-full">3555阅读</div>-->
                </div>
            </div>
            <div class="col4">
                <?php foreach($info['Advertise'] as $k=>$v){
                    ?>
                    <a target="_blank" href="<?php echo 'http://'.$v[3]?>" class="col_a"><img src="<?=$v[4]?>" class="col_img" width="110px;" height="82px;"><p class="col_tit"><?php echo $v[2]?></p></a>
                    <?php
                }?>
            </div>
            <div class="feed-list">
                <div class="feed-title">相关推荐</div>
                <ul class="item_ul">
                    <!--多张图-->
                    <?php foreach($info['recomm'] as $k=>$v){
                    ?>
                        <li class="lrpic">
                            <a href="<?php echo 'http://wiki.joyme.'.$wgEnv.'/bdhz/'.$wgWikiname.'/'. $v[2]?>">
                                <div class="item-media"><img src="<?php echo $v[5]?>" style="width: 149px;height: 104px;"></div>
                                <div class="item_tit">
                                    <div class="title"><?php echo $v[2]?></div>
                                    <div class="subtitle">着迷网&nbsp;&nbsp;<?php echo $v[4]?></div>
                                </div>
                            </a>
                        </li>
                    <?php
                    }?>
                </ul>
            </div>
        </div>
        </body>
        </html>
        <script>
            var _hmt = _hmt || [];
            (function() {
                var hm = document.createElement("script");
                hm.src = "https://hm.baidu.com/hm.js?1a79c2baaace62c5deadcdb6d55d557a";
                var s = document.getElementsByTagName("script")[0];
                s.parentNode.insertBefore(hm, s);
            })();
        </script>
        <script type="text/javascript">
            var _paq = _paq || [];
            _paq.push(['trackPageView']);
            _paq.push(['enableLinkTracking']);
            (function() {
                var u="//stat.joyme.com/";
                var n="//static.joyme.com/pc/piwik/";
                _paq.push(['setTrackerUrl', u+'piwik.php']);
                _paq.push(['setSiteId', 1501]);
                var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                g.type='text/javascript'; g.async=true; g.defer=true; g.src=n+'piwik.js'; s.parentNode.insertBefore(g,s);
            })();
        </script>
        <noscript><p><img src="//stat.joyme.com/piwik.php?idsite= " style="border:0;" alt="" /></p></noscript>
        <?php
    }
}