/**
 * Created by kexuedong on 2016/7/19.
 */
jQuery(document).ready(function () {

    //上传头像
    jQuery('.uploadicon').click(function () {
        var usericon = jQuery('#usericon').val();
        if (!usericon) {
            mw.ugcwikiutil.msgDialog('请先上传头像');
            return false;
        }
        var usericonwidth = $("#usericonwidth").val();
        var usericonheight = $("#usericonheight").val();
        if (usericonwidth < 150) {
            mw.ugcwikiutil.msgDialog('图片宽度不能小于150px');
            return false;
        }
        if (usericonheight < 150) {
            mw.ugcwikiutil.msgDialog('图片高度不能小于150px');
            return false;
        }
        jQuery.post(
            mediaWiki.util.wikiScript(), {
                action: 'ajax',
                rs: 'wfUserUploadIcon',
                rsargs: [usericon]
            },
            function (data) {
                var res = jQuery.parseJSON(data);
                if (res.rs == '1') {
                    mw.ugcwikiutil.msgDialog(res.data, '');
                    window.setTimeout(function () {
                        location.reload();
                    }, 3000);
                } else {
                    mw.ugcwikiutil.msgDialog(res.data);
                }
            }
        );
    });

    function avatarupload() {
        var Qiniu = new QiniuJsSDK();
        var uploader = Qiniu.uploader({
            runtimes: 'html5,html4',      // 上传模式，依次退化
            browse_button: 'file',         // 上传选择的点选按钮，必需
            unique_names: false,
            uptoken: $('#uptoken').val(), // uptoken是上传凭证，由其他程序生成
            //get_new_uptoken: false,             // 设置上传文件的时候是否每次都重新获取新的uptoken
            domain: 'http://' + $('#qiniu_domain').val(),     // bucket域名，下载资源时用到，必需
            container: 'uploadAvatarBtn',             // 上传区域DOM ID，默认是browser_button的父元素
            flash_swf_url: '',  //引入flash,相对路径
            max_file_size: '2mb',             // 最大文件体积限制
            max_retries: 3,                     // 上传失败最大重试次数
            dragdrop: false,                     // 开启可拖曳上传
            drop_element: 'message',          // 拖曳上传区域元素的ID，拖曳文件或文件夹后可触发上传
            chunk_size: '2mb',                  // 分块上传时，每块的体积
            auto_start: true,                   // 选择文件后自动上传，若关闭需要自己绑定事件触发上传
            multi_selection: false,
            filters: {
                // max_file_size : '2mb',
                prevent_duplicates: true,
                // Specify what files to browse for
                mime_types: [
                    {title: "Image files", extensions: "jpg,jpeg,png,bmp"}, // 限定jpg,gif,png后缀上传
                ]
            },
            init: {
                'FilesAdded': function (up, files) {
                    plupload.each(files, function (file) {
                        files.splice(1, 1);
                        // 文件添加进队列后，处理相关的事情
                        console.log('file add');
                    });
                },
                'BeforeUpload': function (up, file) {

                },
                'UploadProgress': function (up, file) {
                    // 每个文件上传时，处理相关的事情
                },
                'FileUploaded': function (up, file, info) {
                    // 每个文件上传成功后，处理相关的事情
                    var domain = up.getOption('domain');
                    var res = Qiniu.parseJSON(info);
                    var sourceLink = domain + '/' + res.key + '?imageMogr2/auto-orient';

                    var img = new Image();
                    img.src = sourceLink;
                    img.onload = function () {
                        $("#usericonwidth").val(img.width);
                        $("#usericonheight").val(img.height);
                        if (img.width < 150) {
                            mw.ugcwikiutil.msgDialog('图片宽度不能小于150px');
                            return false;
                        } else if (img.height < 150) {
                            mw.ugcwikiutil.msgDialog('图片高度不能小于150px');
                            return false;
                        } else {
                            $("#file_prev").attr('src', sourceLink);
                            $("#usericon").val(sourceLink);
                        }
                    };

                },
                'Error': function (up, err, errTip) {
                    if (err.code == '-601') {
                        mw.ugcwikiutil.msgDialog('请选择指定图片格式的文件');
                    }
                    else if (err.code == '-602') {
                        mw.ugcwikiutil.msgDialog('不能上传同一张图片');
                    }
                    else {
                        mw.ugcwikiutil.msgDialog(errTip);
                    }
                    return false;
                },
                'UploadComplete': function () {
                    //队列文件处理完毕后，处理相关的事情
                },
                'Key': function (up, file) {
                    // 若想在前端对每个文件的key进行个性化处理，可以配置该函数
                    // 该配置必须要在unique_names: false，save_key: false时才生效
                    var type = file.name.substring(file.name.lastIndexOf('.'), file.name.length).toUpperCase();
                    var myDate = new Date();
                    var key = "wiki/" + myDate.getFullYear() + myDate.getMonth() + myDate.getDate() + '/' + myDate.getTime() + Math.floor(Math.random() * 1000) + type;
                    // do something with key here
                    return key;
                }
            }
        });
    }

    avatarupload();
});
