<?php
use Joyme\core\Request;
class SpecialWikiDynamic extends UnlistedSpecialPage{

    public $search_type = array(
                2=>'全部',
                1=>'内容更新',
                0=>'内容新增'
        );

    public $search_time = array(
                0=>'本日动态',
                1=>'过去1天动态',
                7=>'过去7天动态',
                14=>'过去14天动态',
                30=>'过去30天动态',
        );

    //搜索参数
    public $paras = array();

    public function __construct(){

        parent::__construct('WikiDynamic', 'wikidynamic');
    }

    public function execute($par) {

        global $wgUser;

        $this->setHeaders();
        if ( wfReadOnly() ) {
            throw new ReadOnlyError;
        }
        if ( $wgUser->isBlocked() ) {
            throw new UserBlockedError( $this->getUser()->mBlock );
        }

        $output = $this->getOutput();
        $output->addModuleStyles( 'ext.WikiDynamic.css' );
        $output->addModuleScripts( 'ext.WikiDynamic.js' );

        $pb_page = intval(Request::get('pb_page',1));
        $type1 = Request::get('page_type',1);
        $type2 = Request::get('page_type',0);
        $time = Request::get('day',0);

        if($type1==$type2){
            $this->paras = array(
                'search_type'=>$type1,
                'search_time'=>$time
            );
        }else{
            $this->paras = array(
                'search_time'=>$time
            );
        }
        $this->buildListHtml( $pb_page,$type1,$type2,$time );
    }

    //类型选项
    public function getSearchTyleOptions(){

        $optionsTyle = array();
        foreach($this->search_type as $k=>$v){
            if(key_exists('search_type',$this->paras)){
                if($this->paras['search_type'] == $k){
                    $optionsTyle[] = '<option value="'.$k.'" selected="selected">'.$v.'</option>';
                }else{
                    $optionsTyle[] = '<option value="'.$k.'">'.$v.'</option>';
                }
            }else{
                $optionsTyle[] = '<option value="'.$k.'">'.$v.'</option>';
            }
        }
        return $optionsTyle;
    }

    //时间选项
    public function getSearchTimeOptions(){

        $optionsTime = array();
        foreach($this->search_time as $k=>$v){
            if(key_exists('search_time',$this->paras)){
                if($this->paras['search_time'] == $k){
                    $optionsTime[] = '<option value="'.$k.'" selected="selected">'.$v.'</option>';
                }else{
                    $optionsTime[] = '<option value="'.$k.'">'.$v.'</option>';
                }
            }else{
                $optionsTime[] = '<option value="'.$k.'">'.$v.'</option>';
            }
        }
        return $optionsTime;
    }

    //内容列表
    public function buildContent( $pb_page,$type1,$type2,$time ){

        global $wgWikiname;

        $url = $this->getPageTitle()->getLocalUrl("getval=1");

        $result = WikiDynamicClass::listData( $pb_page,$type1,$type2,$time,$url);
        $pageHtml = '';
        $listHtml = '';
        if($result['rs']==1){
            foreach($result['result']['data'] as $k=>$v){
                $is_new = $v['rc_new']==1?'新增内容':'内容更新';
                $listHtml.='<tr style="text-align: center"><td>'.$is_new.'</td>';
                $listHtml.='<td>'.$v['time'].'</td>';
                $listHtml.='<td><a target="_blank" href="/home/用户:'.$v['rc_user_text'].'">'.$v['rc_user_text'].'</a></td>';
                $listHtml.='<td><a target="_blank" href="/'.$wgWikiname.'/'.$v['rc_title'].'" title="'.$v['rc_title'].'">'.$v['rc_title'].'</a></td></tr>';
            }
            $pageHtml = $result['result']['page'];
        }else{
            $listHtml = '<tr><td colspan="4" style="text-align: center">暂无内容</td></tr>';
        }
        $html = '<table class="wikitable">
                        <tbody><tr>
                            <th style="text-align: center">更新性质</th>
                            <th style="text-align: center">更新时间</th>
                            <th style="text-align: center">更新人</th>
                            <th style="text-align: center">更新位置</th>
                        </tr>'.$listHtml.'</tbody>
                    </table>'.$pageHtml.'';
        return $html;
    }

    //列表页面
    public function buildListHtml( $pb_page,$type1,$type2,$time ){

        $this->getOutput()->addHTML('
            <div class="section-right1">
               <div class="section1">
                   <div class="gx-lf">
                       <p class="gx-title">更新性质</p>
                   <div class="select-area">
                       <div class="select-ele">
                           <span class="select-value">全部</span>
                           <i class=""></i>
                       </div>'.
                       Xml::tags( 'select',array('name'=>'choose','id'=>'search_type'),implode( "\n", $this->getSearchTyleOptions() ) )
                   .'</div>
                  </div>
                   <div class="gx-lf">
                       <p class="gx-title">更新时间</p>
                       <div class="select-area">
                           <div class="select-ele">
                               <span class="select-value select-value2">全部</span>
                               <i class="fa"></i>
                           </div>'.
                           Xml::tags( 'select',array('name'=>'choose','id'=>'search_time'),implode( "\n", $this->getSearchTimeOptions() ) )
                    .'</div>
                   </div>
                   <div class="fn-clear"></div><input type="hidden" value="'.$pb_page.'" id="pb_page">
               </div><div class="section2">'.$this->buildContent( $pb_page,$type1,$type2,$time ).'</div>
            </div>
            </div>');
    }
}