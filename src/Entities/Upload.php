<?php
namespace App\Entities;

/**
 * @Entity(repositoryClass="UploadRepository")
 * @Table(name="Upload")
 *
 * @property integer    $id
 * @property string     $name
 * @property string     $desc
 * @property string     $mime
 * @property string     $suffix
 * @property string     $path
 * @property integer    $userId
 * @property datetimetz  $postTime
 * @property string     $oldName
 */
class Upload extends BaseEntity
{

    /**
     * @Id @Column(type="integer", options={"comment":"上传文件ID", "unsigned": true})
     * @GeneratedValue
     * @var integer
     */
    protected $id;

    /**
     * @Column(type="string", length=80, options={"comment":"文件名", "default":""})
     * @var string
     * 文件名
     */
    protected $name = '';

    /**
     * @Column(name="`desc`", type="string", nullable = true, options={"comment":"文件描述", "default":""})
     * @var string
     * 文件描述
     */
    protected $desc = '';

    /**
     * @Column(type="string", length=80, options={"comment":"文件类型", "default":""})
     * @var string
     * mimeType
     */
    protected $mime = '';

    /**
     * @Column(type="string", length=20, options={"comment":"文件后缀", "default":""})
     * @var string
     * 文件名后缀
     */
    protected $suffix = '';

    /**
     * @Column(type="string", length=100, options={"comment":"文件存放地址"})
     * @var string
     * 文件存放地址
     */
    protected $path;

    /**
     * @Column(type="integer", options={"comment":"上传文件用户ID", "unsigned": true, "default":0})
     * @var integer
     * 用户ID
     */
    protected $userId = 0;

    /**
     * @Column(type="datetimetz", options={"comment":"文件上传时间"})
     * @var datetimetz
     * 上传时间
     */
    protected $postTime;

    /**
     * @Column(type = "string", length = 255, nullable = true, options = {"comment":"文件原名称"})
     * @var
     */
    protected $oldName;

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
