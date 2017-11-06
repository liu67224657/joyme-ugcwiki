<?php
class DiscussionListTemplate extends BaseTemplate {

    static $url;

    function __construct($wikiPostsInfo,$topdatapath,$linkdatapath,$jumpurl){

        if(!@$wikiPostsInfo['rs']){
            $this->PostsInfo = $wikiPostsInfo;
            $this->str_page = $wikiPostsInfo['page_str'];
        }else{
            $this->str_page = '';
        }
        $this->filepath = $topdatapath;
        $this->linkFile = $linkdatapath;
        self::$url = $jumpurl;
        $model = new SpecialDiscussion();
        $this->operation = $model->userPermissions();
        $this->width = 100;
        $this->height = 100;
    }

    function execute() {

    ?>
    <?php global $wgThread,$wgServer,$wgWikiStatic,$wgStaticUrl,$wgWikiname; if(!$wgThread):?>
        <div>你所访问的wiki讨论区暂未开放</div>
    <?php else:?>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" xmlns="http://www.w3.org/1999/html"/>
        <link href="<?php echo $wgStaticUrl?>/pc/wiki/discuss/css/discuss.css" rel="stylesheet" type="text/css">
        <div id="wrapper">
            <!--title-->

        <?php if($this->operation == 'management'):?>
            <div class="discuss-title">
            <a href="<?php echo self::$url?>?recycle=recovery"><input type="button" value="回收站"></a>
            <input type="button" value="删除" onclick="Operationdel(1)"/>
            <input type="button" value="加精" onclick="Operationdel(2)" id="jiajing"/>
            <input type="button" value="置顶" onclick="Operationdel(3)"/>
            </div>
        <?php endif;?>
        <?php if($this->operation == 'handle'):?>
            <div class="discuss-title">
            <a href="<?php echo self::$url;?>?operation=management"><input type="button" value="管理帖子"></a>
            </div>
        <?php endif;?>

            <!--title end-->
            <!--main-->
            <div class="discuss-main">
                <!--disuss left-->
                <div class="dis-left">
                    <!--tab-->
                    <div class="joyme-rmsy-tab-box">
                        <input type="hidden" value="<?=self::$url?>" id="url">
                        <div class="joyme-rmsy-tab-tit" id="tab">
                            <p class="cur" id="tab_1"><a href="<?php echo self::$url;?>">全部帖子</a></p>
                            <em>/</em>
                            <p id="tab_2"><a href="<?php echo self::$url?>?type=1">精品帖子</a></p>
                            <a href="<?php echo $wgServer.'/'.$wgWikiname;?>/index.php?title=THREAD&action=edit&ns=THREAD">发新帖</a>
<!--                            <span>1分钟内不能发表相同内容</span>-->
                        </div>
                        <div class="joyme-rmsy-tab-cont" id="tab_con">
                            <div id="tab_con_1">
                                <?php if(!empty($this->PostsInfo) ):?>
                                <?php foreach($this->PostsInfo as $k=>$v):?>
                                    <?php if(is_numeric($k)):?>
                                        <div class="tab-cont-list">
                                            <span><?=$v['comments_num']?></span>
                                            <a href="<?php echo self::$url;?>?&details=<?=$v['wiki_title']?>&namespace=<?=$v['page_namespace']?>" target="_blank"><?=$v['wiki_title']?></a>
                                            <?php if($v['is_top'] == 1):?>
                                                <i class="i-blue">置顶</i>
                                            <?php endif;?>
                                            <?php if($v['is_essence'] == 1):?>
                                                <i class="i-red">精品</i>
                                            <?php endif;?>
                                            <?php if($v['page_namespace'] != 1000):?>
                                                <i class="i-green">wiki</i>
                                            <?php endif;?>
                                            <i></i>
                                            <div class="joyme-author">
                                                <?php if(ctype_alnum($v['user_name']) || is_numeric($v['user_name'])){
                                                    $length = 7;
                                                }else{
                                                    $length = 7;
                                                }?>
                                                <?php $name = mb_substr($v['user_name'],0,$length,'utf-8');?>
                                                <cite><?=$name?></cite>
                                                <label>
                                                    <?php if( $this->operation == 'management'):?>
                                                        <?php if($v['is_top'] == 1 && $v['is_essence']==1):?>
                                                            <input type="hidden" value="1" id="top">
                                                        <?php elseif($v['is_top']==1 && $v['is_essence']!=1):?>
                                                            <input type="hidden" value="2" id="is_essence">
                                                        <?php elseif($v['is_top']!=1 && $v['is_essence']==1):?>
                                                            <input type="hidden" value="3" id="is_essence">
                                                        <?php endif;?>
                                                        <input type="checkbox" id="<?=$v['page_id'];?>" value="<?=$v['page_id'];?>" name="one">勾选
                                                    <?php endif;?>
                                                </label>
                                            </div>
                                            <div class="joyme-reply">
                                                <?php if($v['is_top'] != 1):?>
                                                <div class="joyme-reply-tit">
                                                    <?=$v['descrip_page']?>
                                                    <div class="joyme-reply-author">
                                                        <?php if($v['last_comment_user']):?>
                                                            <?php if(ctype_alnum($v['last_comment_user']) || is_numeric($v['last_comment_user'])){
                                                                $length = 7;
                                                            }else{
                                                                $length = 7;
                                                            }?>
                                                            <?php $name = mb_substr($v['last_comment_user'],0,$length,'utf-8');?>
                                                            <cite><?=$name?></cite>
                                                            <?php endif;?>
                                                        <em>
                                                                <?php if($v['last_comment_time'] != 0 && !empty($v['last_comment_user'])):?>
                                                                    <?php  if( $v['last_comment_time'] < strtotime(date('Y-m-d',time()))):?>
                                                                        <?php echo date("m-d",$v['last_comment_time'])?>
                                                                    <?php else:?>
                                                                        <?php echo date("H:i",$v['last_comment_time'])?>
                                                                    <?php endif;?>
                                                                <?php endif;?>
                                                        </em>
                                                    </div>
                                                </div>
                                                <div class="joyme-reply-img">
                                                    <?php if($v['descrip_image']):?>
                                                        <?php $imginfo = explode(",",$v['descrip_image'])?>
                                                        <?php foreach($imginfo as $v):?>
                                                            <?php $http = substr($v,0,4)?>
                                                            <?php if($http == 'http'):?>
                                                                <?php $image = DataSynchronization::imagePath($v,$this->width,$this->height,true)?>
                                                                <img src="<?=$image?>" onerror="this.src='<?php echo $wgStaticUrl?>/pc/wiki/discuss/images/wiki.jpg'">
                                                            <?php else:?>
                                                                <?php $image = DataSynchronization::imagePath($v,$this->width,$this->height)?>
                                                                <img src="<?=$image?>" onerror="this.src='<?php echo $wgStaticUrl?>/pc/wiki/discuss/images/wiki.jpg'">
                                                            <?php endif;?>
                                                        <?php endforeach;?>
                                                    <?php endif;?>
                                                </div>
                                                <?php endif;?>
                                            </div>
                                        </div>
                                    <?php endif;?>
                                <?php endforeach;?>
                                <!--                            </div>-->
                            </div>
                            <?php else:?>
                                现在还没有帖子
                            <?php endif;?>
                        </div>
                    </div>
                    <!--tab end-->
                    <!--pager-->
                    <div class="pager">
                        <?php echo $this->str_page;?>
                    </div>
                </div>
                <!--disuss left end-->
                <!--disuss rigth-->
                <div class="dis-right">
                    <!--hot top-->
                    <div class="dis-right-box">
                        <?php @include_once($this->filepath)?>
                    </div>
                    <!--hot top  end-->
                    <!--link-->
                    <div class="dis-right-box">
                        <div class="hot-tit">友情讨论区
                            <?php if($this->operation):?>
                                <a href="#" class="setting-btn">设置</a>
                            <?php endif;?>
                        <div class="recommend-wiki" id="LinkInfo">

                            <?php if(file_exists($this->linkFile) && strlen(file_get_contents($this->linkFile))):?>
                                <?php include_once($this->linkFile)?>
                            <?php else:?>
                                目前没有友情讨论区
                                <?php if($this->operation):?>
                                    ,您可以 <span style="color: blue;cursor:pointer" class="setting-btn">点此添加</span>
                                <?php endif;?>

                            <?php endif;?>
                        </div>
                    </div>
                    <!--hot top  end-->
                </div>
                <!--disuss rigth end-->
            </div>
            <!--main end-->
            <div class="float-win">
                <h3>设置<span class="btn-close">关闭</span></h3>
                <div class="fw-box">
                    <span>域名：<input class="inp-text" type="text" name="text" id="wikitext" value="" /></span>
                    <span style="padding-left:5px;">名称：<input class="inp-text" type="text" id="wikiname" name="text" value="" /></span>
                    <span class="add-btn" id="addFriendshipLinks">添加</span>
                    <div class="clearfix"><span>图片：
                    <form method="post" action="<?php echo $wgServer;?>/joyme_api.php?action=commentUpload" enctype="multipart/form-data" target="xframe" id="imgForm">
                        <input type="file" name="commentImg" id="commentImg2" accept="image/*"/>
                        <input type="hidden" name="edittoken" id="edittoken" />
                    </form>
                    </span></div>
                    <span id="tips2" style="display: none;"><img src='<?php echo $wgStaticUrl?>/pc/wiki/discuss/images/loading.gif'></span>
                    <iframe name="xframe" id="xframe" src="" style="display: none;"></iframe>​
                    <input type="hidden" id="imageval" value="">
                    <span id="errMsg" style="color: red;"></span>
                </div>
                <div class="fw-status">
                    <span id="imageTip"></span>
                    <h4>最多可添加6个讨论区</h4>
                    <div id="_tips">
                        <span id="tips1" style="display:none;color: red;">域名或名称不能为空</span>
                    </div>
                    <div class="add-list"></div>

                </div>
            </div>
        </div>
        <script type="text/javascript" src="<?php echo $wgStaticUrl?>/js/jquery-1.9.1.min.js"></script>
        <script src="<?php echo $wgWikiStatic;?>/extensions/Discussion/jsscript/wikiPosts.js"></script>

            <?php endif;?>
    <?php
    }
}
?>
