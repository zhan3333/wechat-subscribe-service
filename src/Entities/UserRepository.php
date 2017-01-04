<?php
/**
 * Created by PhpStorm.
 * User: zhan
 * Date: 2016/11/24
 * Time: 17:29
 */

namespace App\Entities;


use App\Err;
use App\Factory;
use App\RepositoryClass;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * @param $data
     * <pre>
     * [
     *  'userType' => 0,        // 用户类型
     *  'accountType' => 0,     // 账号类型
     * ]
     * </pre>
     * @return bool
     */
    private function addUser($data)
    {
        if (empty($data['userType'])) $data['userType'] = User::USER_TYPE_NORMAL;
        if (empty($data['accountType'])) $data['accountType'] = User::ACCOUNT_TYPE_NORMAL;
        $em = Factory::em();
        $User = new User();
        $User->setPostTime(date_create());
        $User->setUserType($data['userType']);
        $User->setAccountType($data['accountType']);
        if (!empty($data['createUserId'])) {
            $User->setCreateUserId($data['createUserId']);
        }
        $em->persist($User);
        $em->flush();
        return $User->getId();
    }

    /**
     * 创建一个普通账户
     * @param $account
     * @param $passwd
     * @return array
     * <pre>
     * // 成功时返回
     * [
     *  'ok' => true,
     *  'userId' => 1000        // 用户id
     * ]
     * // 失败时返回
     * [
     *  'ok' => false,
     *  'code' => 9,            // 错误代码
     * ]
     * </pre>
     */
    public function normalReg($account, $passwd)
    {
        $em = Factory::em();
        $em->beginTransaction();
        try {
            $userId = self::addUser([
                'userType' => User::USER_TYPE_NORMAL,
                'accountType' => User::ACCOUNT_TYPE_NORMAL
            ]);
            if (empty($userId)) throw new \Exception('创建用户失败', E_USER_CREATE_FAIL);
            $normalAccountId = RepositoryClass::NormalAccount()->addNormalAccount([
                'userId' => $userId,
                'passwd' => $passwd,
                'account' => $account
            ]);
            if (empty($normalAccountId)) throw new \Exception('创建用户账号失败', E_USER_CREATE_NORMAL_ACCOUNT_FAIL);
            $em->commit();
            return true;
        } catch (\Exception $e) {
            $em->rollback();
            Factory::logger('error')->addError(__CLASS__, [__FUNCTION__, __LINE__, $e, func_get_args()]);
            return Err::setLastErr($e->getCode());
        }
    }

    /**
     * 创建一个管理员账户
     * @param $account
     * @param $passwd
     * @param array $info
     * @return bool
     */
    public function addAdmin($account, $passwd, $info = [])
    {
        $em = Factory::em();
        $em->beginTransaction();
        try {
            $addUserData = $info;
            $info['userType'] = User::USER_TYPE_ADMIN;
            $info['accountType'] = User::ACCOUNT_TYPE_NORMAL;
            $userId = self::addUser($addUserData);
            if (empty($userId)) throw new \Exception('创建用户失败', E_USER_CREATE_FAIL);
            $normalAccountId = RepositoryClass::NormalAccount()->addNormalAccount([
                'userId' => $userId,
                'passwd' => $passwd,
                'account' => $account
            ]);
            if (empty($normalAccountId)) throw new \Exception('创建用户账号失败', E_USER_CREATE_NORMAL_ACCOUNT_FAIL);
            $em->commit();
            return true;
        } catch (\Exception $e) {
            $em->rollback();
            Factory::logger('error')->addError(__CLASS__, [__FUNCTION__, __LINE__, $e, func_get_args()]);
            return Err::setLastErr($e->getCode());
        }
    }

    // 用户类型与账号类型判断

    /**
     * 获取用户的类型
     * @param integer   $userId
     * @return  integer
     */
    public function getUserTypeByUserId($userId)
    {
        /** @var User $User*/
        $User = RepositoryClass::User()->find($userId);
        return $User->getUserType();
    }

    /**
     * 获取账户类型
     * @param integer   $userId
     * @return integer
     */
    public function getAccountTypeByUserId($userId)
    {
        /** @var User $User*/
        $User = RepositoryClass::User()->find($userId);
        return $User->getAccountType();
    }

    /**
     * 判断用户是否为管理员
     * @param $userId
     * @return bool
     */
    public function isAdmin($userId)
    {
        return (self::getUserTypeByUserId($userId) == User::USER_TYPE_ADMIN)?true:false;
    }

    /**
     * 判断用户是否为普通用户
     * @param $userId
     * @return bool
     */
    public function isNormal($userId)
    {
        return (self::getUserTypeByUserId($userId) == User::USER_TYPE_NORMAL)?true:false;
    }

    /**
     * 判断用户是否为超级管理员
     * @param $userId
     * @return bool
     */
    public function isSuperAdmin($userId)
    {
        return (self::getUserTypeByUserId($userId) == User::USER_TYPE_SUPER_ADMIN)?true:false;
    }

    /**
     * 判断用户是否为微信用户
     * @param integer   $userId
     * @return bool
     */
    public function isWechatAccount($userId)
    {
        return (self::getAccountTypeByUserId($userId) == User::ACCOUNT_TYPE_WECHAT)?true:false;
    }

    /**
     * 判断是否为普通账户类型
     * @param integer   $userId
     * @return bool
     */
    public function isNormalAccount($userId)
    {
        return (self::getAccountTypeByUserId($userId) == User::ACCOUNT_TYPE_NORMAL)?true:false;
    }
}