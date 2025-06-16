<?php

// 费心PHP+mysql极简通用多主题投票系统 V2025.05.08
// 演示地址1: http://demo.fxtp.cn
// 演示地址2: http://wevote.chalide.cn
// 文件路径: vote.php
// 文件大小: 27588 字节
// 最后修改时间: 2025-05-09 11:03:03
// 作者: yujianyue
// 邮件: 15058593138@qq.com Bug反馈或意见建议
// 版权所有,保留发行权和署名权
/**
 * 投票详情页面
 * 显示投票详情、选项，并允许用户提交投票
 */

// 引入必要文件
require_once 'inc/pubs.php';
require_once 'inc/sqls.php';

// 实例化数据库操作类
$db = new DB();

// 获取当前用户
$user = checkLogin();

// 获取投票ID
$voteId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action = isset($_GET['act']) ? $_GET['act'] : '';

// 如果是查看我的投票列表
if ($action == 'my') {
    // 必须登录
    if (!$user) {
        header('Location: login.php');
        exit;
    }
    
    // 获取分页参数
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $pageSize = 10;
    
    // 查询该用户参与的投票
    $sql = "SELECT DISTINCT v.* FROM " . $db->table('vote') . " v 
            INNER JOIN " . $db->table('recs') . " r ON v.id = r.topic_id 
            WHERE r.user_id = " . $user['id'] . " 
            ORDER BY r.vote_time DESC";
    
    $result = $db->query($sql);
    $myVotes = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $myVotes[] = $row;
        }
    }
    
    // 计算分页信息
    $total = count($myVotes);
    $pagination = getPagination($total, $page, $pageSize);
    
    // 按分页截取数据
    $myVotesList = array_slice($myVotes, $pagination['offset'], $pagination['pageSize']);
    
    // 页面标题
    $pageTitle = "我的投票";
    
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
                margin-top: 20px;
            }
            
            .vote-item {
                margin-bottom: 15px;
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
        </style>
    </head>
    <body>
        <!-- 顶部导航 -->
        <header class="header">
            <div class="header-container">
                <div class="logo"><?php echo getSiteTitle(); ?></div>
                <nav class="nav">
                    <a href="index.php" class="nav-item">首页</a>
                    <a href="vote.php?act=my" class="nav-item active">我的投票</a>
                    <?php if ($user && $user['irole'] == 1): ?>
                        <a href="admin.php" class="nav-item">管理中心</a>
                    <?php endif; ?>
                    <a href="api/user.php?act=logout" class="nav-item">退出登录</a>
                </nav>
            </div>
        </header>
        
        <!-- 主体内容 -->
        <div class="main">
            <h1>我的投票记录</h1>
            
            <?php if (!empty($myVotesList)): ?>
                <div class="vote-list">
                    <?php foreach ($myVotesList as $vote): ?>
                        <?php
                        // 确定投票状态
                        $now = date('Y-m-d H:i:s');
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
                        <div class="card vote-item">
                            <div class="card-body">
                                <h3>
                                    <?php echo htmlspecialchars($vote['title']); ?>
                                    <span class="vote-status <?php echo $statusClass; ?>"><?php echo $voteStatus; ?></span>
                                </h3>
                                <p>
                                    <?php echo mb_substr(strip_tags($vote['idesc']), 0, 100, 'UTF-8'); ?>
                                    <?php if (mb_strlen($vote['idesc'], 'UTF-8') > 100): ?>...<?php endif; ?>
                                </p>
                                <p><small>类型：<?php echo $vote['itype'] == 0 ? '单选' : '多选'; ?></small></p>
                                <p><small>时间：<?php echo date('Y-m-d', strtotime($vote['statime'])); ?> 至 <?php echo date('Y-m-d', strtotime($vote['endtime'])); ?></small></p>
                                <div style="margin-top: 10px;">
                                    <a href="vote.php?id=<?php echo $vote['id']; ?>" class="btn btn-blue">查看详情</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- 分页 -->
                <div class="pagination" id="pagination"></div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body" style="text-align: center; padding: 30px;">
                        <p>您还没有参与过任何投票</p>
                        <p style="margin-top: 15px;">
                            <a href="index.php" class="btn btn-blue">去投票</a>
                        </p>
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
                window.location.href = 'vote.php?' + params.toString();
            });
        </script>
    </body>
    </html>
    <?php
    exit;
}

// 如果没有投票ID，跳转到首页
if (!$voteId) {
    header('Location: index.php');
    exit;
}

// 获取投票信息
$vote = $db->getOne('vote', "id = $voteId");
if (!$vote) {
    header('Location: index.php');
    exit;
}

// 更新浏览次数
$db->update('vote', ['iview' => $vote['iview'] + 1], "id = $voteId");

// 获取投票选项
$options = $db->getAll('xuan', "topic_id = $voteId", '*', 'sort ASC, id ASC');

// 检查用户是否已投票
$hasVoted = false;
$userVotes = [];

if ($user) {
    $userVoteRecords = $db->getAll('recs', "topic_id = $voteId AND user_id = {$user['id']}");
    
    if (!empty($userVoteRecords)) {
        $hasVoted = true;
        foreach ($userVoteRecords as $record) {
            $userVotes[] = $record['option_id'];
        }
    }
}

// 获取投票结果
$voteResults = [];
$totalVotes = 0;

$sql = "SELECT option_id, COUNT(*) as vote_count FROM " . $db->table('recs') . " WHERE topic_id = $voteId GROUP BY option_id";
$result = $db->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $voteResults[$row['option_id']] = $row['vote_count'];
        $totalVotes += $row['vote_count'];
    }
}

// 当前时间，用于判断投票状态
$now = date('Y-m-d H:i:s');
$canVote = $vote['status'] == 1 && $vote['statime'] <= $now && $vote['endtime'] >= $now && !$hasVoted && $user;

// 页面标题
$pageTitle = $vote['title'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo getSiteTitle(); ?></title>
    <link rel="stylesheet" href="inc/css.css">
    <style>
        .vote-title {
            margin-bottom: 10px;
        }
        
        .vote-meta {
            color: #777;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .vote-desc {
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .vote-options {
            margin-bottom: 20px;
        }
        
        .vote-option {
            position: relative;
            padding-left: 30px;
        }
        
        .vote-option input {
            position: absolute;
            left: 5px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .vote-submit {
            margin-top: 20px;
        }
        
        .vote-result-count {
            margin-top: 5px;
            color: #777;
        }
        
        .vote-option-image {
            max-width: 100%;
            margin-bottom: 10px;
            border-radius: 3px;
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
    </style>
</head>
<body>
    <!-- 顶部导航 -->
    <header class="header">
        <div class="header-container">
            <div class="logo"><?php echo getSiteTitle(); ?></div>
            <nav class="nav">
                <a href="index.php" class="nav-item">首页</a>
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
        <div class="card">
            <div class="card-body">
                <h1 class="vote-title">
                    <?php echo htmlspecialchars($vote['title']); ?>
                    
                    <?php
                    // 显示投票状态
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
                    <span class="vote-status <?php echo $statusClass; ?>"><?php echo $voteStatus; ?></span>
                </h1>
                
                <div class="vote-meta">
                    <span>类型：<?php echo $vote['itype'] == 0 ? '单选' : "多选（最多选{$vote['maxtime']}项）"; ?></span>
                    <span style="margin-left: 15px;">时间：<?php echo date('Y-m-d H:i', strtotime($vote['statime'])); ?> 至 <?php echo date('Y-m-d H:i', strtotime($vote['endtime'])); ?></span>
                    <span style="margin-left: 15px;">浏览：<?php echo $vote['iview']; ?>次</span>
                </div>
                
                <div class="vote-desc">
                    <?php echo nl2br(htmlspecialchars($vote['idesc'])); ?>
                </div>
                
                <?php if (!empty($options)): ?>
                    <h3>投票选项</h3>
                    
                    <?php if ($user): ?>
                        <?php if ($canVote): ?>
                            <form id="voteForm">
                                <input type="hidden" name="topic_id" value="<?php echo $voteId; ?>">
                                <input type="hidden" name="max_options" value="<?php echo $vote['maxtime']; ?>">
                                <input type="hidden" name="vote_type" value="<?php echo $vote['itype']; ?>">
                                
                                <div class="vote-options">
                                    <?php foreach ($options as $option): ?>
                                        <div class="vote-option">
                                            <?php if ($vote['itype'] == 0): ?>
                                                <input type="radio" name="options[]" id="option_<?php echo $option['id']; ?>" value="<?php echo $option['id']; ?>">
                                            <?php else: ?>
                                                <input type="checkbox" name="options[]" id="option_<?php echo $option['id']; ?>" value="<?php echo $option['id']; ?>">
                                            <?php endif; ?>
                                            
                                            <label for="option_<?php echo $option['id']; ?>" class="card" style="cursor: pointer; display: block; margin-bottom: 15px;">
                                                <div class="card-body">
                                                    <h4><?php echo htmlspecialchars($option['name']); ?></h4>
                                                    
                                                    <?php if (!empty($option['imgs'])): ?>
                                                        <img src="<?php echo htmlspecialchars($option['imgs']); ?>" alt="<?php echo htmlspecialchars($option['name']); ?>" class="vote-option-image">
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($option['idesc'])): ?>
                                                        <p><?php echo nl2br(htmlspecialchars($option['idesc'])); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="vote-submit">
                                    <button type="submit" class="btn btn-blue btn-lg">提交投票</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <!-- 显示投票结果 -->
                            <div class="vote-options">
                                <?php foreach ($options as $option): ?>
                                    <?php 
                                    $voteCount = isset($voteResults[$option['id']]) ? $voteResults[$option['id']] : 0;
                                    $percentage = $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 1) : 0;
                                    $isUserVoted = in_array($option['id'], $userVotes);
                                    ?>
                                    <div class="card" style="margin-bottom: 15px; <?php echo $isUserVoted ? 'border: 2px solid #3498db;' : ''; ?>">
                                        <div class="card-body">
                                            <h4>
                                                <?php echo htmlspecialchars($option['name']); ?>
                                                <?php if ($isUserVoted): ?><span style="color: #3498db; margin-left: 5px;">(已选)</span><?php endif; ?>
                                            </h4>
                                            
                                            <?php if (!empty($option['imgs'])): ?>
                                                <img src="<?php echo htmlspecialchars($option['imgs']); ?>" alt="<?php echo htmlspecialchars($option['name']); ?>" class="vote-option-image">
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($option['idesc'])): ?>
                                                <p><?php echo nl2br(htmlspecialchars($option['idesc'])); ?></p>
                                            <?php endif; ?>
                                            
                                            <div class="vote-result">
                                                <div class="vote-bar">
                                                    <div class="vote-bar-fill" style="width: <?php echo $percentage; ?>%"></div>
                                                </div>
                                                <div class="vote-result-count">
                                                    <?php echo $voteCount; ?>票 (<?php echo $percentage; ?>%)
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <?php if (!$user): ?>
                                <div style="margin-top: 20px; text-align: center;">
                                    <p>您需要登录才能参与投票</p>
                                    <p style="margin-top: 10px;">
                                        <a href="login.php" class="btn btn-blue">立即登录</a>
                                        <a href="reger.php" class="btn btn-green">注册账号</a>
                                    </p>
                                </div>
                            <?php elseif ($vote['status'] == 0): ?>
                                <div style="margin-top: 20px; text-align: center;">
                                    <p>该投票已被禁用</p>
                                </div>
                            <?php elseif ($vote['statime'] > $now): ?>
                                <div style="margin-top: 20px; text-align: center;">
                                    <p>该投票尚未开始，请在开始时间后再来参与</p>
                                </div>
                            <?php elseif ($vote['endtime'] < $now): ?>
                                <div style="margin-top: 20px; text-align: center;">
                                    <p>该投票已经结束</p>
                                </div>
                            <?php elseif ($hasVoted): ?>
                                <div style="margin-top: 20px; text-align: center;">
                                    <p>您已经参与过该投票，感谢您的参与！</p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- 显示投票结果（未登录用户） -->
                        <div class="vote-options">
                            <?php foreach ($options as $option): ?>
                                <?php 
                                $voteCount = isset($voteResults[$option['id']]) ? $voteResults[$option['id']] : 0;
                                $percentage = $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 1) : 0;
                                ?>
                                <div class="card" style="margin-bottom: 15px;">
                                    <div class="card-body">
                                        <h4><?php echo htmlspecialchars($option['name']); ?></h4>
                                        
                                        <?php if (!empty($option['imgs'])): ?>
                                            <img src="<?php echo htmlspecialchars($option['imgs']); ?>" alt="<?php echo htmlspecialchars($option['name']); ?>" class="vote-option-image">
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($option['idesc'])): ?>
                                            <p><?php echo nl2br(htmlspecialchars($option['idesc'])); ?></p>
                                        <?php endif; ?>
                                        
                                        <div class="vote-result">
                                            <div class="vote-bar">
                                                <div class="vote-bar-fill" style="width: <?php echo $percentage; ?>%"></div>
                                            </div>
                                            <div class="vote-result-count">
                                                <?php echo $voteCount; ?>票 (<?php echo $percentage; ?>%)
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div style="margin-top: 20px; text-align: center;">
                            <p>您需要登录才能参与投票</p>
                            <p style="margin-top: 10px;">
                                <a href="login.php" class="btn btn-blue">立即登录</a>
                                <a href="reger.php" class="btn btn-green">注册账号</a>
                            </p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 30px;">
                        <p>暂无投票选项</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="inc/js.js"></script>
    <script>
        <?php if ($canVote): ?>
        // 投票表单提交
        document.getElementById('voteForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            var form = this;
            var voteType = parseInt(form.elements['vote_type'].value);
            var maxOptions = parseInt(form.elements['max_options'].value);
            var options = [];
            
            // 获取选中的选项
            var checkboxes = form.querySelectorAll('input[name="options[]"]:checked');
            checkboxes.forEach(function(checkbox) {
                options.push(checkbox.value);
            });
            
            // 验证
            if (options.length === 0) {
                showMask('提示', '请至少选择一个选项', [
                    {
                        text: '确定',
                        class: 'btn-primary',
                        callback: function() {
                            closeMask();
                        }
                    }
                ]);
                return;
            }
            
            if (voteType === 1 && options.length > maxOptions) {
                showMask('提示', '最多只能选择 ' + maxOptions + ' 项', [
                    {
                        text: '确定',
                        class: 'btn-primary',
                        callback: function() {
                            closeMask();
                        }
                    }
                ]);
                return;
            }
            
            // 发送投票请求
            ajaxRequest('api/vote.php', {
                act: 'submit',
                topic_id: form.elements['topic_id'].value,
                options: options.join(',')
            }, function(response) {
                if (response.code === 0) {
                    // 投票成功，刷新页面显示结果
                    showMask('投票成功', '感谢您的参与！', [
                        {
                            text: '查看结果',
                            class: 'btn-primary',
                            callback: function() {
                                window.location.reload();
                            }
                        }
                    ]);
                } else {
                    // 显示错误信息
                    showMask('投票失败', response.msg || '提交失败，请稍后重试', [
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
                showMask('请求失败', '网络错误，请稍后重试', [
                    {
                        text: '确定',
                        class: 'btn-primary',
                        callback: function() {
                            closeMask();
                        }
                    }
                ]);
            });
        });
        
        // 单选模式下，点击卡片选中对应的单选按钮
        if (document.querySelector('input[type="radio"][name="options[]"]')) {
            var cards = document.querySelectorAll('.vote-option label.card');
            cards.forEach(function(card) {
                card.addEventListener('click', function() {
                    var radio = this.previousElementSibling;
                    if (radio && radio.type === 'radio') {
                        radio.checked = true;
                    }
                });
            });
        }
        <?php endif; ?>
    </script>
</body>
</html>
