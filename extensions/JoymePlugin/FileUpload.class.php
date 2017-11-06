<?php

use Joyme\qiniu\conf as qiniu_utils;
use Joyme\qiniu\Qiniu_RS_PutPolicy;
use Joyme\qiniu\Qiniu_PutExtra;

class FileUpload {

    protected $_max_size = 2097152; // 最大上传字节
    protected $_allowedTypes = 'jpg|png|gif|jpeg|rar|txt|doc|csv|docx'; // 允许上传的文件类型
    protected $_file_path = '';  // 上传文件保存路径
    protected $_file_name = '';  // 上传文件名（新文件名）
    protected $_encrypt_name = true;  // 是否重命名
    protected $_file_path_pre = '';  // 上传到七牛的一级路径，区分项目用
    protected $_errorInfo; // 错误信息
    protected $_uploadInfo; // 上传数组$_FILES['field']的拷贝
    protected $_cloud_storage = true;  // 开启云存储

    public function __construct(array $params = array()) {
        $this->init($params);
    }

    /**
     * 初始化，偏好设置
     * @param array $params
     */
    public function init(array $params = array()) {
        if (count($params) > 0) {
            foreach ($params as $key => $val) {
                $key = '_' . $key;
                $this->$key = $val;
            }
        }
    }

    /**
     * 文件上传
     * 上传失败，返回false，可以通过errmsg()获取错误信息
     * 上传成功，返回上传文件信息数组：
     * 		array(
     * 			'file_ext' 	=> '文件后缀',
     * 			'file_path'	=> '不包括文件名的文件绝对路径，在sae中为空',
     * 			'sae_storage_domain' => 'sae storage domain',
     * 			'file_size'	=> '文件大小',
     * 			'orig_name' => '上传的文件最初的文件名，原始文件名',
     * 			'file_name' => '已上传的文件名（包括扩展名），新文件名',
     * 			'full_path' => '包括文件名在内的文件绝对路径',
     * 			'http_url'  => '上传文件的URL地址'
     *  	) 
     * @param string $field 上传域
     * @return flase/array()  
     */
    public function upload($field) {
        $this->_file_name = '';
        if (!isset($_FILES[$field])) { // 未找到文件域
            $this->_errorInfo = $field . 'Does not exist';
            return FALSE;
        }

        // 检测上传文件大小
        if ($_FILES[$field]['size'] > $this->_max_size) {
            $this->_errorInfo = 'File size can not exceed ' . $this->_max_size;
            return FALSE;
        }

        $this->_uploadInfo = $_FILES[$field];

        $orig_name = $this->_uploadInfo['name']; // 上传文件名（原始）
        $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION)); // 上传文件后缀
        // 检测文件是否允许上传
        if (!$this->_allowedType($ext)) {
            $this->_errorInfo = '图片格式不允许上传';
            return FALSE;
        }

        // 上传成功
        if ($this->_uploadInfo['error'] == UPLOAD_ERR_OK) {
            // 不允许重命名，则使用上传文件名	
            if (!$this->_encrypt_name) {
                $this->_file_name = $orig_name;
            }
            // 允许重命名，且用户未指定文件名，则使用系统生成的随机文件名
            else if ($this->_encrypt_name && $this->_file_name == '') {
                $this->_file_name = $this->_genFilename() . '.' . $ext;
            }

            $arr = array();
            $arr['file_ext'] = $ext;      // 文件后缀
            $arr['file_path'] = $this->_file_path; // 不包括文件名的文件绝对路径
            $arr['file_size'] = $this->_uploadInfo['size']; // 文件大小	
            $arr['orig_name'] = $orig_name;     // 上传的文件最初的文件名，原始文件名
            $arr['file_name'] = $this->_file_name;     // 已上传的文件名（包括扩展名），新文件名
            if ($this->_cloud_storage) {
                return $this->_cloudStorage($arr);
            } else {
                return $this->_localStorage($arr);
            }
        }
        // 上传失败
        else {
            $this->_errorInfo = $this->_getError();
            return FALSE;
        }
    }

    /**
     * 云存储移动上传文件
     * @param array $arr
     */
    protected function _cloudStorage(array $arr) {
        global $wgQiNiuPath;
        global $wgQiNiuBucket;
        global $wgQiNiuAccessKey;
        global $wgQiNiuSecretKey;
        // 如果没有指定上传目录，则使用系统默认目录
        if (empty($arr['file_path'])) {
            $time = time();
            $arr['file_path'] = $this->_file_path_pre . '/' . date('Y', $time) . '/' . date('md', $time);
        }
        $arr['file_path'] = str_replace('\\', '/', $arr['file_path']);
        $arr['full_path'] = $arr['file_path'] . '/' . $arr['file_name']; // 包括文件名在内的文件绝对路径
        $arr['http_url'] = 'http://' . $wgQiNiuPath . '/' . $arr['file_path'] . '/' . $arr['file_name'];
        qiniu_utils::Qiniu_SetKeys($wgQiNiuAccessKey, $wgQiNiuSecretKey);
        $putPolicy = new Qiniu_RS_PutPolicy($wgQiNiuBucket);
        $upToken = $putPolicy->Token(null);
        $putExtra = new Qiniu_PutExtra();
        $putExtra->Crc32 = 1;
        list($ret, $err) = qiniu_utils::Qiniu_PutFile($upToken, $arr['full_path'], $this->_uploadInfo['tmp_name'], $putExtra);

        if ($err !== null) {
            $this->_errorInfo = $err;
            return array();
        } else {
            return $arr;
        }
    }

    /**
     * 本地存储移动上传文件
     * @param array $arr
     */
    protected function _localStorage(array $arr) {
        $this->_errorInfo = '暂不支持本地存储';
        return FALSE;
    }

    /**
     * 移动上传文件  该函数停用
     * @param array $arr
     */
    protected function _mv(array $arr) {
        // 如果没有指定上传目录，则使用系统默认目录
        if (empty($arr['file_path'])) {
            $time = time();
            $arr['file_path'] = ROOT . conf('xxoo', 'upload_dir') . DS . date('Y', $time) . DS . date('md', $time);
        }

        $ROOT = str_replace('\\', '/', ROOT);
        $arr['file_path'] = str_replace('\\', '/', $arr['file_path']);

        $arr['full_path'] = $arr['file_path'] . '/' . $arr['file_name']; // 包括文件名在内的文件绝对路径
        $arr['http_url'] = base_url() . trim(str_ireplace($ROOT, '', $arr['file_path']), '/') . '/' . $arr['file_name'];
        // 创建上传目录
        if (!mkdirs($arr['file_path'])) {
            $this->_errorInfo = 'Create upload directory failed';
            return FALSE;
        }

        // 移动文件失败
        if (!@move_uploaded_file($this->_uploadInfo['tmp_name'], $arr['full_path'])) {
            $this->_errorInfo = 'Can not move uploaded file';
            return FALSE;
        }
        return $arr;
    }

    /**
     * 获取上传错误信息
     */
    public function errmsg() {
        return $this->_errorInfo;
    }

    /**
     * 检测文件是否为允许上传的文件类型
     * @param string $ext
     * @return boolean
     */
    protected function _allowedType($ext) {
        return in_array($ext, explode('|', $this->_allowedTypes));
    }

    /**
     * 产生文件名
     * @return string
     */
    protected function _genFilename() {
        $str = time();
        while (strlen($str) < 15) {
            $str .= mt_rand(0, mt_getrandmax());
        }
        return $str;
    }

    /**
     * 根据上传错误号，生成错误信息
     * @return string
     */
    protected function _getError() {
        switch ($this->_uploadInfo['error']) {
            case UPLOAD_ERR_INI_SIZE :
                $error = 'File exceeds the maximum upload limit';
                break;
            case UPLOAD_ERR_PARTIAL :
                $error = 'File upload unfinished';
                break;
            case UPLOAD_ERR_NO_FILE :
                $error = 'No file was uploaded';
                break;
            case UPLOAD_ERR_NO_TMP_DIR :
                $error = 'No temporary files';
                break;
            case UPLOAD_ERR_CANT_WRITE :
                $error = 'File write failed';
                break;
            case UPLOAD_ERR_FORM_SIZE :
                $error = 'Upload file size exceeds the specified size';    // form中指定的大小
                break;
            default :
                $error = 'An unknown error';
        }
        return $error;
    }

}
