<?php

// 费心PHP+mysql极简通用多主题投票系统 V2025.05.08
// 演示地址1: http://demo.fxtp.cn
// 演示地址2: http://wevote.chalide.cn
// 文件路径: api/user.php
// 文件大小: 5652 字节
// 最后修改时间: 2025-05-09 06:56:32
// 作者: yujianyue
// 邮件: 15058593138@qq.com Bug反馈或意见建议
// 版权所有,保留发行权和署名权
/**
 * 用户相关API
 */

// 引入必要文件
require_once '../inc/pubs.php';
require_once '../inc/sqls.php';

// 获取操作类型
$action = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';

// 实例化数据库操作类
$db = new DB();

// 根据操作类型执行相应操作
switch ($action) {
    // 用户登录
    case 'login':
        // 获取参数
        $username = isset($_POST['username']) ? safeFilter($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // 参数验证
        if (empty($username) || empty($password)) {
            ajaxReturn(1, '用户名和密码不能为空');
        }
        
        // 查询用户
        $user = $db->getOne('users', "username = '$username'");
        
        // 验证用户和密码
        if (!$user || !password_verify($password, $user['password'])) {
            ajaxReturn(1, '用户名或密码错误');
        }
        
        // 验证用户状态
        if ($user['status'] != 1) {
            ajaxReturn(1, '账号已被禁用，请联系管理员');
        }
        
        // 更新最后登录时间
        $db->update('users', [
            'logtime' => date('Y-m-d H:i:s')
        ], "id = {$user['id']}");
        
        // 记录登录日志
        writeLog($user['id'], 'login', '用户登录');
        
        // 存储session
        session_start();
        $_SESSION['user'] = $user;
        
        ajaxReturn(0, '登录成功', $user);
        break;
    
    // 用户注册
    case 'register':
        // 获取参数
        $username = isset($_POST['username']) ? safeFilter($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // 参数验证
        if (empty($username) || empty($password)) {
            ajaxReturn(1, '用户名和密码不能为空');
        }
        
        // 验证手机号格式
        if (!preg_match('/^1[3456789]\d{9}$/', $username)) {
            ajaxReturn(1, '请输入正确的手机号码');
        }
        
        // 验证密码长度
        if (strlen($password) < 6) {
            ajaxReturn(1, '密码长度不能少于6位');
        }
        
        // 检查用户名是否已存在
        $existUser = $db->getOne('users', "username = '$username'");
        if ($existUser) {
            ajaxReturn(1, '该手机号已被注册');
        }
        
        // 密码加密
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // 添加用户
        $userId = $db->insert('users', [
            'username' => $username,
            'password' => $hashedPassword,
            'irole' => 0, // 普通用户
            'status' => 1, // 正常状态
            'regtime' => date('Y-m-d H:i:s')
        ]);
        
        if (!$userId) {
            ajaxReturn(1, '注册失败，请稍后重试');
        }
        
        // 记录注册日志
        writeLog($userId, 'register', '用户注册');
        
        ajaxReturn(0, '注册成功');
        break;
    
    // 用户登出
    case 'logout':
        session_start();
        
        // 记录登出日志
        if (isset($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
            writeLog($_SESSION['user']['id'], 'logout', '用户登出');
        }
        
        // 清除session
        unset($_SESSION['user']);
        session_destroy();
        
        // 跳转到首页
        header('Location: ../index.php');
        exit;
        break;
    
    // 获取用户信息
    case 'getUserInfo':
        // 验证权限
        $user = checkAuth('getUserInfo');
        if (!$user) {
            exit;
        }
        
        // 移除敏感信息
        unset($user['password']);
        
        ajaxReturn(0, '获取成功', $user);
        break;
    
    // 更新用户信息
    case 'updateUserInfo':
        // 验证权限
        $user = checkAuth('updateUserInfo');
        if (!$user) {
            exit;
        }
        
        // 获取参数
        $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        // 如果要更新密码
        if (!empty($newPassword)) {
            // 验证密码长度
            if (strlen($newPassword) < 6) {
                ajaxReturn(1, '密码长度不能少于6位');
            }
            
            // 验证两次密码是否一致
            if ($newPassword !== $confirmPassword) {
                ajaxReturn(1, '两次输入的密码不一致');
            }
            
            // 更新密码
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // 更新用户信息
            $result = $db->update('users', [
                'password' => $hashedPassword
            ], "id = {$user['id']}");
            
            if (!$result) {
                ajaxReturn(1, '更新失败，请稍后重试');
            }
            
            // 记录日志
            writeLog($user['id'], 'update_password', '修改密码');
            
            // 更新 session 中的用户信息
            $updatedUser = $db->getOne('users', "id = {$user['id']}");
            $_SESSION['user'] = $updatedUser;
            
            ajaxReturn(0, '密码更新成功');
        } else {
            ajaxReturn(1, '请输入新密码');
        }
        break;
    
    // 未知操作
    default:
        ajaxReturn(1, '未知操作');
        break;
}
