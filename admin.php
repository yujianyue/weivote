<?php

// 费心PHP+mysql极简通用多主题投票系统 V2025.05.08
// 演示地址1: http://demo.fxtp.cn
// 演示地址2: http://wevote.chalide.cn
// 文件路径: admin.php
// 文件大小: 368 字节
// 最后修改时间: 2025-05-09 11:03:03
// 作者: yujianyue
// 邮件: 15058593138@qq.com Bug反馈或意见建议
// 版权所有,保留发行权和署名权
/**
 * 管理员面板入口
 * 检查管理员权限，并跳转到管理后台
 */

// 引入必要文件
require_once 'inc/pubs.php';

// 检查管理员权限
if (!checkAdmin()) {
    // 未登录或不是管理员，跳转到登录页
    header('Location: login.php');
    exit;
}

// 跳转到管理后台首页
header('Location: admin/index.php');
exit;
