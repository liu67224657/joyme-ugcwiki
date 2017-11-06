/**
 * Created by kexuedong on 2016/8/4.
 */
jQuery( document ).ready( function() {
    mw.ugcwikiutil.ensureDialog("该账号已与其他账号绑定",function (action) {
        if(action=="accept"){
            window.location.href = mediaWiki.config.get( 'wgServer' ) + "/home/index.php?title=%E7%89%B9%E6%AE%8A:%E8%B4%A6%E5%8F%B7%E5%AE%89%E5%85%A8";
        }
    });
} );