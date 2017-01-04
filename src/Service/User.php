<?php
/**
 * Created by PhpStorm.
 * User: zhan
 * Date: 2016/11/24
 * Time: 16:56
 */

namespace App\Service;
use App\Entities\NormalAccount;
use App\Entities\User as UserEntity;
use App\Err;
use App\Factory;
use App\RepositoryClass;
use App\Util;

/**
 * 用户类，用于提供用户操作接口
 * Class User
 * @package App\Service
 * @default enable
 */
class User extends Base
{
    /**
     * 普通账号登陆
     * @default enable
     * @param string $account        账号
     * @param string $passwd         密码
     * @return array
     * <pre>
     * [
     *  'once' => [
     *      'userId'    => 19,      // usreId
     *      'token'     => '',      // token
     *  ]
     * ]
     * </pre>
     */
    public static function normalLogin($account, $passwd)
    {
        $account = filter_var($account, FILTER_SANITIZE_STRING);
        $passwd = filter_var($passwd, FILTER_SANITIZE_STRING);
        if (empty($account)) return Err::setLastErr(E_USER_ACCOUNT_ERROR);
        if (empty($passwd)) return Err::setLastErr(E_USER_PASSWD_ERROR);
        $normalAccountInfo = RepositoryClass::NormalAccount()->getNormalAccountByWhere(['login' => $account]);
        if (empty($normalAccountInfo)) return Err::setLastErr(E_USER_ACCOUNT_NOT_EXIST);
        $normalAccountInfo = reset($normalAccountInfo);
        $sPasswd = empty($normalAccountInfo['passwd'])?'':$normalAccountInfo['passwd'];
        $userId = empty($normalAccountInfo['userId'])?'':$normalAccountInfo['userId'];
        if (empty($sPasswd) || empty($userId)) return Err::setLastErr(E_SYS_ERROR);   // 系统错误
        if (!Util::verifyPasswd($passwd, $sPasswd)) return Err::setLastErr(E_USER_PASSWD_ERROR);    // 密码错误
        $token = self::login($userId, $passwd);
        return [
            'once' => [
                'userId' => $userId,
                'token' => $token
            ]
        ];
    }

    /**
     * 普通账号注册
     * @default enable
     * @param string $account    账号
     * @param string $passwd     密码
     * @param array $ext 额外注册信息
     * @return array
     * <pre>
     * [
     *  // 注册成功，code为0
     * ]
     * </pre>
     */
    public static function normalReg($account, $passwd, $ext = [])
    {
        $account = filter_var($account, FILTER_SANITIZE_STRING);
        $passwd = filter_var($passwd, FILTER_SANITIZE_STRING);
        if (empty($account)) return Err::setLastErr(E_USER_ACCOUNT_ERROR);
        if (empty($passwd)) return Err::setLastErr(E_USER_PASSWD_ERROR);
        // 检查账号是否已被使用
        if (!empty(RepositoryClass::NormalAccount()->normalAccount2UserId($account))) return Err::setLastErr(E_USER_ACCOUNT_ALREADY_USE);
        $hashPasswd = Util::createPasswd($passwd);
        RepositoryClass::User()->normalReg($account, $hashPasswd);
        return [];
    }

    /**
     * 判断是否为登陆状态
     * @default enable
     * @return array
     * <pre>
     * [
     *  'isLogin' => true   // 是否登陆成功
     * ]
     * </pre>
     */
    public static function isLogin()
    {
        $userId = self::getClientUserId();
        return [
            'isLogin' => $userId?true:false
        ];
    }

    /**
     * 添加一个管理员账号
     * @default enable
     * @param string $account
     * @param string $passwd
     * @return array
     */
    public static function addAdmin($account, $passwd)
    {
        if (!self::isSuperAdmin()) return [];
        $userId = self::getClientUserId();
        $account = filter_var($account, FILTER_SANITIZE_STRING);
        $passwd = filter_var($passwd, FILTER_SANITIZE_STRING);
        if (empty($account)) return Err::setLastErr(E_USER_ACCOUNT_ERROR);
        if (empty($passwd)) return Err::setLastErr(E_USER_PASSWD_ERROR);
        if (!empty(RepositoryClass::NormalAccount()->normalAccount2UserId($account))) return Err::setLastErr(E_USER_ACCOUNT_ALREADY_USE);
        $hashPasswd = Util::createPasswd($passwd);
        $info = [
            'createUserId' => $userId
        ];
        RepositoryClass::User()->addAdmin($account, $hashPasswd, $info);
        return [];
    }

    /**
     * 执行修改密码操作
     * @param $userId
     * @param $passwd
     * @return bool
     */
    private static function changePasswd($userId, $passwd)
    {
        $hashNewPasswd = Util::createPasswd($passwd);
        $updateRet = RepositoryClass::NormalAccount()->updateNormalAccount(['userId' => $userId], ['passwd' => $hashNewPasswd]);
        return $updateRet;
    }

    /**
     * 登陆用户修改自己的密码
     * @default enable
     * @param string $oldPasswd
     * @param string $newPasswd
     * @return array
     */
    public static function changeSelfPasswd($oldPasswd, $newPasswd)
    {
        if (!self::isNormalAccount()) return [];
        $userId = self::getClientUserId();
        $oldPasswd = filter_var($oldPasswd, FILTER_SANITIZE_STRING);
        $newPasswd = filter_var($newPasswd, FILTER_SANITIZE_STRING);
        if (empty($oldPasswd)) return Err::setLastErr(E_PARAM_ERROR);
        if (empty($newPasswd)) return Err::setLastErr(E_PARAM_ERROR);
        $userAccountInfo = RepositoryClass::NormalAccount()->getNormalAccountByWhere(['userId' => $userId]);
        if (empty($userAccountInfo)) return Err::setLastErr(E_SYS_ERROR);
        $userAccountInfo = reset($userAccountInfo);
        $sPasswd = empty($userAccountInfo['passwd'])?'':$userAccountInfo['passwd'];
        if (empty($sPasswd)) return Err::setLastErr(E_SYS_ERROR);
        if (!Util::verifyPasswd($oldPasswd, $sPasswd)) return Err::setLastErr(E_USER_PASSWD_ERROR);  // 密码填写错误
        $updateRet = self::changePasswd($userId, $newPasswd);
        if ($updateRet) self::loginOut($userId);
        return [];
    }

    /**
     * 超级管理员修改用户密码
     * @default enable
     * @param integer $userId
     * @param string $passwd
     * @return array
     */
    public static function changeUserPasswd($userId, $passwd)
    {
        if (!self::isSuperAdmin()) return [];
        $passwd = filter_var($passwd, FILTER_SANITIZE_STRING);
        $userId = filter_var($userId, FILTER_VALIDATE_INT);
        if (empty($passwd)) return Err::setLastErr(E_PARAM_ERROR);  // 参数错误
        if (empty($userId)) return Err::setLastErr(E_PARAM_ERROR);
        $normalAccountInfo = RepositoryClass::NormalAccount()->getNormalAccountByWhere(['userId' => $userId]);
        if (empty($normalAccountInfo)) return Err::setLastErr(E_USER_NOT_EXIST);    // 用户不存在
        self::changePasswd($userId, $passwd);
        return [];
    }
}