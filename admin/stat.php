<?php

// 费心PHP+mysql极简通用多主题投票系统 V2025.05.08
// 演示地址1: http://demo.fxtp.cn
// 演示地址2: http://wevote.chalide.cn
// 文件路径: admin/stat.php
// 文件大小: 18093 字节
// 最后修改时间: 2025-05-09 07:06:38
// 作者: yujianyue
// 邮件: 15058593138@qq.com Bug反馈或意见建议
// 版权所有,保留发行权和署名权
/**
 * 数据统计页面
 * 显示投票统计和分析
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

// 获取所有投票主题
$topics = $db->getAll('vote', '', 'id, title', 'addtime DESC');

// 页面标题
$pageTitle = "数据统计";
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
        
        .filter-form {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .chart-container {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .chart-title {
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .vote-bar {
            height: 30px;
            background-color: #eee;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 5px;
        }
        
        .vote-bar-fill {
            height: 100%;
            background-color: #3498db;
            border-radius: 15px;
        }
        
        .vote-option-row {
            margin-bottom: 15px;
        }
        
        .vote-option-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .vote-option-name {
            font-weight: bold;
        }
        
        .participants-container {
            max-height: 300px;
            overflow-y: auto;
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
                <li class="admin-menu-item active"><a href="stat.php">数据统计</a></li>
                <li class="admin-menu-item"><a href="logs.php">系统日志</a></li>
            </ul>
        </div>
        
        <!-- 主体内容 -->
        <div class="admin-content">
            <div class="admin-header">
                <h2>数据统计</h2>
            </div>
            
            <!-- 筛选表单 -->
            <div class="filter-form">
                <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
                    <div>
                        <label for="topic_id">选择投票：</label>
                        <select id="topic_id" class="form-control" style="min-width: 200px;">
                            <option value="0">-- 显示所有投票统计 --</option>
                            <?php foreach ($topics as $topic): ?>
                                <option value="<?php echo $topic['id']; ?>"><?php echo htmlspecialchars($topic['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <button type="button" id="loadStatsBtn" class="btn btn-blue">加载统计</button>
                    </div>
                </div>
            </div>
            
            <!-- 总体统计 -->
            <div id="overall-stats" class="stats-container">
                <!-- 由JavaScript填充 -->
            </div>
            
            <!-- 详细统计 -->
            <div id="detailed-stats">
                <!-- 由JavaScript填充 -->
            </div>
        </div>
    </div>
    
    <script src="../inc/js.js"></script>
    <script>
        // 页面加载完成后加载所有投票统计
        document.addEventListener('DOMContentLoaded', function() {
            loadStatistics(0);
        });
        
        // 加载统计按钮点击事件
        document.getElementById('loadStatsBtn').addEventListener('click', function() {
            var topicId = document.getElementById('topic_id').value;
            loadStatistics(topicId);
        });
        
        // 加载统计数据
        function loadStatistics(topicId) {
            // 显示加载中
            document.getElementById('overall-stats').innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 20px;">加载中...</div>';
            document.getElementById('detailed-stats').innerHTML = '';
            
            // 发送请求获取统计数据
            ajaxRequest('../api/admin.php', {
                act: 'getVoteStats',
                topic_id: topicId
            }, function(response) {
                if (response.code === 0) {
                    if (topicId > 0) {
                        // 显示单个投票的详细统计
                        displaySingleVoteStats(response.data);
                    } else {
                        // 显示所有投票的总体统计
                        displayOverallStats(response.data);
                    }
                } else {
                    document.getElementById('overall-stats').innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 20px; color: #e74c3c;">加载失败：' + (response.msg || '未知错误') + '</div>';
                }
            }, function(error) {
                document.getElementById('overall-stats').innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 20px; color: #e74c3c;">网络错误，请稍后重试</div>';
            });
        }
        
        // 显示所有投票的总体统计
        function displayOverallStats(data) {
            var voteStats = data.voteStats;
            var overallStatsHtml = '';
            var detailedStatsHtml = '';
            
            // 计算总数据
            var totalVotes = 0;
            var totalUsers = 0;
            var totalViews = 0;
            var activeVotes = 0;
            
            voteStats.forEach(function(stat) {
                totalVotes += parseInt(stat.total_votes);
                totalUsers += parseInt(stat.user_count);
                totalViews += parseInt(stat.view_count);
                
                // 检查投票是否进行中
                var now = new Date();
                var startTime = new Date(stat.start_time);
                var endTime = new Date(stat.end_time);
                
                if (now >= startTime && now <= endTime) {
                    activeVotes++;
                }
            });
            
            // 总体统计卡片
            overallStatsHtml += `
                <div class="stat-card">
                    <div class="stat-value">${voteStats.length}</div>
                    <div class="stat-label">投票总数</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${activeVotes}</div>
                    <div class="stat-label">进行中的投票</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${totalVotes}</div>
                    <div class="stat-label">总投票数</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${totalUsers}</div>
                    <div class="stat-label">参与用户数</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${totalViews}</div>
                    <div class="stat-label">总浏览量</div>
                </div>
            `;
            
            // 每个投票的统计表格
            detailedStatsHtml += `
                <div class="chart-container">
                    <h3 class="chart-title">投票统计列表</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>投票标题</th>
                                    <th>开始时间</th>
                                    <th>结束时间</th>
                                    <th>总票数</th>
                                    <th>参与人数</th>
                                    <th>浏览量</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            
            voteStats.forEach(function(stat) {
                var startTime = new Date(stat.start_time);
                var endTime = new Date(stat.end_time);
                
                detailedStatsHtml += `
                    <tr>
                        <td>${stat.id}</td>
                        <td>${stat.title}</td>
                        <td>${formatDateTime(startTime)}</td>
                        <td>${formatDateTime(endTime)}</td>
                        <td>${stat.total_votes}</td>
                        <td>${stat.user_count}</td>
                        <td>${stat.view_count}</td>
                        <td>
                            <button class="btn btn-blue btn-sm" onclick="loadStatistics(${stat.id})">详细统计</button>
                            <a href="../vote.php?id=${stat.id}" target="_blank" class="btn btn-green btn-sm">查看投票</a>
                        </td>
                    </tr>
                `;
            });
            
            detailedStatsHtml += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            
            // 更新DOM
            document.getElementById('overall-stats').innerHTML = overallStatsHtml;
            document.getElementById('detailed-stats').innerHTML = detailedStatsHtml;
        }
        
        // 显示单个投票的详细统计
        function displaySingleVoteStats(data) {
            var vote = data.vote;
            var options = data.options;
            var totalVotes = data.totalVotes;
            var participants = data.participants;
            
            var overallStatsHtml = '';
            var detailedStatsHtml = '';
            
            // 总体统计卡片
            overallStatsHtml += `
                <div class="stat-card">
                    <div class="stat-value">${totalVotes}</div>
                    <div class="stat-label">总投票数</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${participants.length}</div>
                    <div class="stat-label">参与用户数</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${vote.iview}</div>
                    <div class="stat-label">浏览量</div>
                </div>
            `;
            
            // 投票信息
            detailedStatsHtml += `
                <div class="chart-container">
                    <h3 class="chart-title">投票信息</h3>
                    <div>
                        <p><strong>投票标题：</strong>${vote.title}</p>
                        <p><strong>投票描述：</strong>${vote.idesc}</p>
                        <p><strong>投票类型：</strong>${vote.itype == 0 ? '单选' : '多选（最多选' + vote.maxtime + '项）'}</p>
                        <p><strong>开始时间：</strong>${formatDateTime(new Date(vote.statime))}</p>
                        <p><strong>结束时间：</strong>${formatDateTime(new Date(vote.endtime))}</p>
                        <p><strong>状态：</strong>${vote.status == 1 ? '正常' : '禁用'}</p>
                    </div>
                </div>
            `;
            
            // 选项统计
            detailedStatsHtml += `
                <div class="chart-container">
                    <h3 class="chart-title">选项统计</h3>
                    <div id="options-chart">
            `;
            
            options.forEach(function(option) {
                detailedStatsHtml += `
                    <div class="vote-option-row">
                        <div class="vote-option-label">
                            <span class="vote-option-name">${option.name}</span>
                            <span>${option.count}票 (${option.percentage}%)</span>
                        </div>
                        <div class="vote-bar">
                            <div class="vote-bar-fill" style="width: ${option.percentage}%"></div>
                        </div>
                    </div>
                `;
            });
            
            detailedStatsHtml += `
                    </div>
                </div>
            `;
            
            // 参与用户
            detailedStatsHtml += `
                <div class="chart-container">
                    <h3 class="chart-title">参与用户（${participants.length}人）</h3>
                    <div class="participants-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>用户名</th>
                                    <th>投票时间</th>
                                    <th>IP地址</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            
            participants.forEach(function(participant) {
                detailedStatsHtml += `
                    <tr>
                        <td>${participant.username}</td>
                        <td>${formatDateTime(new Date(participant.vote_time))}</td>
                        <td>${participant.ip}</td>
                    </tr>
                `;
            });
            
            detailedStatsHtml += `
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div style="margin-top: 20px; text-align: center;">
                    <button class="btn btn-gray btn-lg" onclick="loadStatistics(0)">返回总体统计</button>
                    <a href="../vote.php?id=${vote.id}" target="_blank" class="btn btn-blue btn-lg">查看投票页面</a>
                </div>
            `;
            
            // 更新DOM
            document.getElementById('overall-stats').innerHTML = overallStatsHtml;
            document.getElementById('detailed-stats').innerHTML = detailedStatsHtml;
        }
        
        // 格式化日期时间
        function formatDateTime(date) {
            return date.getFullYear() + '-' + 
                   padZero(date.getMonth() + 1) + '-' + 
                   padZero(date.getDate()) + ' ' + 
                   padZero(date.getHours()) + ':' + 
                   padZero(date.getMinutes());
        }
        
        // 数字补零
        function padZero(num) {
            return num < 10 ? '0' + num : num;
        }
    </script>
</body>
</html>
