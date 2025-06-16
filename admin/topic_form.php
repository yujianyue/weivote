<?php

// 费心PHP+mysql极简通用多主题投票系统 V2025.05.08
// 演示地址1: http://demo.fxtp.cn
// 演示地址2: http://wevote.chalide.cn
// 文件路径: admin/topic_form.php
// 文件大小: 13788 字节
// 最后修改时间: 2025-05-09 10:55:53
// 作者: yujianyue
// 邮件: 15058593138@qq.com Bug反馈或意见建议
// 版权所有,保留发行权和署名权
/**
 * 投票添加/编辑表单页面
 * 该文件由topic.php包含使用
 */

// 检查是否已经引入必要文件
if (!isset($db) || !isset($user) || !isset($action)) {
    header('Location: topic.php');
    exit;
}

// 初始化表单数据
$formData = [
    'id' => 0,
    'title' => '',
    'idesc' => '',
    'statime' => date('Y-m-d H:i:s', strtotime('+1 hour')),
    'endtime' => date('Y-m-d H:i:s', strtotime('+7 days')),
    'itype' => 0,
    'maxtime' => 1,
    'status' => 1
];

// 如果是编辑模式，加载现有数据
if ($action == 'edit' && isset($vote)) {
    $formData = array_merge($formData, $vote);
}
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
        
        .form-card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .form-actions {
            margin-top: 20px;
            text-align: center;
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
                <li class="admin-menu-item active"><a href="topic.php">投票管理</a></li>
                <li class="admin-menu-item"><a href="user.php">用户管理</a></li>
                <li class="admin-menu-item"><a href="stat.php">数据统计</a></li>
                <li class="admin-menu-item"><a href="logs.php">系统日志</a></li>
            </ul>
        </div>
        
        <!-- 主体内容 -->
        <div class="admin-content">
            <div class="admin-header">
                <h2><?php echo $pageTitle; ?></h2>
                <div>
                    <a href="topic.php" class="btn btn-gray">返回列表</a>
                </div>
            </div>
            
            <!-- 表单卡片 -->
            <div class="form-card">
                <form id="topicForm">
                    <?php if ($action == 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $formData['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="title" class="form-label">投票标题</label>
                        <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($formData['title']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="desc" class="form-label">投票描述</label>
                        <textarea id="desc" name="desc" class="form-control" rows="5"><?php echo htmlspecialchars($formData['idesc']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="type" class="form-label">投票类型</label>
                        <select id="type" name="type" class="form-control">
                            <option value="0" <?php echo $formData['itype'] == 0 ? 'selected' : ''; ?>>单选</option>
                            <option value="1" <?php echo $formData['itype'] == 1 ? 'selected' : ''; ?>>多选</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="maxOptionsGroup" style="<?php echo $formData['itype'] == 0 ? 'display:none;' : ''; ?>">
                        <label for="maxtime" class="form-label">最多可选项数</label>                      
                        <input type="number" id="maxtime" name="maxtime" class="form-control" min="1" value="<?php echo $formData['maxtime']; ?>" <?php echo $formData['itype'] == 0 ? '' : 'required'; ?>>
                    </div>
                    
                    <div class="form-group">
                        <label for="statime" class="form-label">开始时间</label>
                        <input type="datetime-local" id="statime" name="statime" class="form-control" value="<?php echo date('Y-m-d\TH:i', strtotime($formData['statime'])); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="endtime" class="form-label">结束时间</label>
                        <input type="datetime-local" id="endtime" name="endtime" class="form-control" value="<?php echo date('Y-m-d\TH:i', strtotime($formData['endtime'])); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="status" class="form-label">状态</label>
                        <select id="status" name="status" class="form-control">
                            <option value="1" <?php echo $formData['status'] == 1 ? 'selected' : ''; ?>>正常</option>
                            <option value="0" <?php echo $formData['status'] == 0 ? 'selected' : ''; ?>>禁用</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-blue btn-lg">保存</button>
                        <a href="topic.php" class="btn btn-gray btn-lg">取消</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../inc/js.js"></script>
    <script>
        // 监听投票类型变化，控制最多可选项数显示
        document.getElementById('type').addEventListener('change', function() {
            var maxOptionsGroup = document.getElementById('maxOptionsGroup');
            if (this.value == '1') {
                maxOptionsGroup.style.display = 'block';
            } else {
                maxOptionsGroup.style.display = 'none';
            }
        });
        
        // 表单提交
        document.getElementById('topicForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // 获取表单数据
            var form = this;
            var title = form.elements['title'].value.trim();
            var desc = form.elements['desc'].value.trim();
            var type = form.elements['type'].value;
            var maxtime = form.elements['maxtime'].value;
            var statime = form.elements['statime'].value;
            var endtime = form.elements['endtime'].value;
            var status = form.elements['status'].value;
            
            // 验证表单
            if (!title) {
                showMask('提示', '请输入投票标题', [
                    {
                        text: '确定',
                        class: 'btn-primary',
                        callback: function() {
                            closeMask();
                            form.elements['title'].focus();
                        }
                    }
                ]);
                return;
            }
            
            if (!statime || !endtime) {
                showMask('提示', '请选择开始和结束时间', [
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
            
            if (new Date(statime) >= new Date(endtime)) {
                showMask('提示', '结束时间必须晚于开始时间', [
                    {
                        text: '确定',
                        class: 'btn-primary',
                        callback: function() {
                            closeMask();
                            form.elements['endtime'].focus();
                        }
                    }
                ]);
                return;
            }
            
            if (type == 1 && maxtime < 2) {
                showMask('提示', '多选投票最少可选2项', [
                    {
                        text: '确定',
                        class: 'btn-primary',
                        callback: function() {
                            closeMask();
                            form.elements['maxtime'].focus();
                        }
                    }
                ]);
                return;
            }
            
            // 准备提交数据
            var postData = {
                title: title,
                desc: desc,
                type: type,
                maxtime: maxtime,
                statime: statime.replace('T', ' '),
                endtime: endtime.replace('T', ' '),
                status: status
            };
            
            // 如果是编辑模式，添加ID
            <?php if ($action == 'edit'): ?>
                postData.id = <?php echo $formData['id']; ?>;
                postData.act = 'update';
            <?php else: ?>
                postData.act = 'add';
            <?php endif; ?>
            
            // 发送请求
            ajaxRequest('../api/topic.php', postData, function(response) {
                if (response.code === 0) {
                    <?php if ($action == 'edit'): ?>
                        showMask('成功', '投票更新成功', [
                            {
                                text: '返回列表',
                                class: 'btn-primary',
                                callback: function() {
                                    window.location.href = 'topic.php';
                                }
                            },
                            {
                                text: '管理选项',
                                class: 'btn-blue',
                                callback: function() {
                                    window.location.href = 'topic.php?act=options&id=<?php echo $formData['id']; ?>';
                                }
                            }
                        ]);
                    <?php else: ?>
                        showMask('成功', '投票创建成功', [
                            {
                                text: '返回列表',
                                class: 'btn-default',
                                callback: function() {
                                    window.location.href = 'topic.php';
                                }
                            },
                            {
                                text: '管理选项',
                                class: 'btn-primary',
                                callback: function() {
                                    window.location.href = 'topic.php?act=options&id=' + response.data.id;
                                }
                            }
                        ]);
                    <?php endif; ?>
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
        });
    </script>
</body>
</html>
