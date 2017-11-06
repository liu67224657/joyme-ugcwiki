<?php 
// This is the hook function. It adds the tag to the wiki parser and tells it what callback function to use.
function wfAddCompareHook() {
	global $wgParser;
	# register the extension with the WikiText parser
	$wgParser->setHook( "pcompare", "compare" );
}
# The callback function for converting the input text to HTML output
function compare( $input ,$argv ) {
	$host = $_SERVER['HTTP_HOST'];
	$html = <<<EOT
<script src="http://$host/extensions/jsscripts/compare.js"></script>
<div class="compare" id="compare" style="display:none;">
	<table class="floatBox">
		<thead>
			<tr>
				<th colspan="5">
					<div class="th-tit">
						<a href="javascript:;" onclick="card_hidden()">隐藏</a><em>对比栏</em>		
					</div>
				</th>
			</tr>
			<tr>
				<td width="30%" class="td-bg1">
					
				</td>
				<td width="30%" class="td-bg2">
				</td>
				<td width="30%" class="td-bg3">
					
				</td>
				<td width="10%">
					<div class="float_btn">
						<dl>
							<dt><a href="javascript:;" id="tocompare" onclick="card_chakan()">对比</a></dt>
							<dd><a href="javascript:;" onclick="card_clear();">清空对比栏</a></dd>
						</dl>
					</div>
				</td>
			</tr>
		</thead>
	</table>
</div>
EOT;
	return $html;
}

$wgExtensionFunctions[] = "wfAddCompareHook";

?>