<?php
/**
 * Created by PhpStorm.
 * User: zhan
 * Date: 2016/11/24
 * Time: 17:57
 */

namespace App\Service;


use App\Err;
use App\Factory;
use App\RepositoryClass;
use App\Util;

/**
 * Class UserBase
 * @package App\Service
 * @default disable
 */
class UserBase
{
    const USER_LOGIN_TOKEN = 'userLoginToken';

    /**
     * 获取当前用户userId
     */
    public static function getClientUserId()
    {
        if (empty($_REQUEST['_uid'])) {
            if (empty($_REQUEST['token'])) return 0;
            if (empty($_REQUEST['userId'])) return 0;
            $redis = Factory::redis();
            $tokenInfo = $redis->hGetAll(self::USER_LOGIN_TOKEN . $_REQUEST['userId']);
            if (empty($tokenInfo['token'])) return 0;
            if (empty($tokenInfo['credential'])) return 0;
            if (!Util::verifyPasswd($_REQUEST['userId'] . $tokenInfo['credential'] . $tokenInfo['deviceIdentification'], $_REQUEST['token'])) {
                $redis->del(self::USER_LOGIN_TOKEN . $_REQUEST['userId']);
                return 0;
            }
            $_REQUEST['_uid'] = $_REQUEST['userId'];
        }
        $uid = $_REQUEST['_uid'];
        return $uid;
    }

    /**
     * @param $userId       integer     userId
     * @param $credential   string      登陆密钥
     * @param array $ext                扩展数据，存放设备信息
     * @return string                   登陆后生成凭据
     */
    public static function login($userId, $credential, $ext = [])
    {
        $deviceIdentification = empty($ext['uuid'])?self::getClientIp():$ext['uuid'];
        $redis = Factory::redis();
        $token = Util::createPasswd($userId . $credential . $deviceIdentification);
        $redis->hMset(self::USER_LOGIN_TOKEN . $userId, [
            'deviceIdentification' => $deviceIdentification,
            'credential' => $credential,
            'token' => $token
        ]);
        return $token;
    }

    /**
     * 退出登陆
     * @param integer   $userId
     */
    public static function loginOut($userId)
    {
        Factory::redis()->del(self::USER_LOGIN_TOKEN . $userId);
        unset($_REQUEST['userId']);
        unset($_REQUEST['token']);
        unset($_REQUEST['_uid']);
    }

    /**
     * 获取客户端所在ip
     * @return string
     */
    public static function getClientIp()
    {
        $ip = '127.0.0.1';
        if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        }
        return $ip;
    }

    /**
     * 判断登陆用户是否为管理员
     * @return bool
     */
    public static function isAdmin()
    {
        $userId = self::getClientUserId();
        if (empty($userId)) return Err::setLastErr(E_USER_NO_LOGIN);
        if(empty(RepositoryClass::User()->isAdmin($userId))) return Err::setLastErr(E_USER_NOT_IS_ADMIN);
        return true;
    }

    /**
     * 判断登陆用户是否为普通用户
     * @return bool
     */
    public static function isNormal()
    {
        $userId = self::getClientUserId();
        if (empty($userId)) return Err::setLastErr(E_USER_NO_LOGIN);
        if (RepositoryClass::User()->isNormal($userId)) return true;
        return Err::setLastErr(E_USER_NOT_IS_NORMAL);
    }

    /**
     * 判断登陆用户是否为超级管理员
     * @param int $userId
     * @return bool
     */
    public static function isSuperAdmin($userId = 0)
    {
        if (empty($userId)) $userId = self::getClientUserId();
        if (empty($userId)) return Err::setLastErr(E_USER_NO_LOGIN);
        $isSuperAdmin = RepositoryClass::User()->isSuperAdmin($userId);
        if (empty($isSuperAdmin)) return Err::setLastErr(E_USER_NOT_IS_SUPER_ADMIN);
        return true;
    }

    /**
     * 判断是否为普通账户
     * @param int $userId
     * @return bool
     */
    public static function isNormalAccount($userId = 0)
    {
        if (empty($userId)) $userId = self::getClientUserId();
        if (empty($userId)) return Err::setLastErr(E_USER_NO_LOGIN);
        $isNormalAccount = RepositoryClass::User()->isNormalAccount($userId);
        if (empty($isNormalAccount)) return Err::setLastErr(E_USER_ACCOUNT_NOT_IS_NORMAL);
        return true;
    }

    /**
     * 判断是否为微信用户
     * @param int $userId
     * @return bool
     */
    public static function isWechatAccount($userId = 0)
    {
        if (empty($userId)) $userId = self::getClientUserId();
        if (empty($userId)) return Err::setLastErr(E_USER_NO_LOGIN);
        $isWechatAccount = RepositoryClass::User()->isWechatAccount($userId);
        if (empty($isWechatAccount)) return Err::setLastErr(E_USER_ACCOUNT_NOT_IS_WECHAT);
        return true;
    }
}