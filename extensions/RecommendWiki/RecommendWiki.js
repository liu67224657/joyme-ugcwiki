document.domain='wiki.joyme.'+window.wgWikiCom;
function upImgCallback(data){
    var imgData = eval('('+data+')');
    if( imgData.result && imgData.result.rs == 0 ){
        $('#errMsg').html(imgData.result.msg);
        $('#errMsg').show();
    }else{
        $("#imageval").val(imgData.http_url);
        console.log(imgData.http_url);
        $('#errMsg').hide();
    }
}
	
$('#commentImg').change(function(){
    var content = $("#commentImg").val();
    if(content){
        $('#edittoken').val(mw.user.tokens.get('editToken'));
        $('#imgForm').submit();
    }
});