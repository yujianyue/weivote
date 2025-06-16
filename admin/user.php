<?php

// 费心PHP+mysql极简通用多主题投票系统 V2025.05.08
// 演示地址1: http://demo.fxtp.cn
// 演示地址2: http://wevote.chalide.cn
// 文件路径: admin/user.php
// 文件大小: 30298 字节
// 最后修改时间: 2025-05-09 07:04:30
// 作者: yujianyue
// 邮件: 15058593138@qq.com Bug反馈或意见建议
// 版权所有,保留发行权和署名权
/**
 * 用户管理页面
 * 管理用户账号
 */

// 引入必要文件
require_once '../inc/pubs.php';
require_once '../inc/sqls.php';

// 验证管理员权限
if (!checkAdmin()) {
    header('Location: ../login.php');
    exit;
}

// 获取当前用户
$user = $_SESSION['user'];

// 实例化数据库操作类
$db = new DB();

// 获取分页参数
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$pageSize = 20;

// 获取筛选参数
$status = isset($_GET['status']) ? intval($_GET['status']) : -1;
$role = isset($_GET['role']) ? intval($_GET['role']) : -1;
$keyword = isset($_GET['keyword']) ? safeFilter($_GET['keyword']) : '';

// 构建查询条件
$whereConditions = [];
if ($status >= 0) {
    $whereConditions[] = "status = $status";
}
if ($role >= 0) {
    $whereConditions[] = "irole = $role";
}
if ($keyword) {
    $whereConditions[] = "username LIKE '%$keyword%'";
}

$whereStr = !empty($whereConditions) ? implode(' AND ', $whereConditions) : '';

// 获取总记录数
$total = $db->count('users', $whereStr);

// 计算分页信息
$pagination = getPagination($total, $page, $pageSize);

// 获取用户列表
$orderBy = "id ASC";
$limit = "{$pagination['offset']}, {$pagination['pageSize']}";
$userList = $db->getAll('users', $whereStr, '*', $orderBy, $limit);

// 页面标题
$pageTitle = "用户管理";
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo getSiteTitle(); ?></title>
    <link rel="stylesheet" href="../inc/css.css">
    <style>
        .admin-container {
            display: flex;
            min-height: calc(100vh - 60px);
        }
        
        .admin-sidebar {
            width: 200px;
            background-color: #2c3e50;
            color: #fff;
            padding-top: 20px;
        }
        
        .admin-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .admin-menu-item {
            padding: 12px 20px;
            border-bottom: 1px solid #34495e;
        }
        
        .admin-menu-item a {
            color: #fff;
            text-decoration: none;
            display: block;
        }
        
        .admin-menu-item:hover {
            background-color: #34495e;
        }
        
        .admin-menu-item.active {
            background-color: #3498db;
        }
        
        .admin-content {
            flex: 1;
            padding: 20px;
            background-color: #f5f5f5;
            overflow-y: auto;
        }
        
        .admin-header {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .filter-form {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .operation-btns {
            display: flex;
            gap: 5px;
        }
        
        .user-role, .user-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            color: #fff;
            font-size: 12px;
        }
        
        .user-role-admin {
            background-color: #e74c3c;
        }
        
        .user-role-user {
            background-color: #3498db;
        }
        
        .user-status-enabled {
            background-color: #2ecc71;
        }
        
        .user-status-disabled {
            background-color: #95a5a6;
        }
    </style>
</head>
<body>
    <!-- 顶部导航 -->
    <header class="header">
        <div class="header-container">
            <div class="logo"><?php echo getSiteTitle(); ?> - 管理后台</div>
            <nav class="nav">
                <a href="../index.php" class="nav-item">前台首页</a>
                <a href="../api/user.php?act=logout" class="nav-item">退出登录</a>
            </nav>
        </div>
    </header>
    
    <!-- 管理内容 -->
    <div class="admin-container">
        <!-- 侧边栏 -->
        <div class="admin-sidebar">
            <ul class="admin-menu">
                <li class="admin-menu-item"><a href="index.php">系统概况</a></li>
                <li class="admin-menu-item"><a href="topic.php">投票管理</a></li>
                <li class="admin-menu-item active"><a href="user.php">用户管理</a></li>
                <li class="admin-menu-item"><a href="stat.php">数据统计</a></li>
                <li class="admin-menu-item"><a href="logs.php">系统日志</a></li>
            </ul>
        </div>
        
        <!-- 主体内容 -->
        <div class="admin-content">
            <div class="admin-header">
                <h2>用户管理</h2>
                <div>
                    <button class="btn btn-green" onclick="showAddUserForm()">添加用户</button>
                </div>
            </div>
            
            <!-- 筛选表单 -->
            <div class="filter-form">
                <form action="user.php" method="get">
                    <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
                        <div>
                            <label for="keyword">用户名：</label>
                            <input type="text" name="keyword" id="keyword" class="form-control" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="搜索用户名">
                        </div>
                        <div>
                            <label for="status">状态：</label>
                            <select name="status" id="status" class="form-control">
                                <option value="-1">全部</option>
                                <option value="1" <?php echo $status == 1 ? 'selected' : ''; ?>>正常</option>
                                <option value="0" <?php echo $status === 0 ? 'selected' : ''; ?>>禁用</option>
                            </select>
                        </div>
                        <div>
                            <label for="role">角色：</label>
                            <select name="role" id="role" class="form-control">
                                <option value="-1">全部</option>
                                <option value="0" <?php echo $role === 0 ? 'selected' : ''; ?>>普通用户</option>
                                <option value="1" <?php echo $role == 1 ? 'selected' : ''; ?>>管理员</option>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-blue">搜索</button>
                            <a href="user.php" class="btn btn-gray">重置</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- 用户列表 -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>用户名</th>
                            <th>角色</th>
                            <th>状态</th>
                            <th>注册时间</th>
                            <th>最后登录</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($userList)): ?>
                            <?php foreach ($userList as $userData): ?>
                                <tr>
                                    <td><?php echo $userData['id']; ?></td>
                                    <td><?php echo htmlspecialchars($userData['username']); ?></td>
                                    <td>
                                        <?php if ($userData['irole'] == 1): ?>
                                            <span class="user-role user-role-admin">管理员</span>
                                        <?php else: ?>
                                            <span class="user-role user-role-user">普通用户</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($userData['status'] == 1): ?>
                                            <span class="user-status user-status-enabled">正常</span>
                                        <?php else: ?>
                                            <span class="user-status user-status-disabled">禁用</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($userData['regtime'])); ?></td>
                                    <td><?php echo $userData['logtime'] ? date('Y-m-d H:i', strtotime($userData['logtime'])) : '从未登录'; ?></td>
                                    <td class="operation-btns">
                                        <button class="btn btn-blue btn-sm" onclick="editUser(<?php echo $userData['id']; ?>, '<?php echo htmlspecialchars(addslashes($userData['username'])); ?>', <?php echo $userData['irole']; ?>, <?php echo $userData['status']; ?>)">编辑</button>
                                        <button class="btn btn-gray btn-sm" onclick="resetPassword(<?php echo $userData['id']; ?>, '<?php echo htmlspecialchars(addslashes($userData['username'])); ?>')">重置密码</button>
                                        
                                        <?php if ($userData['id'] != $user['id']): // 不能删除自己 ?>
                                            <?php if ($userData['status'] == 1): ?>
                                                <button class="btn btn-red btn-sm" onclick="changeUserStatus(<?php echo $userData['id']; ?>, '<?php echo htmlspecialchars(addslashes($userData['username'])); ?>', 0)">禁用</button>
                                            <?php else: ?>
                                                <button class="btn btn-green btn-sm" onclick="changeUserStatus(<?php echo $userData['id']; ?>, '<?php echo htmlspecialchars(addslashes($userData['username'])); ?>', 1)">启用</button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">暂无用户数据</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- 分页 -->
            <div class="pagination" id="pagination"></div>
        </div>
    </div>
    
    <script src="../inc/js.js"></script>
    <script>
        // 初始化分页
        pagination('pagination', <?php echo $pagination['page']; ?>, <?php echo $pagination['totalPage']; ?>, function(page) {
            // 构建URL参数
            var params = new URLSearchParams(window.location.search);
            params.set('page', page);
            
            // 跳转到新页面
            window.location.href = 'user.php?' + params.toString();
        });
        
        // 显示添加用户表单
        function showAddUserForm() {
            var content = `
                <form id="addUserForm">
                    <div class="form-group">
                        <label for="username" class="form-label">用户名(手机号)</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">密码</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">确认密码</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role" class="form-label">角色</label>
                        <select id="role" name="role" class="form-control">
                            <option value="0">普通用户</option>
                            <option value="1">管理员</option>
                        </select>
                    </div>
                </form>
            `;
            
            showMask('添加用户', content, [
                {
                    text: '取消',
                    class: 'btn-default',
                    callback: function() {
                        closeMask();
                    }
                },
                {
                    text: '确定添加',
                    class: 'btn-primary',
                    callback: function() {
                        var form = document.getElementById('addUserForm');
                        var username = form.elements['username'].value.trim();
                        var password = form.elements['password'].value;
                        var confirmPassword = form.elements['confirm_password'].value;
                        var role = form.elements['role'].value;
                        
                        // 验证手机号格式
                        var phoneRegex = /^1[3456789]\d{9}$/;
                        if (!phoneRegex.test(username)) {
                            showMask('提示', '请输入正确的手机号码', [
                                {
                                    text: '确定',
                                    class: 'btn-primary',
                                    callback: function() {
                                        showAddUserForm();
                                    }
                                }
                            ]);
                            return;
                        }
                        
                        // 验证密码
                        if (password.length < 6) {
                            showMask('提示', '密码长度不能少于6位', [
                                {
                                    text: '确定',
                                    class: 'btn-primary',
                                    callback: function() {
                                        showAddUserForm();
                                    }
                                }
                            ]);
                            return;
                        }
                        
                        if (password !== confirmPassword) {
                            showMask('提示', '两次输入的密码不一致', [
                                {
                                    text: '确定',
                                    class: 'btn-primary',
                                    callback: function() {
                                        showAddUserForm();
                                    }
                                }
                            ]);
                            return;
                        }
                        
                        // 发送添加用户的请求
                        ajaxRequest('../api/admin.php', {
                            act: 'addUser',
                            username: username,
                            password: password,
                            role: role
                        }, function(response) {
                            if (response.code === 0) {
                                showMask('成功', '用户添加成功', [
                                    {
                                        text: '确定',
                                        class: 'btn-primary',
                                        callback: function() {
                                            window.location.reload();
                                        }
                                    }
                                ]);
                            } else {
                                showMask('错误', response.msg || '添加失败，请稍后重试', [
                                    {
                                        text: '确定',
                                        class: 'btn-primary',
                                        callback: function() {
                                            showAddUserForm();
                                        }
                                    }
                                ]);
                            }
                        }, function(error) {
                            showMask('错误', '网络错误，请稍后重试', [
                                {
                                    text: '确定',
                                    class: 'btn-primary',
                                    callback: function() {
                                        closeMask();
                                    }
                                }
                            ]);
                        });
                    }
                }
            ]);
        }
        
        // 编辑用户
        function editUser(id, username, role, status) {
            var content = `
                <form id="editUserForm">
                    <input type="hidden" name="id" value="${id}">
                    
                    <div class="form-group">
                        <label class="form-label">用户名</label>
                        <div>${username}</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="role" class="form-label">角色</label>
                        <select id="role" name="role" class="form-control">
                            <option value="0" ${role == 0 ? 'selected' : ''}>普通用户</option>
                            <option value="1" ${role == 1 ? 'selected' : ''}>管理员</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status" class="form-label">状态</label>
                        <select id="status" name="status" class="form-control">
                            <option value="1" ${status == 1 ? 'selected' : ''}>正常</option>
                            <option value="0" ${status == 0 ? 'selected' : ''}>禁用</option>
                        </select>
                    </div>
                </form>
            `;
            
            showMask('编辑用户', content, [
                {
                    text: '取消',
                    class: 'btn-default',
                    callback: function() {
                        closeMask();
                    }
                },
                {
                    text: '保存',
                    class: 'btn-primary',
                    callback: function() {
                        var form = document.getElementById('editUserForm');
                        var userId = form.elements['id'].value;
                        var userRole = form.elements['role'].value;
                        var userStatus = form.elements['status'].value;
                        
                        // 不能禁用自己
                        if (userId == <?php echo $user['id']; ?> && userStatus == 0) {
                            showMask('提示', '不能禁用当前登录的账号', [
                                {
                                    text: '确定',
                                    class: 'btn-primary',
                                    callback: function() {
                                        editUser(id, username, role, status);
                                    }
                                }
                            ]);
                            return;
                        }
                        
                        // 发送更新用户的请求
                        ajaxRequest('../api/admin.php', {
                            act: 'updateUser',
                            id: userId,
                            role: userRole,
                            status: userStatus
                        }, function(response) {
                            if (response.code === 0) {
                                showMask('成功', '用户信息更新成功', [
                                    {
                                        text: '确定',
                                        class: 'btn-primary',
                                        callback: function() {
                                            window.location.reload();
                                        }
                                    }
                                ]);
                            } else {
                                showMask('错误', response.msg || '更新失败，请稍后重试', [
                                    {
                                        text: '确定',
                                        class: 'btn-primary',
                                        callback: function() {
                                            closeMask();
                                        }
                                    }
                                ]);
                            }
                        }, function(error) {
                            showMask('错误', '网络错误，请稍后重试', [
                                {
                                    text: '确定',
                                    class: 'btn-primary',
                                    callback: function() {
                                        closeMask();
                                    }
                                }
                            ]);
                        });
                    }
                }
            ]);
        }
        
        // 重置密码
        function resetPassword(id, username) {
            var content = `
                <form id="resetPasswordForm">
                    <input type="hidden" name="id" value="${id}">
                    <p>您将重置用户 <strong>${username}</strong> 的密码。</p>
                    
                    <div class="form-group">
                        <label for="new_password" class="form-label">新密码</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">确认密码</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                </form>
            `;
            
            showMask('重置密码', content, [
                {
                    text: '取消',
                    class: 'btn-default',
                    callback: function() {
                        closeMask();
                    }
                },
                {
                    text: '确定重置',
                    class: 'btn-primary',
                    callback: function() {
                        var form = document.getElementById('resetPasswordForm');
                        var userId = form.elements['id'].value;
                        var newPassword = form.elements['new_password'].value;
                        var confirmPassword = form.elements['confirm_password'].value;
                        
                        // 验证密码
                        if (newPassword.length < 6) {
                            showMask('提示', '密码长度不能少于6位', [
                                {
                                    text: '确定',
                                    class: 'btn-primary',
                                    callback: function() {
                                        resetPassword(id, username);
                                    }
                                }
                            ]);
                            return;
                        }
                        
                        if (newPassword !== confirmPassword) {
                            showMask('提示', '两次输入的密码不一致', [
                                {
                                    text: '确定',
                                    class: 'btn-primary',
                                    callback: function() {
                                        resetPassword(id, username);
                                    }
                                }
                            ]);
                            return;
                        }
                        
                        // 发送重置密码的请求
                        ajaxRequest('../api/admin.php', {
                            act: 'resetPassword',
                            id: userId,
                            password: newPassword
                        }, function(response) {
                            if (response.code === 0) {
                                showMask('成功', '密码重置成功', [
                                    {
                                        text: '确定',
                                        class: 'btn-primary',
                                        callback: function() {
                                            closeMask();
                                        }
                                    }
                                ]);
                            } else {
                                showMask('错误', response.msg || '重置失败，请稍后重试', [
                                    {
                                        text: '确定',
                                        class: 'btn-primary',
                                        callback: function() {
                                            closeMask();
                                        }
                                    }
                                ]);
                            }
                        }, function(error) {
                            showMask('错误', '网络错误，请稍后重试', [
                                {
                                    text: '确定',
                                    class: 'btn-primary',
                                    callback: function() {
                                        closeMask();
                                    }
                                }
                            ]);
                        });
                    }
                }
            ]);
        }
        
        // 更改用户状态
        function changeUserStatus(id, username, status) {
            var statusText = status == 1 ? '启用' : '禁用';
            
            showMask('确认操作', `确定要${statusText}用户 "${username}" 吗？`, [
                {
                    text: '取消',
                    class: 'btn-default',
                    callback: function() {
                        closeMask();
                    }
                },
                {
                    text: '确定',
                    class: status == 1 ? 'btn-green' : 'btn-red',
                    callback: function() {
                        // 发送更改状态的请求
                        ajaxRequest('../api/admin.php', {
                            act: 'updateUser',
                            id: id,
                            status: status
                        }, function(response) {
                            if (response.code === 0) {
                                showMask('成功', `用户已${statusText}`, [
                                    {
                                        text: '确定',
                                        class: 'btn-primary',
                                        callback: function() {
                                            window.location.reload();
                                        }
                                    }
                                ]);
                            } else {
                                showMask('错误', response.msg || '操作失败，请稍后重试', [
                                    {
                                        text: '确定',
                                        class: 'btn-primary',
                                        callback: function() {
                                            closeMask();
                                        }
                                    }
                                ]);
                            }
                        }, function(error) {
                            showMask('错误', '网络错误，请稍后重试', [
                                {
                                    text: '确定',
                                    class: 'btn-primary',
                                    callback: function() {
                                        closeMask();
                                    }
                                }
                            ]);
                        });
                    }
                }
            ]);
        }
    </script>
</body>
</html>
