<?php

class UserBoardAPI extends ApiBase {

	//uid 用户id
	//type 1为获取私信数 2修改隐私状态3关注处理4取消关注处理
	//is_secretchat 私信隐私设置：0关闭，1开启
    public function execute() {
		global $wgWikiname;
		$uid = $this->getMain()->getVal( 'uid' );
		$tuid = $this->getMain()->getVal( 'tuid' ,0);
		$type = $this->getMain()->getVal( 'type' ,1);
		$is_secretchat = $this->getMain()->getVal( 'is_secretchat' ,0);
		$is_secretchat = $is_secretchat==1?1:0;

		$user = User::newFromId($uid);
		$tuser = User::newFromId($tuid);

		if($user->whoIs($uid)){
			if($type == 1){
				$stats = new UserStats( $user->getId() ,$user->getName());
				$stats_data = $stats->getUserStats();
				$rs = intval($stats_data['user_board'] + $stats_data['user_board_priv']);
				$rs = $rs>0?$rs:0;
			}elseif($type == 2){
				$joymewikiuser = new JoymeWikiUser();
				$useradd = array('user_id'=>$uid,'is_secretchat'=>$is_secretchat);
				
				$rs = $joymewikiuser->editUserAddition($useradd);
				$rs = $rs == true?'ok':'false';
			}elseif($type == 3){
				$uuf = new UserUserFollow();
				$uuf->addUserUserFollow($user, $tuser);
				$rs = 'ok';
			}elseif($type == 4){
				$uuf = new UserUserFollow();
				$uuf->deleteUserUserFollow($user, $tuser);
				$rs = 'ok';
			}else{
				$rs = 'false';
			}
			
			$data = array('rs'=>1,'msg'=>'success','result'=>$rs);
		}else{
			$data = array('rs'=>-10104,'msg'=>'profile.not.exists');
		}
		
		$result = $this->getResult();
        $result->addValue( null,'data', $data );
        return true;
    }
}