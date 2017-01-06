<?php
/**
 * 上传文件处理
 */

namespace App\Entities;

use App\Factory;
use Doctrine\ORM\EntityRepository;


class UploadRepository extends EntityRepository
{
    /**
     * 将上传的文件信息存入到数据库中
     * @param $data
     * @param $userId
     * @return array|bool|mixed
     */
    public function addUpload($data, $userId)
    {
        $addData = $data;
        $addData['userId'] = $userId;
        $addData['postTime'] = date_create();
        $Upload = new Upload();
        foreach ($addData as $key => $value) {
            $Upload->$key = $value;
        }
        $em = Factory::em();
        $em->persist($Upload);
        $em->flush();
        $uploadId = $Upload->id;
        $fileInfo = self::getFileInfoById($uploadId);
        return $fileInfo;
    }

    /**
     * 查询一条文件信息
     * @param $id
     * @param array $show
     * @param array $hide
     * @return array|mixed
     */
    public function getFileInfoById($id, $show = [], $hide = [])
    {
        return Upload::getById($id, $show, $hide);
    }

    /**
     * 获取文件列表
     * @param $filter
     * @param array $where
     * @param array $orderBy
     * @param int $first
     * @param int $length
     * @return array
     */
    public function getFileInfoList(&$filter, $where = [], $orderBy = [], $first = 0, $length = 0)
    {
        return Upload::getList($filter, $where, $orderBy, $first, $length);
    }

    /**
     * 检测id对应文件是否存在
     * @param $id       integer
     * @return bool
     */
    public function checkFileExist($id)
    {
        $em = Factory::em();
        $queryRet = $em->createQueryBuilder()
            ->from(':Upload', 'u')
            ->select('u.id')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()->getArrayResult();
        return empty($queryRet)?false:true;
    }

    /**
     * 获取文件url访问路径
     * @param integer $id
     * @return string
     */
    public function getFileUrlPathById($id)
    {
        $localPath = Upload::getById($id, 'path');
        return self::spliceUrlPath($localPath);
    }

    /**
     * 获取文件绝对路径
     * @param integer $id
     * @return string
     */
    public function getFileAbsolutePathById($id)
    {
        $localPath = Upload::getById($id, 'path');
        return self::spliceAbsolutePath($localPath);
    }

    /**
     * 拼接url路径
     * @param $path
     * @return string
     */
    public function spliceUrlPath($path)
    {
        return Factory::getConfig('app', 'resUrl') . $path;
    }

    /**
     * 拼接绝对路径
     * @param $path
     * @return string
     */
    public function spliceAbsolutePath($path)
    {
        return WEBPATH . $path;
    }
}