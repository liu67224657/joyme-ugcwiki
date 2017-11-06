/**
*图片转换插件
*/

var imgChange = function (data){
	this.data = data;
	this.num = this.data.length;
}

imgChange.prototype.run = function(){
	for(img in this.data){
		var imgs = this.data[img].split('/');
		var img1 = encodeURI(imgs[0]);
		var img2 = encodeURI(imgs[1]);
		this.imgHide(img2);
		this.change(img1, img2);
	}
}

imgChange.prototype.change = function(img1, img2){
	$("img[src *= '"+img1+"']").parent("a").mouseover(
		function(){
			$(this).hide();
			$("img[src *= '"+img2+"']").parent("a").show();
		}
	);
	$("img[src *= '"+img2+"']").parent("a").mouseout(
		function(){
			$(this).hide();
			$("img[src *= '"+img1+"']").parent("a").show();
		}
	);
}

imgChange.prototype.imgHide = function(img2){
	$("img[src *= '"+img2+"']").parent("a").hide();
}




$(function(){
	$('.imgchange').each(function(i,v){
		var wikiImgChange = new imgChange($(v).val());
		wikiImgChange.run();
	});
	
});


//var imgChange = new imgChange();
//imgChange.change('9JJ5O76I00AP0001.jpg', 'Watermark.jpg');
//imgChange.run();