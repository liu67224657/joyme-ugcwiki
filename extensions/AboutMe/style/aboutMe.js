jQuery( document ).ready( function() {
    $('.del-all').click(function(){
        var even_type = $('#even_type').val();
        $('#'+even_type).addClass('on');
        mw.ugcwikiutil.confirmDialog('删除内容不可恢复,确认要清空所有吗？',function (action) {
            if(action=="accept"){
                if(window.wgUserId){
                    var url = $('#local_url').val();
                    jQuery.ajax({
                        "url": url,
                        "type": "post",
                        "async": false,
                        "data": {'even_type':even_type,'user_id':window.wgUserId,'confirm_delete':true},
                        "success": function(msg) {
                            mw.ugcwikiutil.msgDialog('清空成功');
                            location.reload();
                        }
                    })
                }
            }
        });
    });
});