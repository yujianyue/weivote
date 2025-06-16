<?php

// 费心PHP+mysql极简通用多主题投票系统 V2025.05.08
// 演示地址1: http://demo.fxtp.cn
// 演示地址2: http://wevote.chalide.cn
// 文件路径: login.php
// 文件大小: 4644 字节
// 最后修改时间: 2025-05-09 11:03:03
// 作者: yujianyue
// 邮件: 15058593138@qq.com Bug反馈或意见建议
// 版权所有,保留发行权和署名权
/**
 * 用户登录页面
 */

// 引入必要文件
require_once 'inc/pubs.php';

// 检查是否已登录
$user = checkLogin();
if ($user) {
    // 已登录，跳转到首页
    header('Location: index.php');
    exit;
}

// 页面标题
$pageTitle = "用户登录";
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo getSiteTitle(); ?></title>
    <link rel="stylesheet" href="inc/css.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .login-title {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-footer {
            margin-top: 20px;
            text-align: center;
        }
        
        .error-message {
            color: #e74c3c;
            background-color: #fadbd8;
            padding: 10px;
            border-radius: 3px;
            margin-bottom: 15px;
            display: none;
        }
    </style>
</head>
<body>
    <!-- 顶部导航 -->
    <header class="header">
        <div class="header-container">
            <div class="logo"><?php echo getSiteTitle(); ?></div>
            <nav class="nav">
                <a href="index.php" class="nav-item">首页</a>
                <a href="login.php" class="nav-item active">登录</a>
                <a href="reger.php" class="nav-item">注册</a>
            </nav>
        </div>
    </header>
    
    <!-- 主体内容 -->
    <div class="main">
        <div class="login-container">
            <h1 class="login-title">用户登录</h1>
            
            <div class="error-message" id="errorMessage"></div>
            
            <div class="form-container">
                <form id="loginForm">
                    <div class="form-group">
                        <label for="username" class="form-label">用户名</label>
                        <input type="text" id="username" name="username" class="form-control" placeholder="请输入用户名(手机号)" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">密码</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="请输入密码" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="form-submit" style="width: 100%;">登录</button>
                    </div>
                </form>
                
                <div class="form-footer">
                    <p>还没有账号？<a href="reger.php">立即注册</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="inc/js.js"></script>
    <script>
        // 登录表单提交
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            var username = document.getElementById('username').value.trim();
            var password = document.getElementById('password').value.trim();
            var errorMessage = document.getElementById('errorMessage');
            
            // 简单验证
            if (!username) {
                showError('用户名不能为空');
                return;
            }
            
            if (!password) {
                showError('密码不能为空');
                return;
            }
            
            // 发送登录请求
            ajaxRequest('api/user.php', {
                act: 'login',
                username: username,
                password: password
            }, function(response) {
                if (response.code === 0) {
                    // 登录成功，跳转到首页
                    window.location.href = 'index.php';
                } else {
                    // 显示错误信息
                    showError(response.msg || '登录失败，请检查用户名和密码');
                }
            }, function(error) {
                showError('请求失败，请稍后重试');
            });
        });
        
        // 显示错误信息
        function showError(message) {
            var errorMessage = document.getElementById('errorMessage');
            errorMessage.textContent = message;
            errorMessage.style.display = 'block';
        }
    </script>
</body>
</html>
