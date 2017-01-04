<?php
/**
 * 储存用户微信账号信息
 * User: zhan
 * Date: 2017/1/4
 * Time: 16:04
 */

namespace App\Entities;

/**
 * Class WechatAccount
 * @package App\Entities
 * @Table(name="WechatAccount")
 * @Entity(repositoryClass="WechatAccountRepository")
 *
 * @property integer    $id
 * @property integer    $userId
 * @property string     $openid
 * @property datetimetz $postTime
 * @property integer    $subscribe
 * @property string     $nickname
 * @property integer    $sex
 * @property string     $city
 * @property string     $country
 * @property string     $province
 * @property string     $language;
 * @property string     $headimgurl;
 * @property datetimetz $subscribe_time;
 *
 */
class WechatAccount extends BaseEntity
{
    const SEX_MALE = 1;         // 性别男
    const SEX_FEMALE = 2;       // 性别女
    const SEX_UNKNOWN = 0;      //  性别未知

    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var integer
     */
    protected $id;

    /**
     * @Column(type="integer", options={"comment":"用户ID", "unsigned": true})
     * @var integer
     * 用户ID
     */
    protected $userId;

    /**
     * @Column(type="string", unique=true, options={"comment":"openid", "default": ""})
     * @var string
     * 登录帐号
     */
    protected $openid = '';

    /**
     * @Column(type="datetimetz", options={"comment":"注册关联时间"})
     * @var datetimetz
     * 注册关联时间
     */
    protected $postTime;

    /**
     * @Column(type="smallint", nullable = true, options = {"comment":"是否关注公众号"})
     * @var
     */
    protected $subscribe;

    /**
     * @Column(type="json_array", nullable=true, options = {"comment":"微信昵称"})
     * @var
     */
    protected $nickname;

    /**
     * @Column(type="smallint", nullable=true, options = {"comment":"微信性别"})
     * @var
     */
    protected $sex = self::SEX_UNKNOWN;

    /**
     * @Column(type="string", nullable=true, options = {"comment":"微信城市"})
     * @var
     */
    protected $city;

    /**
     * @Column(type="string", nullable=true, options = {"comment":"微信国家"})
     * @var
     */
    protected $country;

    /**
     * @Column(type="string", nullable=true, options = {"comment":"微信省份"})
     * @var
     */
    protected $province;

    /**
     * @Column(type="string", nullable=true, options = {"comment":"微信语言"})
     * @var
     */
    protected $language;

    /**
     * @Column(type="string", nullable=true, options = {"comment":"微信头像url"})
     * @var
     */
    protected $headimgurl;

    /**
     * @Column(type="datetimetz", nullable=true, options = {"comment":"微信关注时间"})
     * @var
     */
    protected $subscribe_time;

    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) return $this->$name;
        return null;
    }

}