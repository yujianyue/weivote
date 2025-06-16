<?php

// 费心PHP+mysql极简通用多主题投票系统 V2025.05.08
// 演示地址1: http://demo.fxtp.cn
// 演示地址2: http://wevote.chalide.cn
// 文件路径: admin/topic.php
// 文件大小: 18539 字节
// 最后修改时间: 2025-05-09 09:23:50
// 作者: yujianyue
// 邮件: 15058593138@qq.com Bug反馈或意见建议
// 版权所有,保留发行权和署名权
/**
 * 投票管理页面
 * 管理投票主题和选项
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

// 获取操作类型
$action = isset($_GET['act']) ? $_GET['act'] : 'list';

// 根据操作类型显示不同页面
switch ($action) {
    // 添加投票页面
    case 'add':
        $pageTitle = "添加投票";
        include 'topic_form.php';
        break;
    
    // 编辑投票页面
    case 'edit':
        $pageTitle = "编辑投票";
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$id) {
            header('Location: topic.php');
            exit;
        }
        
        // 获取投票信息
        $vote = $db->getOne('vote', "id = $id");
        if (!$vote) {
            header('Location: topic.php');
            exit;
        }
        
        include 'topic_form.php';
        break;
    
    // 管理选项页面
    case 'options':
        $pageTitle = "管理选项";
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$id) {
            header('Location: topic.php');
            exit;
        }
        
        // 获取投票信息
        $vote = $db->getOne('vote', "id = $id");
        if (!$vote) {
            header('Location: topic.php');
            exit;
        }
        
        // 获取选项列表
        $options = $db->getAll('xuan', "topic_id = $id", '*', 'sort ASC, id ASC');
        
        include 'topic_options.php';
        break;
    
    // 投票列表页面（默认）
    default:
        $pageTitle = "投票管理";
        
        // 获取分页参数
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $pageSize = 10;
        
        // 获取筛选参数
        $status = isset($_GET['status']) ? intval($_GET['status']) : -1;
        $type = isset($_GET['type']) ? intval($_GET['type']) : -1;
        $keyword = isset($_GET['keyword']) ? safeFilter($_GET['keyword']) : '';
        
        // 构建查询条件
        $whereConditions = [];
        if ($status >= 0) {
            $whereConditions[] = "status = $status";
        }
        if ($type >= 0) {
            $whereConditions[] = "itype = $type";
        }
        if ($keyword) {
            $whereConditions[] = "(title LIKE '%$keyword%' OR idesc LIKE '%$keyword%')";
        }
        
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
        $pageTitle = "投票管理";
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
                
                .vote-status {
                    display: inline-block;
                    padding: 3px 8px;
                    border-radius: 3px;
                    color: #fff;
                    font-size: 12px;
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
                    <div class="admin-header" style="height:40px;">
                    </div>
                    
                    <!-- 筛选表单 -->
                    <div class="filter-form">
                        <form action="topic.php" method="get">
                            <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
                                <div>
                                    <label for="keyword">关键词：</label>
                                    <input type="text" name="keyword" id="keyword" class="form-control" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="标题或描述">
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
                                    <label for="type">类型：</label>
                                    <select name="type" id="type" class="form-control">
                                        <option value="-1">全部</option>
                                        <option value="0" <?php echo $type === 0 ? 'selected' : ''; ?>>单选</option>
                                        <option value="1" <?php echo $type == 1 ? 'selected' : ''; ?>>多选</option>
                                    </select>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-blue">搜索</button>
                                  <a href="topic.php" class="btn btn-gray">重置</a>
                                  <a href="topic.php?act=add" class="btn btn-green">添加投票</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- 投票列表 -->
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>标题</th>
                                    <th>类型</th>
                                    <th>开始时间</th>
                                    <th>结束时间</th>
                                    <th>状态</th>
                                    <th>浏览量</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($voteList)): ?>
                                    <?php foreach ($voteList as $vote): 
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
                                        <tr>
                                            <td><?php echo $vote['id']; ?></td>
                                            <td><?php echo htmlspecialchars($vote['title']); ?></td>
                                            <td><?php echo $vote['itype'] == 0 ? '单选' : "多选({$vote['maxtime']}项)"; ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($vote['statime'])); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($vote['endtime'])); ?></td>
                                            <td><span class="vote-status <?php echo $statusClass; ?>"><?php echo $voteStatus; ?></span></td>
                                            <td><?php echo $vote['iview']; ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($vote['addtime'])); ?></td>
                                            <td class="operation-btns">
                                                <a href="../vote.php?id=<?php echo $vote['id']; ?>" class="btn btn-blue btn-sm" target="_blank">查看</a>
                                                <a href="topic.php?act=options&id=<?php echo $vote['id']; ?>" class="btn btn-green btn-sm">选项</a>
                                                <a href="topic.php?act=edit&id=<?php echo $vote['id']; ?>" class="btn btn-gray btn-sm">编辑</a>
                                                <button class="btn btn-red btn-sm" onclick="deleteTopic(<?php echo $vote['id']; ?>, '<?php echo htmlspecialchars(addslashes($vote['title'])); ?>')">删除</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" style="text-align: center;">暂无投票数据</td>
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
                    params.delete('act');
                    params.set('page', page);
                    
                    // 跳转到新页面
                    window.location.href = 'topic.php?' + params.toString();
                });
                
                // 删除投票
                function deleteTopic(id, title) {
                    showMask('确认删除', '确定要删除投票 "' + title + '" 吗？<br><span style="color: #e74c3c;">注意：删除后将同时删除该投票的所有选项和投票记录，且不可恢复！</span>', [
                        {
                            text: '取消',
                            class: 'btn-default',
                            callback: function() {
                                closeMask();
                            }
                        },
                        {
                            text: '确定删除',
                            class: 'btn-danger',
                            callback: function() {
                                // 发送删除请求
                                ajaxRequest('../api/topic.php', {
                                    act: 'delete',
                                    id: id
                                }, function(response) {
                                    if (response.code === 0) {
                                        // 删除成功，刷新页面
                                        showMask('操作成功', '投票删除成功', [
                                            {
                                                text: '确定',
                                                class: 'btn-primary',
                                                callback: function() {
                                                    window.location.reload();
                                                }
                                            }
                                        ]);
                                    } else {
                                        // 显示错误信息
                                        showMask('操作失败', response.msg || '删除失败，请稍后重试', [
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
                            }
                        }
                    ]);
                }
            </script>
        </body>
        </html>
        <?php
        break;
}
