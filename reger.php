<?php

// 费心PHP+mysql极简通用多主题投票系统 V2025.05.08
// 演示地址1: http://demo.fxtp.cn
// 演示地址2: http://wevote.chalide.cn
// 文件路径: reger.php
// 文件大小: 6032 字节
// 最后修改时间: 2025-05-09 11:03:03
// 作者: yujianyue
// 邮件: 15058593138@qq.com Bug反馈或意见建议
// 版权所有,保留发行权和署名权
/**
 * 用户注册页面
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
$pageTitle = "用户注册";
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo getSiteTitle(); ?></title>
    <link rel="stylesheet" href="inc/css.css">
    <style>
        .register-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .register-title {
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
                <a href="login.php" class="nav-item">登录</a>
                <a href="reger.php" class="nav-item active">注册</a>
            </nav>
        </div>
    </header>
    
    <!-- 主体内容 -->
    <div class="main">
        <div class="register-container">
            <h1 class="register-title">用户注册</h1>
            
            <div class="error-message" id="errorMessage"></div>
            
            <div class="form-container">
                <form id="registerForm">
                    <div class="form-group">
                        <label for="username" class="form-label">用户名(手机号)</label>
                        <input type="text" id="username" name="username" class="form-control" placeholder="请输入手机号码" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">密码</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="请输入密码" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">确认密码</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="请再次输入密码" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="form-submit" style="width: 100%;">注册</button>
                    </div>
                </form>
                
                <div class="form-footer">
                    <p>已有账号？<a href="login.php">立即登录</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="inc/js.js"></script>
    <script>
        // 注册表单提交
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            var username = document.getElementById('username').value.trim();
            var password = document.getElementById('password').value.trim();
            var confirmPassword = document.getElementById('confirm_password').value.trim();
            var errorMessage = document.getElementById('errorMessage');
            
            // 简单验证
            if (!username) {
                showError('用户名不能为空');
                return;
            }
            
            // 验证手机号格式
            var phoneRegex = /^1[3456789]\d{9}$/;
            if (!phoneRegex.test(username)) {
                showError('请输入正确的手机号码');
                return;
            }
            
            if (!password) {
                showError('密码不能为空');
                return;
            }
            
            if (password.length < 6) {
                showError('密码长度不能少于6位');
                return;
            }
            
            if (password !== confirmPassword) {
                showError('两次输入的密码不一致');
                return;
            }
            
            // 发送注册请求
            ajaxRequest('api/user.php', {
                act: 'register',
                username: username,
                password: password
            }, function(response) {
                if (response.code === 0) {
                    // 注册成功，显示提示后跳转到登录页
                    showMask('注册成功', '您的账号已注册成功，请登录使用。', [
                        {
                            text: '立即登录',
                            class: 'btn-primary',
                            callback: function() {
                                window.location.href = 'login.php';
                            }
                        }
                    ]);
                } else {
                    // 显示错误信息
                    showError(response.msg || '注册失败，请稍后重试');
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
