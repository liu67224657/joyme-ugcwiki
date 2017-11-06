var nowindex,wd_url = 'http://wiki.joyme.'+window.wgWikiCom+'/'+window.wgWikiname+'/index.php?action=ajax&rs=wfWikiDynamic';

var wikidynamic = {

    searchData:function(){

        $.getJSON(wd_url,{'page_type':$('#search_type option:selected').attr("value"),'day':$('#search_time option:selected').attr("value")},function(json){
            if(json['rs']==1){
                $('.section2').html(json['data']);
            }
        });
    }
}

jQuery( document ).ready( function() {

    jQuery( '#search_type' ).on( 'change', function() {
        wikidynamic.searchData();
    } );

    jQuery( '#search_time' ).on( 'change', function() {
        wikidynamic.searchData();
    } );
})