<?php

// 费心PHP+mysql极简通用多主题投票系统 V2025.05.08
// 演示地址1: http://demo.fxtp.cn
// 演示地址2: http://wevote.chalide.cn
// 文件路径: admin/index.php
// 文件大小: 9255 字节
// 最后修改时间: 2025-05-09 06:58:56
// 作者: yujianyue
// 邮件: 15058593138@qq.com Bug反馈或意见建议
// 版权所有,保留发行权和署名权
/**
 * 管理后台首页
 * 显示系统概况及最近活动
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

// 统计数据
// 1. 用户总数
$totalUsers = $db->count('users');
// 2. 投票主题总数
$totalTopics = $db->count('vote');
// 3. 进行中的投票数
$now = date('Y-m-d H:i:s');
$activeTopics = $db->count('vote', "status = 1 AND statime <= '$now' AND endtime >= '$now'");
// 4. 总投票记录数
$totalVotes = $db->count('recs');

// 最近投票记录
$recentVotes = [];
$sql = "SELECT r.*, u.username, v.title 
        FROM " . $db->table('recs') . " r
        LEFT JOIN " . $db->table('users') . " u ON r.user_id = u.id
        LEFT JOIN " . $db->table('vote') . " v ON r.topic_id = v.id
        ORDER BY r.vote_time DESC LIMIT 10";
$result = $db->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recentVotes[] = $row;
    }
}

// 最近操作日志
$recentLogs = [];
$sql = "SELECT l.*, u.username 
        FROM " . $db->table('logs') . " l
        LEFT JOIN " . $db->table('users') . " u ON l.user_id = u.id
        ORDER BY l.logtime DESC LIMIT 10";
$result = $db->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recentLogs[] = $row;
    }
}

// 页面标题
$pageTitle = "管理后台";
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
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: bold;
            color: #3498db;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .table-container {
            margin-bottom: 30px;
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
                <li class="admin-menu-item active"><a href="index.php">系统概况</a></li>
                <li class="admin-menu-item"><a href="topic.php">投票管理</a></li>
                <li class="admin-menu-item"><a href="user.php">用户管理</a></li>
                <li class="admin-menu-item"><a href="stat.php">数据统计</a></li>
                <li class="admin-menu-item"><a href="logs.php">系统日志</a></li>
            </ul>
        </div>
        
        <!-- 主体内容 -->
        <div class="admin-content">
            <div class="admin-header">
                <h2>系统概况</h2>
                <div>
                    <span>欢迎您，<?php echo htmlspecialchars($user['username']); ?></span>
                </div>
            </div>
            
            <!-- 统计卡片 -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $totalUsers; ?></div>
                    <div class="stat-label">用户总数</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?php echo $totalTopics; ?></div>
                    <div class="stat-label">投票主题总数</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?php echo $activeTopics; ?></div>
                    <div class="stat-label">进行中的投票</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?php echo $totalVotes; ?></div>
                    <div class="stat-label">总投票记录数</div>
                </div>
            </div>
            
            <!-- 最近投票记录 -->
            <div class="table-container">
                <h3>最近投票记录</h3>
                <table>
                    <thead>
                        <tr>
                            <th>用户</th>
                            <th>投票主题</th>
                            <th>投票时间</th>
                            <th>IP地址</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentVotes)): ?>
                            <?php foreach ($recentVotes as $vote): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($vote['username']); ?></td>
                                    <td><?php echo htmlspecialchars($vote['title']); ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($vote['vote_time'])); ?></td>
                                    <td><?php echo htmlspecialchars($vote['ip']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">暂无投票记录</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- 最近操作日志 -->
            <div class="table-container">
                <h3>最近操作日志</h3>
                <table>
                    <thead>
                        <tr>
                            <th>用户</th>
                            <th>操作类型</th>
                            <th>操作内容</th>
                            <th>IP地址</th>
                            <th>时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentLogs)): ?>
                            <?php foreach ($recentLogs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['username']); ?></td>
                                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                                    <td><?php echo htmlspecialchars($log['idesc']); ?></td>
                                    <td><?php echo htmlspecialchars($log['ip']); ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($log['logtime'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">暂无操作日志</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="../inc/js.js"></script>
</body>
</html>
