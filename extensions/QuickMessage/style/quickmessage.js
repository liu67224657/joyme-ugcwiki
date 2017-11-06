$(document).ready(function() {

    $('#createwikireturn').click(function(){

        var url = $('#createwiki-wiki-return').val();
        window.location.href = url;
    });

    //create page validation
    $("#quickmessage").validate({
        rules: {
            user_message_theme: {
                required: true,
                maxlength:40
            },
            quickmessage_content_format:{
                required: true,
                maxlength:2000
            },
            quickmessage_upload_file:{
                required: true
            }
        },
        messages:{
            user_message_theme: {
                required: "请填写主题名称",
                maxlength: "主题名称不能大于40个字符"
            },
            quickmessage_content_format:{
                required: "请填内容格式",
                maxlength: "内容格式不能大于2000个字符"
            },
            quickmessage_upload_file:{
                required: ""
            }
        }
    });

    $('#quickmessageStartTime').datepicker({
        language: "ch",           //语言选择中文
        format: 'yyyy-mm-dd',      //格式化日期
        orientation: "bottom left"
    });

    $('#quickmessageEndTime').datepicker({
        language: "ch",           //语言选择中文
        format: 'yyyy-mm-dd',      //格式化日期
        orientation: "bottom left"
    });

    //详情重发功能
    $('.quickmessage_item_agin_send_button').click(function(){
        var umi_id = $(this).attr('item_data');
        var umi_status = $(this).attr('status_flag');
        var send_um_id = $(this).attr('send_um_id');
        if(umi_id){
            jQuery.post(
                mw.util.wikiScript(), {
                    action: 'ajax',
                    rs: 'wfQuickMessageAgainSend',
                    rsargs: [umi_id,umi_status,send_um_id]
                },
                function( data ) {
                    var res = jQuery.parseJSON(data);
                    if (res.rs == '1'){
                        alert('重发成功!');
                        $('#'+umi_id).html(res.data.time);
                        if(parseInt(umi_status) == 2){
                            $('#status'+umi_id).html('成功');
                        }
                    }else{
                        mw.hook( 'postEdit' ).fire( {
                            message: '重发失败'
                        });
                    }
                }
            );
        }
    })
});