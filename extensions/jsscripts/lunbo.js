(function(ele) {
	var timer = null;
	var n = 0;
	var ptime = document.getElementById("plooptime").value;
	var img = ele.getElementsByTagName("img");
	var len = img.length;
	if(img[0]){
		img[0].style.display = "inline-block";
	}else{
		console.log('图片插件：图片不存在');
	}
	

	var span = document.createElement("span");
	for (var i = 0; i < len; i++) {
		var d = document.createElement("i");
		if (i === 0) {
			d.className = "current";
		}
		d.setAttribute("idx", i);
		span.appendChild(d);
	}
	ele.appendChild(span);
	span.style.marginLeft = -(span.offsetWidth / 2) + "px";

	timer = setInterval(loop, ptime);

	function loop(type) {
		if (type === 'right') {
			if (n === 0) {
				n = len - 1;
			}else {
				n--;
			}
			changeFocus(n);
			return;
		};
		if (n === len - 1) {
			n = 0;
		} else {
			n++;
		}
		changeFocus(n);
	};

	span.addEventListener('mouseover', function(e) {
		clearInterval(timer);
		var ev = e || window.event;
		var tag = ev.target || ev.srcElement;
		if (tag.tagName.toUpperCase() === "I") {
			changeFocus(parseFloat(tag.getAttribute("idx")));
			try {
				ev.stopPropagation();
			} catch (e) {
				ev.cancelBubble = true;
			}
		}
	},false);

	span.addEventListener('mouseout', function(e) {
		timer = setInterval(loop, ptime);
		var ev = e || window.event;
		var tag = ev.target || ev.srcElement;
		try {
			ev.stopPropagation();
		} catch (e) {
			ev.cancelBubble = true;
		}
	}, false);

	ele.addEventListener('touchstart', function (e) {
		e.stopPropagation();
		clearInterval(timer);
		this.currentPointX = e.touches[0].clientX;
		this.currentPointY = e.touches[0].clientY;
	}, false);

	ele.addEventListener('touchmove', function (e) {
        this.movedPointX = e.touches[0].clientX-this.currentPointX;
        this.movedPointY = e.touches[0].clientY-this.currentPointY;

        if (Math.abs(this.movedPointX) > 20) {
        	ev.stopPropagation()
        }
    }, false);

    ele.addEventListener('touchend', function (e) {
        timer = setInterval(loop, ptime);

        if (this.movedPointX > 40) { //向右滑动
        	loop('right');
        };
        if (this.movedPointX < 40) {//向左滑动
        	loop();
        }
        
          
    }, false);

	function changeFocus(idx) {
		n = idx;
		var b = span.getElementsByTagName("i");
		for (var i = 0; i < len; i++) {
			img[i].style.display = "none";
			b[i].className = "";
		}

		img[idx].style.display = "inline-block";
		b[idx].className = "current";
	};

})(document.getElementById("focus_img"))