(function(){
	function scrollImg(){
		var oDiv=document.getElementById('s-img');
		var oUl=oDiv.getElementsByTagName('ul')[0];
		var aLi=oUl.getElementsByTagName('li');
		var speed=560;
		var t=null;
		oUl.innerHTML+=oUl.innerHTML;
		oUl.style.width=aLi[0].offsetWidth*aLi.length+'px';
		function startMove(obj, iTarget)
		{
			clearInterval(obj.t);
			obj.t=setInterval(function (){
				var sp=(iTarget-obj.offsetLeft)/5;
				sp=sp>0?Math.ceil(sp):Math.floor(sp);
				console.log('obj.offsetLeft：' + obj.offsetLeft);
				console.log('iTarget：' + iTarget);
				if(oUl.offsetLeft<-oUl.offsetWidth/2)
				{
					oUl.style.left='0';
					iTarget=0;
				}
				if(oUl.offsetLeft>0)
				{
					oUl.style.left=-speed+'px';	
					iTarget=-oUl.offsetWidth/2;	
				}
				if(obj.offsetLeft==iTarget)
				{
					clearInterval(obj.t);
				}
				else
				{
					obj.style.left=obj.offsetLeft+sp+'px';
				}
			}, 100);
		}
		document.getElementById('hot-btn-l').onclick=function ()
		{
			speed=560;
			startMove(oUl,oUl.offsetLeft+speed);
		};
		document.getElementById('hot-btn-r').onclick=function ()
		{
			speed=-560;
			startMove(oUl,oUl.offsetLeft+speed);
		};
	}
	scrollImg();
})();