<?php
/**
 * Created by PhpStorm.
 * User: zhan
 * Date: 2016/11/24
 * Time: 17:26
 */

namespace App\Entities;

/**
 * 用户主表
 * Class User
 * @package App\Entities
 *
 * @Entity(repositoryClass="UserRepository")
 * @Table(name="User")
 */
class User extends BaseEntity
{
    // 用户大类型
    const USER_TYPE_NORMAL = 1;         // 普通用户
    const USER_TYPE_ADMIN = 2;          // 管理员
    const USER_TYPE_SUPER_ADMIN = 3;    // 超级管理员

    // 账号类型
    const ACCOUNT_TYPE_WECHAT = 1;      // 微信用户
    const ACCOUNT_TYPE_NORMAL = 2;      // 一般用户

    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     * @var
     */
    protected $id;

    /**
     * @Column(type = "integer", nullable = false, options={"comment": "用户类型"})
     * @var
     */
    protected $userType = self::USER_TYPE_NORMAL;

    /**
     * @Column(type = "smallint", nullable = false, options = {"comment": "账号类型"})
     * @var
     */
    protected $accountType = self::ACCOUNT_TYPE_NORMAL;

    /**
     * @Column(type="datetimetz")
     * @var
     */
    protected $postTime;

    /**
     * 创建用户id
     * @Column(type = "integer", nullable = true, options = {"comment": "创建用户id"})
     * @var
     */
    protected $createUserId;

    /**
     * @Column(type="datetimetz", nullable = true)
     * @var
     */
    protected $updateTime;

    public function setPostTime($postTime)
    {
        $this->postTime = $postTime;
    }

    public function setUserType($userType)
    {
        $this->userType = $userType;
    }

    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;
    }

    public function setCreateUserId($createUserId)
    {
        $this->createUserId = $createUserId;
    }

    public function setUpdateTime($updateTime)
    {
        $this->updateTime = $updateTime;
    }

    public function getUserType()
    {
        return $this->userType;
    }

    public function getPostTime()
    {
        return $this->postTime;
    }

    public function getAccountType()
    {
        return $this->accountType;
    }

    public function getId()
    {
        if (!empty($this->id)) {
            return $this->id;
        } else {
            return false;
        }
    }
}