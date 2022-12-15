<?php

/**
 * Class BigFileUploader
 *
 * 文件分片上传的服务端代码。
 * WARNING: 本代码仅用于功能展示，不可用于生产环境
 *
 * @author fising <fising@qq.com>
 * @license MIT
 */
class BigFileUploader
{
    /**
     * 根据错误代码获取错误信息
     *
     * @param int $code
     * @return string
     */
    public static function getErrorMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_OK:
            case UPLOAD_ERR_FORM_SIZE:
                return '文件块太大';
            case UPLOAD_ERR_PARTIAL:
                return '文件没有完整上传';
            case UPLOAD_ERR_NO_FILE:
                return '文件没有上传';
            case UPLOAD_ERR_NO_TMP_DIR:
                return '找不到临时文件夹';
            case UPLOAD_ERR_CANT_WRITE:
                return '文件写入失败';
            default:
                return '未知错误';
        }
    }

    /**
     * 保存上传的文件到一个目录
     *
     * @param string $dir
     * @param string $dest
     * @return string
     */
    public static function save($dir, $dest = '')
    {
        $index = isset($_POST['index']) ? intval($_POST['index']) : 0;
        $count = isset($_POST['count']) ? intval($_POST['count']) : 0;
        $sum = isset($_POST['sum']) ? trim($_POST['sum']) : '';
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';

        if ($index < 0 || $index >= $count || empty($sum) || empty($name)) {
            return json_encode([
                'status' => 1,
                'done'   => false
            ]);
        }

        if ($_FILES['data']['error'] > 0) {
            return json_encode([
                'status'  => $_FILES['data']['error'],
                'message' => static::getErrorMessage($_FILES['data']['error'])
            ], JSON_UNESCAPED_UNICODE);
        }
        
        $dest = empty($dest) ? $name : $dest;
        $dest = $dir . DIRECTORY_SEPARATOR . $dest;
        if (file_exists($dest)) {
            return json_encode([
                'status'  => 2,
                'message' => '同名文件已经存在'
            ]);
        }

        copy($_FILES['data']['tmp_name'], sys_get_temp_dir() . DIRECTORY_SEPARATOR . $sum . '-' . $index);

        if ($index + 1 == $count) {
            $fd = fopen($dest, 'x');
            if (false === $fd && !flock($fd, LOCK_EX)) {
                return json_encode([
                    'status'  => 1,
                    'message' => '打开文件失败'
                ]);
            }

            for ($i = 0; $i < $count; $i++) {
                $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $sum . '-' . $i;
                fwrite($fd, file_get_contents($tmp));
                unlink($tmp);
            }

            flock($fd, LOCK_UN);
            fclose($fd);
        }

        return json_encode([
            'status' => 0,
            'done'   => $index + 1 == $count
        ]);
    }
}
