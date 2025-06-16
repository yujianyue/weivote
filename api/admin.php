<?php

// 费心PHP+mysql极简通用多主题投票系统 V2025.05.08
// 演示地址1: http://demo.fxtp.cn
// 演示地址2: http://wevote.chalide.cn
// 文件路径: api/admin.php
// 文件大小: 12244 字节
// 最后修改时间: 2025-05-09 07:05:24
// 作者: yujianyue
// 邮件: 15058593138@qq.com Bug反馈或意见建议
// 版权所有,保留发行权和署名权
/**
 * 管理员API
 * 提供管理后台所需的API接口
 */

// 引入必要文件
require_once '../inc/pubs.php';
require_once '../inc/sqls.php';

// 获取操作类型
$action = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';

// 实例化数据库操作类
$db = new DB();

// 根据操作类型执行相应操作
switch ($action) {
    // 添加用户
    case 'addUser':
        // 验证管理员权限
        $admin = checkAuth('addUser', true);
        if (!$admin) {
            exit;
        }
        
        // 获取参数
        $username = isset($_POST['username']) ? safeFilter($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $role = isset($_POST['role']) ? intval($_POST['role']) : 0;
        
        // 参数验证
        if (empty($username) || empty($password)) {
            ajaxReturn(1, '用户名和密码不能为空');
        }
        
        // 验证手机号格式
        if (!preg_match('/^1[3456789]\d{9}$/', $username)) {
            ajaxReturn(1, '请输入正确的手机号码');
        }
        
        // 验证密码长度
        if (strlen($password) < 6) {
            ajaxReturn(1, '密码长度不能少于6位');
        }
        
        // 检查用户名是否已存在
        $existUser = $db->getOne('users', "username = '$username'");
        if ($existUser) {
            ajaxReturn(1, '该手机号已被注册');
        }
        
        // 密码加密
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // 添加用户
        $userId = $db->insert('users', [
            'username' => $username,
            'password' => $hashedPassword,
            'irole' => $role,
            'status' => 1,
            'regtime' => date('Y-m-d H:i:s')
        ]);
        
        if (!$userId) {
            ajaxReturn(1, '添加失败，请稍后重试');
        }
        
        // 记录日志
        writeLog($admin['id'], 'add_user', "添加用户：$username");
        
        ajaxReturn(0, '添加成功', ['id' => $userId]);
        break;
    
    // 更新用户信息
    case 'updateUser':
        // 验证管理员权限
        $admin = checkAuth('updateUser', true);
        if (!$admin) {
            exit;
        }
        
        // 获取参数
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $role = isset($_POST['role']) ? intval($_POST['role']) : null;
        $status = isset($_POST['status']) ? intval($_POST['status']) : null;
        
        // 参数验证
        if (empty($id)) {
            ajaxReturn(1, '参数错误');
        }
        
        // 获取用户信息
        $targetUser = $db->getOne('users', "id = $id");
        if (!$targetUser) {
            ajaxReturn(1, '用户不存在');
        }
        
        // 不能禁用自己的账号
        if ($id == $admin['id'] && $status === 0) {
            ajaxReturn(1, '不能禁用当前登录的账号');
        }
        
        // 准备更新数据
        $updateData = [];
        
        if ($role !== null) {
            $updateData['irole'] = $role;
        }
        
        if ($status !== null) {
            $updateData['status'] = $status;
        }
        
        if (empty($updateData)) {
            ajaxReturn(1, '没有要更新的数据');
        }
        
        // 更新用户信息
        $result = $db->update('users', $updateData, "id = $id");
        if (!$result) {
            ajaxReturn(1, '更新失败，请稍后重试');
        }
        
        // 记录日志
        $logContent = "更新用户信息：" . $targetUser['username'];
        writeLog($admin['id'], 'update_user', $logContent);
        
        ajaxReturn(0, '更新成功');
        break;
    
    // 重置用户密码
    case 'resetPassword':
        // 验证管理员权限
        $admin = checkAuth('resetPassword', true);
        if (!$admin) {
            exit;
        }
        
        // 获取参数
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // 参数验证
        if (empty($id) || empty($password)) {
            ajaxReturn(1, '参数错误');
        }
        
        // 验证密码长度
        if (strlen($password) < 6) {
            ajaxReturn(1, '密码长度不能少于6位');
        }
        
        // 获取用户信息
        $targetUser = $db->getOne('users', "id = $id");
        if (!$targetUser) {
            ajaxReturn(1, '用户不存在');
        }
        
        // 密码加密
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // 更新密码
        $result = $db->update('users', [
            'password' => $hashedPassword
        ], "id = $id");
        
        if (!$result) {
            ajaxReturn(1, '重置失败，请稍后重试');
        }
        
        // 记录日志
        $logContent = "重置用户密码：" . $targetUser['username'];
        writeLog($admin['id'], 'reset_password', $logContent);
        
        ajaxReturn(0, '密码重置成功');
        break;
    
    // 获取投票统计数据
    case 'getVoteStats':
        // 验证管理员权限
        $admin = checkAuth('getVoteStats', true);
        if (!$admin) {
            exit;
        }
        
        // 获取参数
        $topicId = isset($_REQUEST['topic_id']) ? intval($_REQUEST['topic_id']) : 0;
        
        // 如果指定了特定投票
        if ($topicId) {
            // 获取投票信息
            $vote = $db->getOne('vote', "id = $topicId");
            if (!$vote) {
                ajaxReturn(1, '投票不存在');
            }
            
            // 获取选项列表
            $options = $db->getAll('xuan', "topic_id = $topicId", '*', 'sort ASC, id ASC');
            
            // 获取每个选项的投票数
            $voteCounts = [];
            $sql = "SELECT option_id, COUNT(*) as vote_count FROM " . $db->table('recs') . " WHERE topic_id = $topicId GROUP BY option_id";
            $result = $db->query($sql);
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $voteCounts[$row['option_id']] = $row['vote_count'];
                }
            }
            
            // 统计总票数
            $totalVotes = array_sum($voteCounts);
            
            // 格式化选项数据
            $formattedOptions = [];
            foreach ($options as $option) {
                $count = isset($voteCounts[$option['id']]) ? $voteCounts[$option['id']] : 0;
                $percentage = $totalVotes > 0 ? round(($count / $totalVotes) * 100, 1) : 0;
                
                $formattedOptions[] = [
                    'id' => $option['id'],
                    'name' => $option['name'],
                    'count' => $count,
                    'percentage' => $percentage
                ];
            }
            
            // 获取参与用户
            $participants = [];
            $sql = "SELECT DISTINCT u.username, r.vote_time, r.ip 
                    FROM " . $db->table('recs') . " r
                    LEFT JOIN " . $db->table('users') . " u ON r.user_id = u.id
                    WHERE r.topic_id = $topicId
                    ORDER BY r.vote_time DESC";
            $result = $db->query($sql);
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $participants[] = [
                        'username' => $row['username'],
                        'vote_time' => $row['vote_time'],
                        'ip' => $row['ip']
                    ];
                }
            }
            
            ajaxReturn(0, '获取成功', [
                'vote' => $vote,
                'options' => $formattedOptions,
                'totalVotes' => $totalVotes,
                'participants' => $participants
            ]);
        } else {
            // 获取所有投票的统计信息
            $voteStats = [];
            
            // 获取所有投票
            $votes = $db->getAll('vote', '', '*', 'addtime DESC');
            
            foreach ($votes as $vote) {
                // 获取该投票的总票数
                $totalVotes = $db->count('recs', "topic_id = {$vote['id']}");
                
                // 获取参与人数
                $sql = "SELECT COUNT(DISTINCT user_id) as user_count FROM " . $db->table('recs') . " WHERE topic_id = {$vote['id']}";
                $result = $db->query($sql);
                $userCount = 0;
                
                if ($result && $row = $result->fetch_assoc()) {
                    $userCount = $row['user_count'];
                }
                
                $voteStats[] = [
                    'id' => $vote['id'],
                    'title' => $vote['title'],
                    'start_time' => $vote['statime'],
                    'end_time' => $vote['endtime'],
                    'total_votes' => $totalVotes,
                    'user_count' => $userCount,
                    'view_count' => $vote['iview']
                ];
            }
            
            ajaxReturn(0, '获取成功', [
                'voteStats' => $voteStats
            ]);
        }
        break;
    
    // 获取系统日志
    case 'getLogs':
        // 验证管理员权限
        $admin = checkAuth('getLogs', true);
        if (!$admin) {
            exit;
        }
        
        // 获取分页参数
        $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $pageSize = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 20;
        
        // 获取筛选参数
        $userId = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
        $action = isset($_REQUEST['action']) ? safeFilter($_REQUEST['action']) : '';
        $startDate = isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : '';
        $endDate = isset($_REQUEST['end_date']) ? $_REQUEST['end_date'] : '';
        
        // 构建查询条件
        $whereConditions = [];
        
        if ($userId) {
            $whereConditions[] = "user_id = $userId";
        }
        
        if ($action) {
            $whereConditions[] = "action = '$action'";
        }
        
        if ($startDate) {
            $whereConditions[] = "logtime >= '$startDate 00:00:00'";
        }
        
        if ($endDate) {
            $whereConditions[] = "logtime <= '$endDate 23:59:59'";
        }
        
        $whereStr = !empty($whereConditions) ? implode(' AND ', $whereConditions) : '';
        
        // 获取总记录数
        $total = $db->count('logs', $whereStr);
        
        // 计算分页信息
        $pagination = getPagination($total, $page, $pageSize);
        
        // 获取日志列表
        $orderBy = "logtime DESC";
        $limit = "{$pagination['offset']}, {$pagination['pageSize']}";
        
        $sql = "SELECT l.*, u.username 
                FROM " . $db->table('logs') . " l
                LEFT JOIN " . $db->table('users') . " u ON l.user_id = u.id
                " . ($whereStr ? "WHERE $whereStr" : "") . "
                ORDER BY $orderBy
                LIMIT $limit";
        
        $result = $db->query($sql);
        $logs = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $logs[] = $row;
            }
        }
        
        // 获取所有操作类型，用于筛选
        $sql = "SELECT DISTINCT action FROM " . $db->table('logs');
        $result = $db->query($sql);
        $actionTypes = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $actionTypes[] = $row['action'];
            }
        }
        
        ajaxReturn(0, '获取成功', [
            'logs' => $logs,
            'pagination' => $pagination,
            'actionTypes' => $actionTypes
        ]);
        break;
    
    // 未知操作
    default:
        ajaxReturn(1, '未知操作');
        break;
}
