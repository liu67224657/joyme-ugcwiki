<?php
/**
 * Created by JetBrains PhpStorm.
 * User: xinshi
 * Date: 15-5-8
 * Time: 下午6:40
 * To change this template use File | Settings | File Templates.
 */
class RecycleTemplate extends BaseTemplate{
static $url;
function __construct($wikiPostsInfo,$topdatapath,$linkdatapath,$url){

    if(!@$wikiPostsInfo['rs']){
        $this->PostsInfo = $wikiPostsInfo;
        $this->str_page = $wikiPostsInfo['page_str'];
    }else{
        $this->str_page = '';
    }
    self::$url = $url;
    $this->filepath = $topdatapath;
    $this->linkFile = $linkdatapath;
}
    function execute() {
?>
        <?php global $wgThread,$wgWikiStatic,$wgStaticUrl; if(!$wgThread):?>
            <div>你所访问的wiki讨论区暂未开放</div>
        <?php else:?>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <link href="<?php echo $wgStaticUrl?>/pc/wiki/discuss/css/discuss.css" rel="stylesheet" type="text/css">
        <title>回收站</title>
        <div id="wrapper">
            <!--title-->
            <div class="joyme-rmsy-tab-tit" id="tab">
                <p class="cur" id="tab_1">
                    <a href="<?php echo self::$url;?>">全部帖子</a>
                </p>
                <em>/</em>
                <p id="tab_2">
                    <a href="<?php echo self::$url;?>?type=1">精品帖子</a>
                </p>
            </div>
            <div class="discuss-title">
                <span>回收站</span>
                <input type="button" value="恢复" onclick="reduction()">
            </div>
            <!--title end-->
            <!--main-->
            <div class="discuss-main">
                <!--disuss left-->
                <div class="dis-left">
                    <input type="hidden" value="<?=self::$url?>" id="url">
                <?php if(!empty($this->PostsInfo)):?>
                <?php foreach($this->PostsInfo as $k=>$v):?>
                    <?php if(is_numeric($k)):?>

                    <div class="tab-cont-list">
                        <span><?=$v['comments_num']?></span>

                        <a href="<?php echo self::$url;?>?&details=<?=$v['wiki_title']?>&namespace=<?=$v['page_namespace']?>"><?=$v['wiki_title']?></a>
                        <?php if($v['is_essence'] == 1):?>
                            <i class="i-red">精品</i>
                        <?php else:?>
                            <i></i>
                        <?php endif;?>
                        <div class="joyme-author">
                            <?php if(ctype_alnum($v['user_name']) || is_numeric($v['user_name'])){
                                $length = 7;
                            }else{
                                $length = 7;
                            }?>
                            <?php $name = mb_substr($v['user_name'],0,$length,'utf-8');?>
                            <cite><?=$name?></cite>
                            <label>
                                <input type="checkbox" value="<?=$v['page_id'];?>" name="one"> 勾选
                            </label>
                        </div>
                    </div>
                    <?php endif;?>
                <?php endforeach;?>
                    <?php else:?>
                        暂无数据
                    <?php endif;?>

                </div>
                <!--disuss left end-->

                <div class="pager">
                    <?php echo $this->str_page;?>
                </div>
                <!--disuss rigth-->
                <div class="dis-right">
                    <div class="dis-right-box">
                        <?php @include_once($this->filepath)?>
                    </div>
                    <!--hot top  end-->
                    <!--link-->
                    <div class="dis-right-box">
                        <div class="hot-tit">友情讨论区
                        </div>
                        <div class="recommend-wiki">
                            <?php @include_once($this->linkFile)?>
                        </div>
                    </div>
                    <!--hot top  end-->
                </div>
                <!--disuss rigth end-->
            </div>
            <!--main end-->
        </div>
        <script type="text/javascript" src="<?php echo $wgStaticUrl?>/js/jquery-1.9.1.min.js"></script>
        <script src="<?php echo $wgWikiStatic;?>/extensions/Discussion/jsscript/wikiPosts.js"></script>
<?php endif;?>
                <?php
}
}
?>
