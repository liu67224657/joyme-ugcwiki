<?php

$wgAjaxExportList[] = 'wfUserRecommendUsers';

function wfUserRecommendUsers( $user_id )
{
    /**
     * The user center recommend users data
     * @return Integer: array
     */
    if( empty($user_id) ){
        return false;
    }

    $model = new RecommendUsers();

    $userinfo = $model->getUserInfo( $user_id );
    $data = '';
    if($userinfo){
    	$i = 0;
        foreach ($userinfo as $manito) {
            $manuserPage = htmlspecialchars(Title::makeTitle( NS_USER, $manito['nick'] )->getFullURL());
            $follow_centent = $manito['is_follow']==1?'<span class="followed"><i class="fa fa-check"></i>已关注</span>': '<span class="user-recommend-follow" data-uid="'.$manito['uid'].'"><i class="fa fa-plus" aria-hidden="true" ></i>关注</span>';
            
            if($i<4){
            	$stylestr = 'style="display:block;"';
            }else{
            	$stylestr = 'style="display:none;"';
            }
            $i++;
            
            $data .= '<li '.$stylestr.'>
                            <div class="int-tj-l">
                                <cite><a href="' . $manuserPage . '"><img src="' . $manito['icon'] . '" alt="img"></a></cite>
                            </div>
                            <div class="int-tj-r">
                                <font>' . $manito['nick'] . '</font>
                                <b>' . mb_substr($manito['brief'],0,5,"UTF-8") . '</b>
                                '.$follow_centent.'
                            </div>
                       </li>';
        }
    }

    $res = RecommendUsers::returnJson( $data );
    echo $res;
    exit;
}









