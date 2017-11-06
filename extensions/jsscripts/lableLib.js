//标签库
$('#category_labels_content td').css({'padding':'10px'});
$('#choice_labels').css({'width':'762px','height':'34px','background-color':'#FFFFFF','line-height':'34px','border':'1px'});
$('#category_labels_content').find('td').bind('click', function(){
    var code = $(this).attr('code');
    //判断是否已经添加
    if($('#'+code).html()==undefined){
        $(this).css({'class':'cur','color':'#C0C0C0'});
        $('.choice_labels_content').html($('.choice_labels_content').html()+' '+'<span style="cursor:pointer;" id="'+code+'">'+$(this).html()+'</span>');
        $('#choice_labels').find('span').bind('click', function(){
            var zcode = $(this).attr('id');
            $('span[id='+zcode+']').remove();
            $('.lable-'+zcode).css({'class':'','color':''});
        });
    }
});

//添加至文本域
$('#wpSave').click(function(){

    if($('#choice_labels').html()!='') {
        var contentHtml = $('#wpTextbox1').html();
        var lableHtml = '';
        spanHtml = document.getElementById("choice_labels").getElementsByTagName("span");
        for (var i = 0; i < spanHtml.length; i++) {
            lableHtml += '  [[Category:'+spanHtml[i].id+']]';
        }
        $('#wpTextbox1').html(contentHtml + lableHtml);
    }
})
