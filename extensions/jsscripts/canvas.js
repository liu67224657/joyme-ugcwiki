//RadarChart 类
var RadarChart = function(canvasId){
	var msg = document.getElementById(canvasId).getAttribute("data-msg");
	var val = document.getElementById("joyme_canvas").getAttribute("data-val").split("_");
	this.backcolor = document.getElementById(canvasId).getAttribute("data-bgcolor");
	this.datacolor = document.getElementById(canvasId).getAttribute("data-color");
	var size = document.getElementById(canvasId).getAttribute("data-size");
	var data = new Array();
	var msg_arr = msg.split("|");
	for(var i=0;i<msg_arr.length;i++){
		var cell_msg_arr = msg_arr[i].split("_");
		data.push({"value":parseInt(cell_msg_arr[0]), "title":cell_msg_arr[1]});
	}
	var data_val = new Array();
	for(var i=0;i<val.length;i++){
		data_val.push({"value":parseInt(val[i])});
	}
    this.ctx = document.getElementById(canvasId).getContext('2d');
    this.data = data;
	this.data_val = data_val;
    this.num = this.data.length;
    this.dataForDraw = [];
	this.dataValForDraw = [];
    this.size=size;
    this.center=size/2;
};
RadarChart.prototype.draw=function(){
    //清除
    this.ctx.clearRect(0,0,this.size,this.size);
    // 开始绘制
    this.dataForDraw = this.getDataForDraw(this.data);
    this.drawBackground();
	this.dataValForDraw = this.getDataForDraw(this.data_val);
    this.drawData();
};
RadarChart.prototype.getDataForDraw=function(data){
    var _phi = 2 * Math.PI / this.num;
    //this.dataForDraw = this.data;
	var datas = data;
    for (var i = 0; i < this.num; i++) {
        datas[i].phi = Math.PI / 2 * 3 +_phi * i;
    };
	return datas;
};
RadarChart.prototype.drawBackground=function(){
    //绘制背景
    var maxR= this.center - 20;
    //设置线宽
    this.ctx.lineWidth = 2;
    //设置颜色
    this.ctx.strokeStyle="#666";
    //设置字体样式
    this.ctx.font = "13px serif";
    //绘制射线及文字标注
	this.ctx.beginPath();
    for (var i = 0,j; j = this.dataForDraw[i]; i++) {
        this.ctx.moveTo(this.center, this.center);
        this.ctx.lineTo(this.center + this.getX(maxR,j.phi), this.center + this.getY(maxR,j.phi));
        this.ctx.fillText(j.title,
        this.center + this.getX(maxR + 14,j.phi) - 13,
        this.center + this.getY(maxR + 14,j.phi) + 5);

    }
	this.ctx.closePath();
	this.ctx.fill();
	this.ctx.stroke();

    // 绘制蜘蛛网
    var level=5;
    for (var k = 1; k <= level; k++) {
		this.ctx.fillStyle ='rgba('+this.backcolor+')';//填充红色，半透明
		this.ctx.beginPath();
        this.ctx.moveTo(this.center + this.getX(maxR/level*k,this.dataForDraw[this.num-1].phi) ,
            this.center + this.getY(maxR/level*k,this.dataForDraw[this.num-1].phi));

        for (var i = 0, j; j = this.dataForDraw[i]; i++) {
			this.ctx.lineTo(this.center + this.getX(maxR/level*k,j.phi) , this.center + this.getY(maxR/level*k,j.phi));
        }
		this.ctx.closePath();
		this.ctx.fill();
		this.ctx.stroke();

    };

};

RadarChart.prototype.drawData=function(){
    // 绘制数据
    // 获取 最大 半径
    var scale = this.dataForDraw[0].value;
    for (var i = 1, j; j = this.dataForDraw[i]; i++) {
        if (j.value > scale) {
            scale = j.value;
        }
    }
    scale =  (this.center - 20) / scale;
	this.ctx.fillStyle ='rgba('+this.datacolor+')';
    this.ctx.beginPath();
    this.ctx.lineWidth = 1;
    this.ctx.strokeStyle = "#007FFF";
//////////////////////////////////////////////////////
    this.ctx.moveTo(this.center + Math.cos(this.dataValForDraw[this.num-1].phi) * this.dataValForDraw[this.num-1].value * scale, this.center + Math.sin(this.dataValForDraw[this.num-1].phi) * this.dataValForDraw[this.num-1].value * scale);
    for (var i = 0, j; j = this.dataValForDraw[i]; i++) {
        this.ctx.lineTo(this.center + Math.cos(j.phi) * j.value * scale, this.center + Math.sin(j.phi) * j.value * scale);
    }
    this.ctx.closePath();
	this.ctx.fill();
    this.ctx.stroke();
};
RadarChart.prototype.getX=function(r,phi){
    return Math.cos(phi) * r;
};
RadarChart.prototype.getY=function(r,phi){
    return Math.sin(phi) * r;
};

$(function(){
	$('.canvas').each(function(i,v){
		var radarTest = new RadarChart($(v).attr('id'));
		radarTest.draw();
	});
	
});

