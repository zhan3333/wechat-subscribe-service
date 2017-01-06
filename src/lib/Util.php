<?php
/**
 * Created by PhpStorm.
 * User: 39096
 * Date: 2016/10/30
 * Time: 18:31
 */

namespace App;


class Util
{
    /**
     * 创建一个哈希值
     * @param $plain    string      待转换哈希值
     * @return bool|string
     */
    public static function createPasswd($plain)
    {
        return password_hash($plain, PASSWORD_BCRYPT);
    }

    /**
     * 验证字符串和哈希值
     * @param $plain    string  密码
     * @param $encrypt  string  哈希值
     * @return bool     是否相等
     */
    public static function verifyPasswd($plain, $encrypt)
    {
        return password_verify($plain, $encrypt);
    }

    /**
     * 返回指定长度的随机字符串
     * @param int $len          随机字符串长度
     * @param array $exclude    排除的字符数组
     * @return string   生成的随机字符串
     */
    public static function random($len = 8, $exclude = [])
    {
        static $initChars = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W',
            'X', 'Y', 'Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j',
            'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'
        ];
        if (!empty($exclude)) {
            $validChars = array_diff($initChars, $exclude);
        } else {
            $validChars = $initChars;
        }
        $result = '';
        for ($i = 1; $i <= $len; $i ++) {
            $result .= $validChars[array_rand($validChars, 1)];
        }
        return $result;
    }

    /**
     * 获取调用接口者的ip
     * @param array $server
     * @return string
     */
    public static function getClientIp($server = [])
    {
        $ip = '';
        if (empty($server)) $server = $_SERVER;
        if (!empty($server['REMOTE_ADDR'])) $ip = $server['REMOTE_ADDR'];
        if (!empty($server['HTTP_X_REAL_IP'])) $ip = $server['HTTP_X_REAL_IP'];
        return $ip;
    }

    /**
     * 获取带时差的date对象
     * @param null|integer $timestamp       时间戳
     * @param string $dz                    时差，默认为系统时差
     * @return \DateTime
     */
    public static function getDateObj($timestamp = null, $dz = APPTIMEZONE)
    {
        $dz = new \DateTimeZone($dz);
        if ($timestamp == null) {
            return date_create(null, $dz);
        } else {
            return date_create()->setTimestamp($timestamp)->setTimezone($dz);
        }
    }

    /**
     * 对象转为数组
     * @param $object
     * @return array
     */
    public static function obj2Arr($object)
    {
        $object = json_decode(json_encode($object), true);
        return $object;
    }

    /**
     * 检测手机号
     * @param $mobile
     * @return bool
     */
    public static function checkMobile($mobile)
    {
        return preg_match('/^(\+?86-?)?(18|15|13|17)[0-9]{9}$/', $mobile) ? true : false;
    }

    /**
     * 生成内部用订单号
     */
    public static function generateOrderNum()
    {
        $id = dk_get_next_id();
        return $id;
    }

    /**
     * 保存上传的文件
     * @return array|bool
     */
    public static function saveFile()
    {
        $file = $_FILES;
        if (empty($file)) return false;
        $fileInfoRet = [];
        Factory::logger('zhan')->addInfo(__CLASS__. '_' . __FUNCTION__, [__LINE__,
            $file
        ]);
        foreach ($file as $item) {
            $fileUpload = $item;
            $uploadCfg = Factory::getConfig('app', 'upload');
            $path = $uploadCfg['base_dir'];
            $mimeType = $fileUpload['type'];
            $fileSize = $fileUpload['size'];
            if (self::mime2Suffix($mimeType)) {
                $fileType = self::mime2Suffix($mimeType);   // 使用转换后的名称
            } else {
                $pos = stripos($fileUpload['name'], '.');
                if ($pos) ++$pos;
                $fileType = substr($fileUpload['name'], $pos);;  // 使用文件扩展名
            }
            //检查文件夹是否存在
            $subDir = date('Ym/d');
            if (!is_dir($path.$subDir) && (false === mkdir($path.$subDir, 0777, true))) return false;
            //如果已存在此文件，不断随机直到产生一个不存在的文件名
            $filename = self::randTime();
            $fullPath = $path.$subDir.DIRECTORY_SEPARATOR.$filename.'.'.$fileType;
            for (; is_file($fullPath);) {
                $filename = self::randTime();
                $fullPath = $path.$subDir.DIRECTORY_SEPARATOR.$filename.'.'.$fileType;
            }
            $result = [
                'size' => $fileSize,
                'suffix' => $fileType,
                'mime' => $mimeType,
                'name' => $filename.'.'.$fileType,
                'path' => "{$uploadCfg['base_url']}{$subDir}/{$filename}.{$fileType}",
                'fullPath' => $fullPath,
                'oldName' => $fileUpload['name']
            ];
            //移动文件

            $moveRet = move_uploaded_file($fileUpload["tmp_name"], $fullPath);
            if ($moveRet) {
                $fileInfoRet[] = $result;
            } else {
                $fileInfoRet[] = false;
            }
        }
        return $fileInfoRet;
    }

    /**
     * 将mime转换为正常的文件后缀名
     * @param $mime string
     * @return bool
     */
    public static function mime2Suffix($mime)
    {
        $data = [
            'audio/3gpp' => '3gpp',
            'video/3gpp' => '3gpp',
            'audio/ac3' => 'ac3',
            'allpication/vnd.ms-asf' => 'asf',
            'audio/basic' => 'au',
            'text/css' => 'css',
            'text/csv' => 'csv',
//            'application/msword' => false,  // doc or dot
            'application/xml-dtd' => 'dtd',
            'image/vnd.dwg' => 'dwg',
            'image/vnd.dxf' => 'dxf',
            'image/gif' => 'gif',
            'text/html' => false,  // html or htm
            'image/jp2' => 'jp2',
            'image/jpeg' => false, // jpeg or jpe or jpg
            'text/javascript' => 'js',
            'application/javascript' => 'js',
            'application/json' => 'json',
            'audio/mpeg' => false,  // mp2 ro mo3
            'video/mpeg' => false,  // mp2 or  mpeg or mpg
            'audio/mp4' => 'mp4',
            'application/vnd.ms-project' => 'mpp',
            'application/ogg' => 'ogg',
            'audio/ogg' => 'ogg',
            'application/pdf' => 'pdf',
            'image/png' => 'png',
            'application/vnd.ms-powerpoint' => false,   // pot or pps or ppt
            'application/rtf' => 'rtf',
            'text/rtf' => 'rtf',
            'image/vnd.svf' => 'svf',
            'image/tiff' => false,      // tiff or tif
            'text/plain' => 'txt',
            'application/vnd.ms-works' => false,    // wdb or wps
            'application/xhtml+xml' => 'xhtml',
            'application/vnd.ms-excel' => false,    // xlc or xlm or xls or xlt or xlw
            'text/xml' => 'xml',
            'application/xml' => 'xml',
            'aplication/zip' => 'zip',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/msword' => 'doc',
        ];
        if (!empty($data[$mime])) return $data[$mime];
        else return false;
    }

    /**
     * 按UNIX时间戳产生随机数
     *
     * @param $rand_length
     *
     * @return string
     */
    public static function randTime($rand_length = 6)
    {
        list($usec, $sec) = explode(" ", microtime());
        $min = intval('1'.str_repeat('0', $rand_length - 1));
        $max = intval(str_repeat('9', $rand_length));

        return substr($sec, -5).((int)$usec * 100).rand($min, $max);
    }
}