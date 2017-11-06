$(document).ready(function() {

    $('#createwikireturn').click(function(){

        var url = $('#createwiki-wiki-return').val();
        window.location.href = url;
    });

    var reporturl = 'http://webcache.joyme.'+window.wgWikiCom+'/wiki/title/report.do';

    $("#create_wiki").validate({
        rules: {
            wiki_name: {
                required: true,
                maxlength:30
            },
            wiki_key:{
                required: true,
                minlength: 2,
                maxlength:20,
                remote: {
                    url: "index.php?action=ajax&rs=wfCreateWikiCheckWikiKey",
                    type: "get",
                    dataType: "json",
                    data:{
                        wiki_key: function(){
                            return $("#wiki_key").val();
                        },
                        random_key:function(){
                            return Math.random();
                        }
                    },
                    dataFilter: function (data) {　　　　//判断控制器返回的内容
                        var json = eval('(' + data + ')');
                        if (json['rs'] == 0) {
                            return true;
                        }
                        else {
                            return false;
                        }
                    }
                },
                levelLimit:true
            },
            wiki_icon: {
                required: true,
                checkImage:true
            },
            create_skins_type:{
                required: true
            },
            create_reason:{
                required: true
            },
            wiki_title:{
                required: true,
                maxlength:50
            },
            wiki_keywords:{
                required: true,
                maxlength:200
            },
            wiki_description:{
                required: true,
                maxlength:200
            }
        },
        messages:{
            wiki_name: {
                required: "请填写wiki名称",
                maxlength: "wiki名称不能大于30个字符"
            },
            wiki_key:{
                required: "请填写wiki key",
                minlength: "wiki key不能小于2个字符",
                maxlength:"wiki key不能大于20个字符",
                remote: "wiki key已存在"
            },
            wiki_icon:{
                required: "请填写wiki icon"
            },
            create_skins_type:{
                required: "请选择皮肤类型"
            },
            create_reason:{
                required: "请选择创建理由"
            },
            wiki_title:{
                required: "请填写wiki title",
                maxlength:"title不能大于150个字符"
            },
            wiki_keywords:{
                required: "请填写wiki keywords",
                maxlength:"keywords不能大于600个字符"
            },
            wiki_description:{
                required: "请填写wiki description",
                maxlength:"description不能大于600个字符"
            }
        },
        submitHandler:function(form){
            jQuery(form).ajaxSubmit({
                beforeSend: function() {
                    $('#loading').html('正在处理中...');
                    $('#back_list').attr("target",'_blank');
                    $('#submit-go').attr("disabled", true);
                },
                success: function(msg) {
                    $('#loading').hide();
                    var data = eval('(' + msg + ')');
                    if(data['rs']==0){
                        $.ajax({
                            url: reporturl,
                            type: "post",
                            async: false,
                            data: {'wikikey':$("#wiki_key").val(),'wikiname':$("#wiki_name").val()},
                            dataType: "jsonp",
                            jsonpCallback: "reportcallback",
                            success: function (msg) {
                                console.log('report ok!'+$("#wiki_key").val()+' name: '+$('#wiki_name').val());
                            }
                        });
                        alert('创建成功');
                        var url = $('#createwiki-wiki-return').val();
                        window.location.href = url;
                    }else{
                        alert(data['message']);
                        $('#submit-go').attr("disabled",false);
                        return false;
                    }
                }
            });
        }
    });
    //自定义wiki key验证方法
    jQuery.validator.addMethod("levelLimit",function(value, element){
        return this.optional(element) || /^[a-zA-Z][a-zA-Z0-9]*$/.test(value);
    },"wiki key 只能是数字字母，并且不能以数字开头!");
    jQuery.validator.addMethod("checkImage",function(value, element){
        return this.optional(element) || /^(\s|\S)+(png)+$/.test(value);
    },"请上传完整的png格式的图片连接");
});