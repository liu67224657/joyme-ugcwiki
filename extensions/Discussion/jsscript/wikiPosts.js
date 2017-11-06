//回收站的还原操作
function reduction(){
    var json={};
    var checkon = new Array();
    $("input[name='one']:checked").each(function() {
        checkon.push($(this).val());
    });
    if(checkon !=''){
        json['id'] = checkon;
        var url = $("#url").val();
        if(confirm('确实要恢复选中数据吗?')){
            $.ajax({
                url:url+"?del=6",
                type:"GET",
                async: false,
                data:json,
                dataType: "jsonp",
                success:function(msg){
                    if(msg==1){
                        alert("恢复成功！");
                        location.reload()
                    }else{
                        alert("恢复失败！");
                    }
                }
            })
        }
    }else{
        alert("请先勾选将要恢复的数据");
    }
}
// 发图回调函数
function upImgCallback(data){
    var imgData = eval('('+data+')');
    if( imgData.result && imgData.result.rs == 0 ){
        $('#errMsg').html(imgData.result.msg);
        $('#errMsg').show();
    }else{
        $("#imageval").val(imgData.http_url);
        $('#addFriendshipLinks').show();
        $('#errMsg').hide();
    }
    $('#tips2').hide();
}

// 发表图片
$('#commentImg2').change(function(){
    var content = $("#commentImg2").val();
    if(content){
        $('#edittoken').val(mw.user.tokens.get('editToken'));
        $('#imgForm').submit();//function(){return false;}
        $('#tips2').show();
        $('#addFriendshipLinks').hide();
    }
});

function Operationdel(str){
    var json={};
    var checkon = new Array();
    $("input[name='one']:checked").each(function() {
        checkon.push($(this).val());
    });
    var url = $("#url").val();
    var newurl = url+"?del=";

    var tishi = "请先选择操作数据";
    var tiptop = '已有置顶数据，不能再执行此操作';
    var tipessence = '已有加精数据，不能再执行此操作';

    if(str ==3){
        for(var i=0;i<checkon.length;i++){
            var vthis=$("#"+checkon[i]);
            var top = vthis.prev().val();
            if(top == '1' || top == '2'){
                alert(tiptop);
                return false;
            }
        }
        var map = checkon.length>0 && checkon.length<=3;
        if(checkon.length==0){
            tishi = "请选择操作数据";
        }else{
            tishi = "请最多选择三项进行此操作";
        }
        var sendurl = newurl+3;
    }
    if(str==2){
        for(var i=0;i<checkon.length;i++){
            var vthis=$("#"+checkon[i]);
            var top = vthis.prev().val();
            if(top == '1' || top == '3'){
                alert(tipessence);
                return false;
            }
        }
        var map = checkon.length>0;
        var sendurl = newurl+2;
    }
    if(str==1){
        for(var i=0;i<checkon.length;i++){
            var vthis=$("#"+checkon[i]);
            var top = vthis.prev().val();
            if(top == '1' || top == '2'){
                alert(tiptop);
                return false;
            }
        }
        var map = checkon.length>0;
        var sendurl = newurl+1;
    }
    if(map){
        json['id'] = checkon;
        if(confirm('确实要执行此操作吗?')){
            $.ajax({
                url:sendurl,
                type:"GET",
                async: false,
                data:json,
                dataType: "jsonp",
                success:function(msg){
                    if(msg==1){
                        alert("设置成功！");
                        location.reload();
                    }else{
                        alert("设置失败！");
                    }
                }
            })
        }
    }else{
        alert(tishi);
        return false;
    }
};
function revmoeinfo(id){

    var url = $("#url").val();
    var sendurl = url+"?del=5";
    if(confirm('确实要删除吗?')){
        $.ajax({
            url:sendurl,
            type:"GET",
            async: false,
            data:{"id":id},
            dataType: "jsonp",
            success:function(msg){
                if(msg==1){
                    alert("设置成功！");
                    location.reload()
                }else{
                    alert("设置失败！");
                }
            }
        })
    }
};
var add={
    int:function(){
        add.show();
        add.addwiki();
    },
    show:function(){
        var settingBtn=$('.setting-btn');
        var floatWin=$('.float-win');
        var btnclose=$('.btn-close');
        settingBtn.on('click',function(){
            var checkon = new Array();
            var checkonid = new Array();

            $("span[id='linkF']").each(function() {
                checkon.push($(this).text());
            });

            $("span[id='linkF']").each(function() {
                checkonid.push($(this).next().val());
            });
            var str = '<span>';
            for(var i= 0;i<checkon.length;i++){

                str+= checkon[i]+"</span><cite onclick='revmoeinfo("+checkonid[i]+")'></cite></br>";
            }
            $(".add-list").html(str);
            floatWin.show();
        });
        btnclose.on('click',function(){
            $('#tips1').hide();
            $('#tips2').hide();
            floatWin.hide();
        });
    },

    addwiki:function(){
        var addbtn=$('.add-btn');
        var fwstatus=$('.fw-status');
        addbtn.on('click',function(){
            var val=$.trim($('.inp-text').val());
            var wikiname = $("#wikiname").val();

            if(val=='' || wikiname ==''){
                $('#tips1').show();
            }else{
                var text_wiki = $("#wikitext").val();
                var text_name = $("#wikiname").val();
                var url = $("#url").val();
                var floatWin=$('.float-win');

                var checkon = new Array();
                $("span[id='linkF']").each(function() {
                    checkon.push($(this).text());
                });

                if(checkon.length>=6){
                    alert("最多只能设置六个链接");
                    return false;
                }

                if(text_name.length >=13){
                    alert("wiki名称最多只限12个字");
                    return false;
                }

                var imagePath = $("#imageval").val();

                sendurl = url+"?del=4";;
                $.ajax({
                    url:sendurl,
                    type:"GET",
                    async: false,
                    data:{"text_wiki":text_wiki,"text_name":text_name,"filename":imagePath},
                    dataType: "jsonp",
                    beforeSend: function(){
                        var tishi = "<img src='"+url+"'+/resources/src/mediawiki.posts/images/loading.gif'>";
                        $('#tips2').show();
                        $('#tips1').hide();
                    },
                    success:function(msg){
                        $('#tips2').hide();
                        if(msg==1){
                            alert("设置成功！");
                            floatWin.hide();
                            location.reload()
                        }else{
                            alert("设置失败！");
                        }
                    }
                })
            };
        });
    }
};
add.int();

//动态选中状态
var reg2 = /(.*)[\?|&]type=.*/gi;
//当前页面的href
var href2 = window.location.href;
var myhref = reg2.exec(href2);

if(myhref){
    $("#tab_1").removeClass("cur");
    $("#tab_2").addClass("cur");
}else{
    $("#tab_2").removeClass("cur");
    $("#tab_1").addClass("cur");
}

$(".active").children().addClass("cur");
