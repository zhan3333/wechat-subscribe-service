<?php
/**
 * 提供文件管理接口
 * User: zhan
 * Date: 2017/1/6
 * Time: 12:02
 */

namespace App\Service;
use App\Err;
use App\RepositoryClass;
use App\Util;

/**
 * Class File
 * @package App\Service
 * @default enable
 */
class File extends Base
{
    /**
     * 上传文件操作
     * @default enable
     * @return array|bool
     */
    public static function uploadFile()
    {
        if (empty($_FILES)) return Err::setLastErr(E_NO_FILE_UPLOAD);  // 未上传文件
        $userId = self::getClientUserId();
        $saveFileRet = Util::saveFile();
        $saveDbRet = [];
        foreach ($saveFileRet as $item) {
            if ($item) {
                $saveDbRet[] = RepositoryClass::Upload()->addUpload($item, $userId);
            } else {
                $saveDbRet[] = false;
            }
        }
        return [
            'fileInfoList' => $saveDbRet
        ];
    }

    /**
     * 根据文件id，获取文件信息
     * @default enable
     * @param integer $id 文件id
     * @return array
     * <pre>
     * [
     *  'result' => [
     *      'id' => 0,                  // 文件id
     *      'name' => '',               // 文件名称
     *      'suffix' => '',             // 文件格式
     *      'path' => '',               // 文件存储路径
     *      'userId' => 0,              // 上传文件用户id
     *      'postTime' => '',           // 上传时间
     *      'oldName' => '',            // 上传文件时名称
     *      'urlPath' => '',            // 通过url访问链接
     *      'absolutePath' => ''        // 文件绝对路径
     *  ]
     * ]
     * </pre>
     */
    public static function getFileInfoById($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (empty($id)) return Err::setLastErr(E_PARAM_ERROR);
        $result = RepositoryClass::Upload()->getFileInfoById($id);
        if (!empty($result['path'])) {
            $path = $result['path'];
            $result['urlPath'] = RepositoryClass::Upload()->spliceUrlPath($path);
            $result['absolutePath'] = RepositoryClass::Upload()->spliceAbsolutePath($path);
        }
        return [
            'result' => $result
        ];
    }

    /**
     * 获取文件列表
     * @default enable
     * @param array $where
     * @param array $orderBy
     * @param int $first
     * @param int $length
     * @return array
     * <pre>
     * [
     *  'table' => [
     *      'data' => [
     *          [
     *              'id' => 0,                  // 文件id
     *              'name' => '',               // 文件名称
     *              'suffix' => '',             // 文件格式
     *              'path' => '',               // 文件存储路径
     *              'userId' => 0,              // 上传文件用户id
     *              'postTime' => '',           // 上传时间
     *              'oldName' => '',            // 上传文件时名称
     *              'urlPath' => '',            // 通过url访问链接
     *              'absolutePath' => ''        // 文件绝对路径
     *          ],
     *          ...
     *      ],
     *      'filter' => 0
     *  ]
     * ]
     * </pre>
     */
    public static function getFileInfoList($where = [], $orderBy = [], $first = 0, $length = 0)
    {
        $filter = 0;
        $result = RepositoryClass::Upload()->getFileInfoList($filter, $where, $orderBy, $first, $length);
        if (!empty($result)) {
            foreach ($result as &$item) {
                if (empty($item['path'])) continue;
                $path = $item['path'];
                $item['urlPath'] = RepositoryClass::Upload()->spliceUrlPath($path);
                $item['absolutePath'] = RepositoryClass::Upload()->spliceAbsolutePath($path);
            }
        }
        return [
            'table' => [
                'data' => $result,
                'filter' => $filter
            ]
        ];
    }
}