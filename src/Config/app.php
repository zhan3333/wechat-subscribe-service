<?php
/**
 * Created by PhpStorm.
 * User: zhan
 * Date: 2016/11/28
 * Time: 16:03
 */
return [
    'db_default_page' => 30,        // 数据库查询默认查询条数
    'geoIpDatabasesPath' => APPPATH . '/src/Databases/geoIp/GeoLite2-City.mmdb',        // geoIp数据库路径配置
    'resUrl' => '',                 // 文件资源路径
    'upload' => [
        'base_dir' => APPPATH . '/upload/',     // 文件储存绝对路径
        'base_url' => '/upload/',               // 文件储存相对路径
    ]
];