<?php
/**
 * Created by PhpStorm.
 * User: zhan
 * Date: 2017/1/4
 * Time: 16:15
 */

namespace App\Entities;


use App\Err;
use App\Factory;
use Doctrine\ORM\EntityRepository;

class WechatAccountRepository extends EntityRepository
{
    /**
     * 添加一条微信账号
     * @param WechatAccount $WechatAccount
     * @return int
     */
    public function addWechatAccount($WechatAccount)
    {
        $em = Factory::em();
        $em->persist($WechatAccount);
        $em->flush();
        return $WechatAccount->id;
    }

    /**
     * 条件查询
     * @param array $where
     * @return array|mixed
     */
    public function getWechatAccountByWhere($where)
    {
        return WechatAccount::get($where);
    }

    /**
     * 根据openid，获取userId
     * @param string    $openid
     * @return int  用户id
     */
    public function openid2UserId($openid)
    {
        $where = [
            'openid' => $openid
        ];
        $queryRet = WechatAccount::get($where);
        Factory::logger('zhan')->addInfo(__CLASS__. '_' . __FUNCTION__, [__LINE__,
            'queryRet' => $queryRet
        ]);

        if (!empty($queryRet)) $queryRet = reset($queryRet);
        $userId = empty($queryRet['userId'])?0:$queryRet['userId'];
        return $userId;
    }

    /**
     * 修改微信账号数据
     * @param $where
     * @param $data
     * @return bool
     */
    public function updateWechatAccountByWhere($where, $data)
    {
        try {
            $updateRet = WechatAccount::update($where, $data);
            if (empty($updateRet)) throw new \Exception('修改WechatAccount表数据失败');
        } catch (\Exception $e) {
            Factory::logger('error')->addError(__CLASS__, [__FUNCTION__, __LINE__, $e, func_get_args()]);
            return Err::setLastErr(E_UPDATE_DATA_FAIL);
        }
    }
}