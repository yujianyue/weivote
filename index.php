<?php

// 费心PHP+mysql极简通用多主题投票系统 V2025.05.08
// 演示地址1: http://demo.fxtp.cn
// 演示地址2: http://wevote.chalide.cn
// 文件路径: index.php
// 文件大小: 8824 字节
// 最后修改时间: 2025-05-09 11:03:03
// 作者: yujianyue
// 邮件: 15058593138@qq.com Bug反馈或意见建议
// 版权所有,保留发行权和署名权
/**
 * 投票系统首页
 * 显示投票列表，支持分页和筛选
 */

// 引入必要文件
require_once 'inc/pubs.php';
require_once 'inc/sqls.php';

// 实例化数据库操作类
$db = new DB();

// 获取当前用户
$user = checkLogin();

// 获取分页参数
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$pageSize = 10;

// 获取筛选参数
$status = isset($_GET['status']) ? intval($_GET['status']) : -1;
$type = isset($_GET['type']) ? intval($_GET['type']) : -1;

// 构建查询条件
$whereConditions = [];
if ($status >= 0) {
    $whereConditions[] = "status = $status";
}
if ($type >= 0) {
    $whereConditions[] = "itype = $type";
}

// 当前时间，用于判断投票是否进行中
$now = date('Y-m-d H:i:s');
$whereStr = !empty($whereConditions) ? implode(' AND ', $whereConditions) : '';

// 获取总记录数
$total = $db->count('vote', $whereStr);

// 计算分页信息
$pagination = getPagination($total, $page, $pageSize);

// 获取投票列表
$orderBy = "addtime DESC";
$limit = "{$pagination['offset']}, {$pagination['pageSize']}";
$voteList = $db->getAll('vote', $whereStr, '*', $orderBy, $limit);

// 页面标题
$pageTitle = "投票系统首页";
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo getSiteTitle(); ?></title>
    <link rel="stylesheet" href="inc/css.css">
    <style>
        .vote-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .vote-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .vote-card-body {
            flex: 1;
        }
        
        .vote-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            color: #fff;
            font-size: 12px;
            margin-left: 5px;
        }
        
        .vote-status-active {
            background-color: #2ecc71;
        }
        
        .vote-status-pending {
            background-color: #f39c12;
        }
        
        .vote-status-ended {
            background-color: #95a5a6;
        }
        
        .vote-status-disabled {
            background-color: #e74c3c;
        }
        
        .filter-form {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border: 1px solid #eee;
        }
    </style>
</head>
<body>
    <!-- 顶部导航 -->
    <header class="header">
        <div class="header-container">
            <div class="logo"><?php echo getSiteTitle(); ?></div>
            <nav class="nav">
                <a href="index.php" class="nav-item active">首页</a>
                <?php if ($user): ?>
                    <a href="vote.php?act=my" class="nav-item">我的投票</a>
                    <?php if ($user['irole'] == 1): ?>
                        <a href="admin.php" class="nav-item">管理中心</a>
                    <?php endif; ?>
                    <a href="api/user.php?act=logout" class="nav-item">退出登录</a>
                <?php else: ?>
                    <a href="login.php" class="nav-item">登录</a>
                    <a href="reger.php" class="nav-item">注册</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    
    <!-- 主体内容 -->
    <div class="main">
        <h1>投票列表</h1>
        
        <!-- 筛选表单 -->
        <div class="filter-form">
            <form action="index.php" method="get">
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <div>
                        <label for="status">状态：</label>
                        <select name="status" id="status" class="form-control" style="width: auto;">
                            <option value="-1">全部</option>
                            <option value="1" <?php echo $status == 1 ? 'selected' : ''; ?>>正常</option>
                            <option value="0" <?php echo $status === 0 ? 'selected' : ''; ?>>禁用</option>
                        </select>
                    </div>
                    <div>
                        <label for="type">类型：</label>
                        <select name="type" id="type" class="form-control" style="width: auto;">
                            <option value="-1">全部</option>
                            <option value="0" <?php echo $type === 0 ? 'selected' : ''; ?>>单选</option>
                            <option value="1" <?php echo $type == 1 ? 'selected' : ''; ?>>多选</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-blue">筛选</button>
                        <a href="index.php" class="btn btn-gray">重置</a>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if ($user && $user['irole'] == 1): ?>
        <div style="margin-bottom: 20px;">
            <a href="admin/topic.php?act=add" class="btn btn-green">发起新投票</a>
        </div>
        <?php endif; ?>
        
        <!-- 投票列表 -->
        <?php if (!empty($voteList)): ?>
            <div class="vote-list">
                <?php foreach ($voteList as $vote): ?>
                    <?php
                    // 确定投票状态
                    $voteStatus = '';
                    $statusClass = '';
                    
                    if ($vote['status'] == 0) {
                        $voteStatus = '已禁用';
                        $statusClass = 'vote-status-disabled';
                    } else {
                        if ($vote['statime'] > $now) {
                            $voteStatus = '未开始';
                            $statusClass = 'vote-status-pending';
                        } elseif ($vote['endtime'] < $now) {
                            $voteStatus = '已结束';
                            $statusClass = 'vote-status-ended';
                        } else {
                            $voteStatus = '进行中';
                            $statusClass = 'vote-status-active';
                        }
                    }
                    ?>
                    <div class="card vote-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <?php echo htmlspecialchars($vote['title']); ?>
                                <span class="vote-status <?php echo $statusClass; ?>"><?php echo $voteStatus; ?></span>
                            </h3>
                        </div>
                        <div class="card-body vote-card-body">
                            <p>
                                <?php echo mb_substr(strip_tags($vote['idesc']), 0, 100, 'UTF-8'); ?>
                                <?php if (mb_strlen($vote['idesc'], 'UTF-8') > 100): ?>...<?php endif; ?>
                            </p>
                            <p><small>类型：<?php echo $vote['itype'] == 0 ? '单选' : '多选'; ?></small></p>
                            <p><small>时间：<?php echo date('Y-m-d', strtotime($vote['statime'])); ?> 至 <?php echo date('Y-m-d', strtotime($vote['endtime'])); ?></small></p>
                            <p><small>浏览：<?php echo $vote['iview']; ?>次</small></p>
                        </div>
                        <div class="card-footer">
                            <a href="vote.php?id=<?php echo $vote['id']; ?>" class="btn btn-blue">查看详情</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- 分页 -->
            <div class="pagination" id="pagination"></div>
        <?php else: ?>
            <div class="card">
                <div class="card-body" style="text-align: center; padding: 30px;">
                    <p>暂无投票数据</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="inc/js.js"></script>
    <script>
        // 初始化分页
        pagination('pagination', <?php echo $pagination['page']; ?>, <?php echo $pagination['totalPage']; ?>, function(page) {
            // 构建URL参数
            var params = new URLSearchParams(window.location.search);
            params.set('page', page);
            
            // 跳转到新页面
            window.location.href = 'index.php?' + params.toString();
        });
    </script>
</body>
</html>
