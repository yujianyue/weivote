<?php

// 费心PHP+mysql极简通用多主题投票系统 V2025.05.08
// 演示地址1: http://demo.fxtp.cn
// 演示地址2: http://wevote.chalide.cn
// 文件路径: inc/conn.php
// 文件大小: 1665 字节
// 最后修改时间: 2025-05-09 10:59:05
// 作者: yujianyue
// 邮件: 15058593138@qq.com Bug反馈或意见建议
// 版权所有,保留发行权和署名权
/**
 * 数据库连接及全局配置文件
 */

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 网站全局配置
$CONFIG = [
    // 网站标题
    'site_title' => '费心投票简洁投票系统(fxtp.cn)',
    
    // 数据库配置
    'db' => [
        'host' => 'localhost',
        'user' => 'wevote_chalide',
        'pass' => 'EaPPfKsBXj3Wij5X',
        'name' => 'wevote_chalide',
        'port' => 3306,
        'prefix' => 'tp_',
        'charset' => 'utf8'
    ],
    
    // 资源文件缓存控制（修改版本号可更新前端缓存）
    'version' => [
        'css' => '1.0.0',
        'js' => '1.0.0'
    ]
];

// 数据库连接
function dbConnect() {
    global $CONFIG;
    
    $conn = new mysqli(
        $CONFIG['db']['host'],
        $CONFIG['db']['user'],
        $CONFIG['db']['pass'],
        $CONFIG['db']['name'],
        $CONFIG['db']['port']
    );
    
    // 检查连接
    if ($conn->connect_error) {
        die("数据库连接失败: " . $conn->connect_error);
    }
    
    // 设置字符集
    $conn->set_charset($CONFIG['db']['charset']);
    
    return $conn;
}

// 数据表前缀函数
function getTable($table) {
    global $CONFIG;
    return $CONFIG['db']['prefix'] . $table;
}

// 获取带版本号的CSS路径
function getCssPath($file) {
    global $CONFIG;
    return "/assets/css/{$file}?v=" . $CONFIG['version']['css'];
}

// 获取带版本号的JS路径
function getJsPath($file) {
    global $CONFIG;
    return "/assets/js/{$file}?v=" . $CONFIG['version']['js'];
}

// 获取网站标题
function getSiteTitle() {
    global $CONFIG;
    return $CONFIG['site_title'];
}
