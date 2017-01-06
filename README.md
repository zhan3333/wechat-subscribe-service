# 运行环境
## 系统环境
- centos 6.5
- nginx
- redis
- mysql
- php5.6

## 软件
- [composer](http://docs.phpcomposer.com) 包管理器

## php扩展
- [donkeyid](https://github.com/osgochina/donkeyid)
- [swoole](http://www.swoole.com/)
- [phpredis](https://github.com/phpredis/phpredis)

---
# 介绍
    本服务用于个人微信订阅号开发，利用 overtrue/wechat 库，实现订阅号的自动回复等操作。

# 实现功能
- 微信消息自动回复设置
- 微信获取关注用户信息（认证订阅号）
- 微信素材管理（认证订阅号）
- 微信菜单管理（认证订阅号）
- 微信支付（认证公众号）
- 聚合平台笑话大全数据对接，订阅号回复 '笑话' 将返回一条随机笑话
- 文件上传与管理
- 微信用户默认注册
- 管理员系统

# 下一步要做
- 使用数据库实现自定义关键词回复设置