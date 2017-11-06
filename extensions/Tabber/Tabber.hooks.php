<?php
/**
 * Tabber
 * Tabber Hooks Class
 *
 * @author		Eric Fortin, Alexia E. Smith
 * @license		GPL
 * @package		Tabber
 * @link		https://www.mediawiki.org/wiki/Extension:Tabber
 *
 **/

class TabberHooks {
	/**
	 * Sets up this extension's parser functions.
	 *
	 * @access	public
	 * @param	object	Parser object passed as a reference.
	 * @return	boolean	true
	 */
	static public function onParserFirstCallInit(Parser &$parser) {
		$parser->setHook("tabber", "TabberHooks::renderTabber");

		return true;
	}

	/**
	 * Renders the necessary HTML for a <tabber> tag.
	 *
	 * @access	public
	 * @param	string	The input URL between the beginning and ending tags.
	 * @param	array	Array of attribute arguments on that beginning tag.
	 * @param	object	Mediawiki Parser Object
	 * @param	object	Mediawiki PPFrame Object
	 * @return	string	HTML
	 */
	static public function renderTabber($input, array $args, Parser $parser, PPFrame $frame) {

		$type = 0;
		$limit = 2;
		$HTML = '';

		if(isset($args['type']) && !empty($args['type'])){
			$type = intval($args['type']);
		}

		if(isset($args['limit']) && !empty($args['limit'])){
			$limit = intval($args['limit']);
		}

		$key = md5($input);
		$arr = explode("|-|", $input);
		$htmlTabs = '';
		$tabHtml = '';
		$moreHtml = '';
		$moreTabHtml = '';
		$num = 0;

		if($type == 1){
			//横版
			$parser->getOutput()->addModules('ext.newTabber');
			foreach ($arr as $k=>$tab) {
				$tab = trim($tab);
				if (empty($tab)) {
					return $tab;
				}
				$num++;
				$args = explode('=', $tab);
				$tabName = array_shift($args);
				if($limit<$num){
					$moreTabHtml .= '<a class="hm-box">'.$tabName.'</a>';
				}else{
					$tabHtml .= '<a class="mb-box">'.$tabName.'</a>';
				}
				if($k == 0){
					$htmlTabs .= '<div class="news1-con show-wjm">'.$parser->recursiveTagParse(implode('=', $args)).'</div>';
				}else{
					$htmlTabs .= '<div class="news1-con show-wjm" style="display: none">'.$parser->recursiveTagParse(implode('=', $args)).'</div>';
				}
			}
			//更多
			if($limit < $num){
				$moreHtml = '<a class="gengduo bl"><i class="m-txt">更多</i><i class="xiala"></i></a>
							<div class="tiao"></div>
							<div class="hid-menu-wjm">'.$moreTabHtml.'<div class="c-wjm"></div>
							</div>
							<div class="c-wjm"></div>';
			}
			$HTML = '<div class="wjm1125 news1-wjm">
						<div class="wjm1125 meunbox1-wjm meunbox2">'.$tabHtml.$moreHtml.'
						</div>
						<div class="news1-con-all">'.$htmlTabs.'
						</div>
					</div>';
		}elseif($type == 2){

			//竖版
			$parser->getOutput()->addModules('ext.newTabber');
			foreach ($arr as $k=>$tab) {
				$tab = trim($tab);
				if (empty($tab)) {
					return $tab;
				}
				$num++;
				$args = explode('=', $tab);
				$tabName = array_shift($args);
				if($k == 0){
					$tabHtml .= '<a class="mb-box bl1 mon3"><i class="border1"></i>'.$tabName.'</a>';
					$htmlTabs .= '<div class="news1-con show-wjm">'.$parser->recursiveTagParse(implode('=', $args)).'</div>';
				}else{
					$tabHtml .= '<a class="mb-box bl1"><i class="border1"></i>'.$tabName.'</a>';
					$htmlTabs .= '<div class="news1-con show-wjm" style="display: none">'.$parser->recursiveTagParse(implode('=', $args)).'</div>';
				}
			}
			$HTML = '<div class="wjm1125 news1-wjm">
						<div class="wjm1125 meunbox3-wjm ">'.$tabHtml.'<a class="gengduo  bl"><i class="xiala"></i></a>
							<div class="tiao"></div>
							<div class="hid-menu-wjm">
								<div class="c-wjm"></div>
							</div>
							<div class="c-wjm"></div>
						</div>
						<div class="news1-con-all news1-con-all-3">'.$htmlTabs.'</div>
					</div>';

		}else{
			//官方默认
			$parser->getOutput()->addModules('ext.Tabber');
			foreach ($arr as $tab) {
				$htmlTabs .= self::buildTab($tab, $parser);
			}
			$HTML = '<div id="tabber-'.$key.'" class="tabber">'.$htmlTabs."</div>";
		}

		return $HTML;
	}

	/**
	 * Build individual tab.
	 *
	 * @access	private
	 * @param	string	Tab information
	 * @param	object	Mediawiki Parser Object
	 * @return	string	HTML
	 */
	static private function buildTab($tab = '', Parser $parser) {

		$tab = trim($tab);
		if (empty($tab)) {
			return $tab;
		}
		$args = explode('=', $tab);
		$tabName = array_shift($args);
		$tabBody = $parser->recursiveTagParse(implode('=', $args));
		$tab = '
			<div class="tabbertab" title="'.htmlspecialchars($tabName).'">
				<p>'.$tabBody.'</p>
			</div>';

		return $tab;
	}
}
