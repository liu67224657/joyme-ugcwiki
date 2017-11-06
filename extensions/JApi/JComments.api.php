<?php

class JCommentsAPI extends ApiBase {

	//$echotype 1点赞 2 评论 3艾特我的 4删除楼中楼评论
	//$type 1评论点赞 2 文章点赞
    public function execute() {
		global $wgWikiname, $wgSiteGameTitle,$wgEnv;
		$echotype = $this->getMain()->getVal( 'echotype' );
		$tuid = $this->getMain()->getVal( 'tuid' );
		$uid = $this->getMain()->getVal( 'uid' );
		$uname = $this->getMain()->getVal( 'uname' );
		$title = $this->getMain()->getVal( 'title' );
		$desc = $this->getMain()->getVal( 'desc' );
		$cid = $this->getMain()->getVal( 'cid' );
		$pageid = $this->getMain()->getVal( 'pageid' );
		$type = $this->getMain()->getVal( 'type' );
		$plid = $this->getMain()->getVal( 'plid' );
		
		//加入排行榜
		if($echotype == 1 && $type == 1){
			//评论点赞不做处理
			
		}elseif($echotype == 4){
			JoymeRank::addContentRank('hot', $pageid,-1);
		}else{
			JoymeRank::addContentRank('hot', $pageid);
		}
		
		if($echotype == 1){
			JoymeWikiUser::pointsreport(14,$uid);
		}elseif($echotype == 2){
			JoymeWikiUser::pointsreport(13,$uid);
		}
		
		if($tuid == $uid){
			$result = $this->getResult();
			$result->addValue( $this->getModuleName(), 'ok', 'ok' );
			return true;
		}
		$rs = true;
		if($echotype == 1){
			JoymeWikiUser::updateUserLikeCount($tuid);
			/*JoymeReminderMessage::onAddMyNotification( 'article-thumb-up',$tuid,$uid,$uname,$title,$desc, $cid, $wgWikiname, $wgSiteGameTitle, $type, $plid );*/
			if($type==1){
				$desttype = 5;
				JoymeWikiUser::noticereport(array(
					'tuid' => $tuid,
					'uid' => $uid,
					'ntype' => 'agree',
					'curl' => '<a target="_blank" href="http://wiki.joyme.'.$wgEnv.'/'.$wgWikiname.'/'.$title.'?plid='.$plid.'">'.$desc.'</a>',
					'desc' => $wgSiteGameTitle,
					'desttype' => $desttype
				));
			}else{
				$desttype = 4;
				//JoymeWikiUser::pointsreport(23,$tuid);
				$pcreator = JoymeSite::getPageCreator($pageid);
				JoymeWikiUser::noticereport(array(
				'tuid' => $pcreator->rev_user,
				'uid' => $uid,
				'ntype' => 'agree',
				'url' => '<a target="_blank" href="http://wiki.joyme.'.$wgEnv.'/'.$wgWikiname.'/'.$title.'">'.$title.'</a>',
				'curl' => '<a target="_blank" href="http://wiki.joyme.'.$wgEnv.'/'.$wgWikiname.'/'.$title.'?plid='.$plid.'">'.$desc.'</a>',
				'desc' => $wgSiteGameTitle,
				'desttype' => $desttype
				));
			}
		}else if($echotype == 2){
			JoymeWikiUser::updateUserCommentCount($tuid);
			/*JoymeReminderMessage::onAddMyNotification( 'article-comments',$tuid,$uid,$uname,$title,$desc, $cid, $wgWikiname, $wgSiteGameTitle, $type, $plid );*/
			
			$pcreator = JoymeSite::getPageCreator($pageid);
			if($type == 1){
				$desttype = 1; //发表评论
				if($pcreator->rev_user != $uid){
					JoymeWikiUser::noticereport(array(
						'tuid' => $pcreator->rev_user,
						'uid' => $uid,
						'ntype' => 'reply',
						'url' => '<a target="_blank" href="http://wiki.joyme.'.$wgEnv.'/'.$wgWikiname.'/'.$title.'">'.$title.'</a>',
						'curl' => '<a target="_blank" href="http://wiki.joyme.'.$wgEnv.'/'.$wgWikiname.'/'.$title.'?plid='.$plid.'">'.$desc.'</a>',
						'desc' => $wgSiteGameTitle,
						'desttype' => $desttype
					));
				}
			}elseif($type == 2){
				$desttype = 2;//回复了我
				if($pcreator->rev_user != $tuid){
					//上报给创建者 - 回复了其他人
					JoymeWikiUser::noticereport(array(
						'tuid' => $pcreator->rev_user,
						'uid' => $uid,
						'otherpid'=>$tuid,
						'ntype' => 'reply',
						'url' => '<a target="_blank" href="http://wiki.joyme.'.$wgEnv.'/'.$wgWikiname.'/'.$title.'">'.$title.'</a>',
						'curl' => '<a target="_blank" href="http://wiki.joyme.'.$wgEnv.'/'.$wgWikiname.'/'.$title.'?plid='.$plid.'">'.$desc.'</a>',
						'desc' => $wgSiteGameTitle,
						'desttype' => 3
					));
					//上报给对方 - 回复了我
					JoymeWikiUser::noticereport(array(
						'tuid' => $tuid,
						'uid' => $uid,
						'ntype' => 'reply',
						'url' => '<a target="_blank" href="http://wiki.joyme.'.$wgEnv.'/'.$wgWikiname.'/'.$title.'">'.$title.'</a>',
						'curl' => '<a target="_blank" href="http://wiki.joyme.'.$wgEnv.'/'.$wgWikiname.'/'.$title.'?plid='.$plid.'">'.$desc.'</a>',
						'desc' => $wgSiteGameTitle,
						'desttype' => $desttype
					));
				}else{
					JoymeWikiUser::noticereport(array(
						'tuid' => $tuid,
						'uid' => $uid,
						'ntype' => 'reply',
						'url' => '<a target="_blank" href="http://wiki.joyme.'.$wgEnv.'/'.$wgWikiname.'/'.$title.'">'.$title.'</a>',
						'curl' => '<a target="_blank" href="http://wiki.joyme.'.$wgEnv.'/'.$wgWikiname.'/'.$title.'?plid='.$plid.'">'.$desc.'</a>',
						'desc' => $wgSiteGameTitle,
						'desttype' => $desttype
					));
				}
			}

		}else if($echotype == 3){
			/*JoymeReminderMessage::onAddMyNotification( 'article-cite-my',$tuid,$uid,$uname,$title,$desc, $cid, $wgWikiname, $wgSiteGameTitle, $type, $plid );*/
			$rs = JoymeWikiUser::noticereport(array(
				'tuid' => $tuid,
				'uid' => $uid,
				'ntype' => 'at',
				'url' => '<a target="_blank" href="http://wiki.joyme.'.$wgEnv.'/'.$wgWikiname.'/'.$title.'">'.$title.'</a>',
				'curl' => '<a target="_blank" href="http://wiki.joyme.'.$wgEnv.'/'.$wgWikiname.'/'.$title.'?plid='.$plid.'">'.$desc.'</a>',
				'desc' => $wgSiteGameTitle,
			));
		}
		
		$result = $this->getResult();
		//if($rs){
			$result->addValue( $this->getModuleName(), 'ok', 'ok' );
		//}else{
		//	$result->addValue( $this->getModuleName(), 'fail', 'fail' );
		//}
		
        return true;
    }

    public function getAllowedParams() {
        return array(
            'echotype' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            ),
            'tuid' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'integer'
            ),
			'uid' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'integer'
            ),
			'uname' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            ),
			'title' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            ),
			'desc' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            ),
			'cid' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'integer'
            ),
			'type' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'integer'
            ),
			'plid' => array(
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'string'
            ),
        );
    }
}