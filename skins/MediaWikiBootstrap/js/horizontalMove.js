;(function($){
	$.extend($,{
		horizontalMove:function(option){
		
			var opt = {
				parent : $('.hot-wiki-listin'),
				sonUl : $('.hot-wiki-parent'), // 滑动的块
				curShowNum : 5, // 显示区内的个数；
				leftBtn :'.wiki-change-btn-left',
				rightBtn : '.wiki-change-btn-right',
				sonMargin:19
			}
			$.extend(opt,option);
			function Hori(opt){
				this.init(opt);
			}
			Hori.prototype.init = function(opt){
				this.initDom(opt);
				this.render();
				this.bind();
			} 
			Hori.prototype.initDom = function(){
				this.parent = opt.parent;
				this.sonUl = opt.sonUl; // 滑动的块
				this.sonUlChild = this.sonUl.children();
				this.sonUlChildLen = this.sonUlChild.length; // 滑块总个数
				this.singleWidth = this.sonUlChild.outerWidth()+opt.sonMargin;
				this.index = 0; // 向左滚动的个数；
				this.curShowNum = opt.curShowNum; // 显示区内的个数；
				this.leftBtn = this.parent.parent().find(opt.leftBtn);
				this.rightBtn = this.parent.parent().find(opt.rightBtn);
				this.sonMargin = opt.sonMargin;
			} 
			Hori.prototype.render = function(){
				this.sonUl.css('width',this.singleWidth*this.sonUlChildLen + this.sonMargin + "px");
				this.checkBtnshow();
			} 
			Hori.prototype.bind = function(){
				var that = this;
				this.leftBtn.click(function(){
					that.index--;
					that.move();
					that.checkBtnshow();
				});
				this.rightBtn.click(function(){
					that.index++;
					that.move();
					that.checkBtnshow();
				})
			} 
			Hori.prototype.checkBtnshow = function(){
				this.rightBtn.show();
				this.leftBtn.show();
				if(this.index == 0){
					this.leftBtn.hide();
				}
				if((this.index + this.curShowNum) >= (this.sonUlChildLen)){
					this.rightBtn.hide();
				}
				
			} 
			Hori.prototype.move = function(num){
				this.sonUl.animate({
					left:-this.index*this.singleWidth+"px"
				})
			} 
			return new Hori(opt); 
		}
	})

})(jQuery);
