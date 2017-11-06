(function () {
	var a = document.getElementById('actor-tab-menu').getElementsByTagName('span');
	var b = document.getElementById('actor-tab-box').getElementsByTagName('table');
	b[0].style.display = 'table';
	
	for (var i=0, l=a.length; i<l; i++) {
		a[i].idx = i;
		a[i].onmouseover = function () {
			setTab(this.idx);
		}
	};
	function setTab (idx) {
		for (var i=0, l=a.length; i<l; i++) {
			a[i].className = a[i].className.replace(' current', '');
			b[i].style.display = 'none';
		};
		a[idx].className += ' current';
		b[idx].style.display = 'table';
	};
})()