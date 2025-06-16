<?php

// 费心PHP+mysql极简通用多主题投票系统 V2025.05.08
// 演示地址1: http://demo.fxtp.cn
// 演示地址2: http://wevote.chalide.cn
// 文件路径: admin/logs.php
// 文件大小: 12997 字节
// 最后修改时间: 2025-05-09 10:36:31
// 作者: yujianyue
// 邮件: 15058593138@qq.com Bug反馈或意见建议
// 版权所有,保留发行权和署名权
/**
 * 系统日志页面
 * 查看系统操作日志
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

// 获取所有用户，用于筛选
$users = $db->getAll('users', '', 'id, username', 'id ASC');

// 获取所有操作类型，用于筛选
$sql = "SELECT DISTINCT action FROM " . $db->table('logs');
$result = $db->query($sql);
$actionTypes = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $actionTypes[] = $row['action'];
    }
}

// 页面标题
$pageTitle = "系统日志";
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
        
        .logs-container {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .export-btn {
            margin-left: 10px;
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
                <li class="admin-menu-item"><a href="user.php">用户管理</a></li>
                <li class="admin-menu-item"><a href="stat.php">数据统计</a></li>
                <li class="admin-menu-item active"><a href="logs.php">系统日志</a></li>
            </ul>
        </div>
        
        <!-- 主体内容 -->
        <div class="admin-content">
            <div class="admin-header">
                <h2>系统日志</h2>
                <div>
                    <button class="btn btn-blue" id="exportBtn">导出日志</button>
                </div>
            </div>
            
            <!-- 筛选表单 -->
            <div class="filter-form">
                <form id="filterForm">
                    <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
                        <div>
                            <label for="user_id">用户：</label>
                            <select id="user_id" name="user_id" class="form-control">
                                <option value="0">全部用户</option>
                                <?php foreach ($users as $userData): ?>
                                    <option value="<?php echo $userData['id']; ?>"><?php echo htmlspecialchars($userData['username']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="action">操作类型：</label>
                            <select id="action" name="action" class="form-control">
                                <option value="">全部操作</option>
                                <?php foreach ($actionTypes as $actionType): ?>
                                    <option value="<?php echo $actionType; ?>"><?php echo htmlspecialchars($actionType); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="start_date">开始日期：</label>
                            <input type="date" id="start_date" name="start_date" class="form-control">
                        </div>
                        <div>
                            <label for="end_date">结束日期：</label>
                            <input type="date" id="end_date" name="end_date" class="form-control">
                        </div>
                        <div>
                            <button type="button" id="searchBtn" class="btn btn-blue">搜索</button>
                            <button type="button" id="resetBtn" class="btn btn-gray">重置</button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- 日志列表 -->
            <div class="logs-container">
                <div id="logsTable" class="table-container">
                    <div style="text-align: center; padding: 20px;">加载中...</div>
                </div>
                
                <!-- 分页 -->
                <div class="pagination" id="pagination"></div>
            </div>
        </div>
    </div>
    
    <script src="../inc/js.js"></script>
    <script>
        // 当前页码
        var currentPage = 1;
        
        // 页面加载完成后加载日志
        document.addEventListener('DOMContentLoaded', function() {
            loadLogs(1);
        });
        
        // 搜索按钮点击事件
        document.getElementById('searchBtn').addEventListener('click', function() {
            loadLogs(1);
        });
        
        // 重置按钮点击事件
        document.getElementById('resetBtn').addEventListener('click', function() {
            document.getElementById('filterForm').reset();
            loadLogs(1);
        });
        
        // 导出按钮点击事件
        document.getElementById('exportBtn').addEventListener('click', function() {
            exportLogs();
        });
        
        // 加载日志数据
        function loadLogs(page) {
            currentPage = page;
            
            // 获取筛选参数
            var userId = document.getElementById('user_id').value;
            var action = document.getElementById('action').value;
            var startDate = document.getElementById('start_date').value;
            var endDate = document.getElementById('end_date').value;
            
            // 显示加载中
            document.getElementById('logsTable').innerHTML = '<div style="text-align: center; padding: 20px;">加载中...</div>';
            
            // 发送请求获取日志数据
            ajaxRequest('../api/admin.php', {
                act: 'getLogs',
                page: page,
                page_size: 20,
                user_id: userId,
                action: action,
                start_date: startDate,
                end_date: endDate
            }, function(response) {
                if (response.code === 0) {
                    displayLogs(response.data);
                } else {
                    document.getElementById('logsTable').innerHTML = '<div style="text-align: center; padding: 20px; color: #e74c3c;">加载失败：' + (response.msg || '未知错误') + '</div>';
                    document.getElementById('pagination').innerHTML = '';
                }
            }, function(error) {
                document.getElementById('logsTable').innerHTML = '<div style="text-align: center; padding: 20px; color: #e74c3c;">网络错误，请稍后重试</div>';
                document.getElementById('pagination').innerHTML = '';
            });
        }
        
        // 显示日志列表
        function displayLogs(data) {
            var logs = data.logs;
            var pagination = data.pagination;
            
            // 如果没有日志
            if (logs.length === 0) {
                document.getElementById('logsTable').innerHTML = '<div style="text-align: center; padding: 20px;">暂无日志数据</div>';
                document.getElementById('pagination').innerHTML = '';
                return;
            }
            
            // 构建表格HTML
            var tableHtml = `
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>用户</th>
                            <th>操作类型</th>
                            <th>操作内容</th>
                            <th>IP地址</th>
                            <th>时间</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            // 遍历日志数据
            logs.forEach(function(log) {
                var logTime = new Date(log.logtime);
                
                tableHtml += `
                    <tr>
                        <td>${log.id}</td>
                        <td>${log.username || '未知用户'}</td>
                        <td>${log.action}</td>
                        <td>${log.idesc || ''}</td>
                        <td>${log.ip}</td>
                        <td>${formatDateTime(logTime)}</td>
                    </tr>
                `;
            });
            
            tableHtml += `
                    </tbody>
                </table>
            `;
            
           // 更新DOM
            document.getElementById('logsTable').innerHTML = tableHtml;
            
            // 初始化分页
            if (typeof pagination === 'function' && pagination.page !== undefined && pagination.totalPage !== undefined) {
                pagination('pagination', pagination.page, pagination.totalPage, function(page) {
                    loadLogs(page);
                });
            } else {
                console.error('Pagination data error:', pagination);
                document.getElementById('pagination').innerHTML = '';
            }
        }
        
        // 导出日志
        function exportLogs() {
            // 获取筛选参数
            var userId = document.getElementById('user_id').value;
            var action = document.getElementById('action').value;
            var startDate = document.getElementById('start_date').value;
            var endDate = document.getElementById('end_date').value;
            
            // 构建导出URL
            var exportUrl = '../api/admin.php?act=exportLogs';
            
            if (userId) exportUrl += '&user_id=' + userId;
            if (action) exportUrl += '&action=' + encodeURIComponent(action);
            if (startDate) exportUrl += '&start_date=' + startDate;
            if (endDate) exportUrl += '&end_date=' + endDate;
            
            // 跳转到导出URL
            window.location.href = exportUrl;
        }
        
        // 格式化日期时间
        function formatDateTime(date) {
            return date.getFullYear() + '-' + 
                   padZero(date.getMonth() + 1) + '-' + 
                   padZero(date.getDate()) + ' ' + 
                   padZero(date.getHours()) + ':' + 
                   padZero(date.getMinutes()) + ':' + 
                   padZero(date.getSeconds());
        }
        
        // 数字补零
        function padZero(num) {
            return num < 10 ? '0' + num : num;
        }
    </script>
</body>
</html>
