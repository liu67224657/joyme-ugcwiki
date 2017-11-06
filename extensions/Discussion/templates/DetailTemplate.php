<?php
class DetailTemplate extends BaseTemplate {
	private $article = array();
    static $url;
    public function __construct($data,$topdatapath,$linkdatapath,$url){

	   $this->filepath = $topdatapath;
       $this->linkFile = $linkdatapath;
       $this->article = $data;
       self::$url = $url;
       $model = new SpecialDiscussion();
       $this->operation = $model->userPermissions();
    }

    public function execute() {
		global $wgEnv, $wgThread, $wgIsLogin,$wgServer,$wgStaticUrl,$wgPhpServer,$wgWikiname;
		if(!$wgThread){
			echo '<div>你所访问的wiki讨论区暂未开放</div>';
		}else if($this->article['error']){
			echo '<div>你所访问的wiki帖子数据丢失</div>';
		}else{
        ?>
		<link href="<?php echo $wgStaticUrl?>/pc/wiki/discuss/css/discuss.css" rel="stylesheet" type="text/css">
        <!--main-->
        <div class="discuss-main">
        <!--disuss left-->
        <div class="dis-left">
            <!--tab-->
            <div class="joyme-rmsy-tab-box">
				
                <div class="joyme-rmsy-tab-tit">
                    <p class="cur" id="tab_1"><a href="<?php echo self::$url;?>">全部帖子</a></p>
                    <em>/</em>
                    <p id="tab_2"><a href="<?php echo self::$url;?>?type=1">精品帖子</a></p>
					<a href="<?php echo $wgServer.'/'.$wgWikiname;?>/index.php?title=THREAD&amp;action=edit&amp;ns=THREAD">发新帖</a>
					<?php if($this->article['power'] || (!$wgIsLogin && ($this->article['user_id'] == $_COOKIE['jmuc_u']))):?>
						<a href="<?php echo $wgServer.'/'.$wgWikiname.'/index.php?title='.$this->article['parse']['title'].'&action=edit';?>">编辑</a>
					<?php endif;?>
					<?php if($this->article['power']):?>
						<?php if($this->article['is_essence']):?>
							<a href="javascript:void(0);" id="quxiaojiajing">取消加精</a>
						<?php endif;?>
						<?php if($this->article['is_top']):?>
							<a href="javascript:void(0);" id="quxiaozhiding">取消置顶</a>
						<?php endif;?>
					<?php endif;?>
                </div>
                <!--post-->
                <div class="post-cont">
					<input type="hidden" name="pageId" id="pageId" value="<?php echo $this->article['page_id']?>">
					<input type="hidden" name="mytoken" id="mytoken" value="<?php echo $this->article['mytoken']?>">
					<input type="hidden" name="namespace" id="namespace" value="<?php echo $this->article['namespace']?>">
                	<div class="post-cont-tit"><span id="title"><?php echo str_replace('THREAD:', '', $this->article['parse']['title']);?> </span><a href="#mainPageList">回复</a></div>
                    <div class="post-cont-author">
                    	<cite><img id="uIcon" data-uid="<?php echo $this->article['user_id'];?>" src="" /></cite>
                        <div class="post-cont-author-tit">来自: <span id="author"><?php echo $this->article['user_name'];?></span>   <?php echo date('Y-m-d H:i:s', $this->article['create_time']);?><i>楼主</i></div>
                        <div class="post-cont-main">
                        	<?php echo $this->article['parse']['text']['*'];?>
                        </div>
                        <div class="post-cont-point" id="articleAgree">
							<a href="javascript:void(0);" ><?php echo $this->article['prise_num']?></a>
                        </div>
                    </div>
                </div> 
                <!--post end-->
                <!--reply-->
                <div class="post-reply-cont" id="comment_list_area"></div>
                <!--reply end-->
                
            <!--pager-->
            <div class="pager" id="mainPageList"></div>
    	<!--disuss left end-->
                <!--主楼回复-->
                <div class="post-reply-detail-main">   
                    
                    <div class="post-reply-review">
                        <!--<div class="review-textarea"><textarea id="textarea_body_0"></textarea></div>-->
						<div class="review-textarea" id="textarea_body_0" contenteditable="true"></div>
                        <div class="review-icon">
                            <!--<a href="javascript:void(0);" class="review-icon-img">图片</a>-->
							<a class="review-icon-img" href="javascript:void(0);">图片</a>
							<form method="post" action="<?php echo $wgServer?>/joyme_api.php?action=commentUpload" enctype="multipart/form-data" target="xframe" id="imgForm">
								<input type="file" name="commentImg" id="commentImg" accept="image/*"/>
								<input type="hidden" name="edittoken" id="edittoken" />
							</form>
                            <em>|</em>
                            <a href="javascript:void(0);" class="review-icon-phiz" data-oid="0">表情</a>
                            <button class="cancel" id="comment_submit">评论</button>
                            <span id="errMsg"></span>
                        </div>
                    </div>
                </div>
                <!--主楼回复 end-->
			</div>
            <!--tab end-->
            <!--pager-->
            <div class="pager">
                <?php //echo $this->str_page?>
            </div>
        </div>
        <!--disuss left end-->
        <!--disuss rigth-->
        <div class="dis-right">
			<!--
            <div class="dis-promote">
                <a href="">
                    <cite><img src="./resources/src/mediawiki.posts/images/left-img.jpg"></cite>
                    <span>推广</span>
                </a>
            </div>
			-->
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
            <input type="hidden" value="<?=self::$url?>" id="url">
            <div class="float-win" id="imgdiv1">
                <h3>设置<span class="btn-close">关闭</span></h3>
                <div class="fw-box">
                    <span>域名：<input class="inp-text" type="text" name="text" id="wikitext" value="" /></span>
                    <span style="padding-left:5px;">名称：<input class="inp-text" type="text" id="wikiname" name="text" value="" /></span>
                    <span class="add-btn" id="addFriendshipLinks">添加</span>
                    <div class="clearfix"><span>图片：
                    <form method="post" action="<?php echo $wgServer;?>/joyme_api.php?action=commentUpload" enctype="multipart/form-data" target="xframe" id="imgForm2">
                        <input type="file" name="commentImg" id="commentImg2" accept="image/*"/>
                        <input type="hidden" name="edittoken" id="edittoken" />
                    </form>
                    </span></div>
                    <span id="tips2" style="display: none;"><img src='<?php echo $wgStaticUrl?>/pc/wiki/discuss/images/loading.gif'></span>
                    <input type="hidden" id="imageval" value="">
                    <span id="errMsg2" style="color: red;"></span>
                </div>
                <div class="fw-status">
                    <h4>最多可添加6个讨论区</h4>
                    <div id="_tips">
                        <span id="tips1" style="display:none;color: red;">域名或名称不能为空</span>
                    </div>
                    <div class="add-list"></div>
                </div>
            </div>
        </div>
		<div id="biaoqing" style="display:none;"></div>
		<iframe name="xframe" id="xframe" src="" style="display: none;"></iframe>​
        <script type="text/javascript" src="<?php echo $wgStaticUrl?>/js/jquery-1.9.1.min.js"></script>
		<script src="<?php echo $wgPhpServer;?>/extensions/jsscripts/commonfn.js"></script>
		<script src="<?php echo $wgPhpServer;?>/extensions/Discussion/jsscript/taolunqu_comment.js"></script>
		<script type="text/javascript" src="http://api.joyme.<?php echo $wgEnv;?>/json/mood?callback=emojiData"></script>

    <?php
		}
    }
}
?>
