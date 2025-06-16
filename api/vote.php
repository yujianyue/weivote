<?php

// 费心PHP+mysql极简通用多主题投票系统 V2025.05.08
// 演示地址1: http://demo.fxtp.cn
// 演示地址2: http://wevote.chalide.cn
// 文件路径: api/vote.php
// 文件大小: 6634 字节
// 最后修改时间: 2025-05-09 06:57:06
// 作者: yujianyue
// 邮件: 15058593138@qq.com Bug反馈或意见建议
// 版权所有,保留发行权和署名权
/**
 * 投票操作API
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
    // 提交投票
    case 'submit':
        // 验证权限
        $user = checkAuth('submit');
        if (!$user) {
            exit;
        }
        
        // 获取参数
        $topicId = isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0;
        $optionsStr = isset($_POST['options']) ? $_POST['options'] : '';
        
        // 参数验证
        if (empty($topicId) || empty($optionsStr)) {
            ajaxReturn(1, '参数错误');
        }
        
        // 转换选项为数组
        $options = explode(',', $optionsStr);
        if (empty($options)) {
            ajaxReturn(1, '请至少选择一个选项');
        }
        
        // 获取投票信息
        $vote = $db->getOne('vote', "id = $topicId");
        if (!$vote) {
            ajaxReturn(1, '投票不存在');
        }
        
        // 验证投票状态
        $now = date('Y-m-d H:i:s');
        if ($vote['status'] != 1) {
            ajaxReturn(1, '该投票已被禁用');
        }
        
        if ($vote['statime'] > $now) {
            ajaxReturn(1, '该投票尚未开始');
        }
        
        if ($vote['endtime'] < $now) {
            ajaxReturn(1, '该投票已经结束');
        }
        
        // 检查用户是否已投票
        $hasVoted = $db->count('recs', "topic_id = $topicId AND user_id = {$user['id']}");
        if ($hasVoted > 0) {
            ajaxReturn(1, '您已经参与过该投票');
        }
        
        // 验证选项数量
        if ($vote['itype'] == 1 && count($options) > $vote['maxtime']) {
            ajaxReturn(1, "最多只能选择 {$vote['maxtime']} 项");
        }
        
        // 验证选项是否存在
        foreach ($options as $optionId) {
            $option = $db->getOne('xuan', "id = $optionId AND topic_id = $topicId");
            if (!$option) {
                ajaxReturn(1, '选项不存在');
            }
        }
        
        // 开始事务
        $db->startTransaction();
        
        try {
            // 添加投票记录
            $ip = $_SERVER['REMOTE_ADDR'];
            $time = date('Y-m-d H:i:s');
            
            foreach ($options as $optionId) {
                $result = $db->insert('recs', [
                    'topic_id' => $topicId,
                    'user_id' => $user['id'],
                    'option_id' => $optionId,
                    'vote_time' => $time,
                    'ip' => $ip
                ]);
                
                if (!$result) {
                    throw new Exception('提交投票失败');
                }
            }
            
            // 记录日志
            writeLog($user['id'], 'vote', "参与投票：{$vote['title']}");
            
            // 提交事务
            $db->commit();
            
            ajaxReturn(0, '投票成功');
        } catch (Exception $e) {
            // 回滚事务
            $db->rollback();
            ajaxReturn(1, $e->getMessage());
        }
        break;
    
    // 检查是否已投票
    case 'checkVoted':
        // 验证权限
        $user = checkAuth('checkVoted');
        if (!$user) {
            exit;
        }
        
        // 获取参数
        $topicId = isset($_REQUEST['topic_id']) ? intval($_REQUEST['topic_id']) : 0;
        
        // 参数验证
        if (empty($topicId)) {
            ajaxReturn(1, '参数错误');
        }
        
        // 检查用户是否已投票
        $hasVoted = $db->count('recs', "topic_id = $topicId AND user_id = {$user['id']}");
        
        ajaxReturn(0, '查询成功', ['hasVoted' => $hasVoted > 0]);
        break;
    
    // 获取投票结果
    case 'getResult':
        // 获取参数
        $topicId = isset($_REQUEST['topic_id']) ? intval($_REQUEST['topic_id']) : 0;
        
        // 参数验证
        if (empty($topicId)) {
            ajaxReturn(1, '参数错误');
        }
        
        // 获取投票信息
        $vote = $db->getOne('vote', "id = $topicId");
        if (!$vote) {
            ajaxReturn(1, '投票不存在');
        }
        
        // 获取选项列表
        $options = $db->getAll('xuan', "topic_id = $topicId", '*', 'sort ASC, id ASC');
        if (empty($options)) {
            ajaxReturn(1, '暂无投票选项');
        }
        
        // 获取投票结果
        $sql = "SELECT option_id, COUNT(*) as vote_count FROM " . $db->table('recs') . " WHERE topic_id = $topicId GROUP BY option_id";
        $result = $db->query($sql);
        
        $voteResults = [];
        $totalVotes = 0;
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $voteResults[$row['option_id']] = $row['vote_count'];
                $totalVotes += $row['vote_count'];
            }
        }
        
        // 格式化结果
        $formattedResults = [];
        foreach ($options as $option) {
            $voteCount = isset($voteResults[$option['id']]) ? $voteResults[$option['id']] : 0;
            $percentage = $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 1) : 0;
            
            $formattedResults[] = [
                'id' => $option['id'],
                'name' => $option['name'],
                'count' => $voteCount,
                'percentage' => $percentage
            ];
        }
        
        // 用户是否已投票
        $hasVoted = false;
        $userVotes = [];
        
        if (isset($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
            $userId = $_SESSION['user']['id'];
            $userVoteRecords = $db->getAll('recs', "topic_id = $topicId AND user_id = $userId");
            
            if (!empty($userVoteRecords)) {
                $hasVoted = true;
                foreach ($userVoteRecords as $record) {
                    $userVotes[] = $record['option_id'];
                }
            }
        }
        
        ajaxReturn(0, '获取成功', [
            'vote' => $vote,
            'options' => $options,
            'results' => $formattedResults,
            'totalVotes' => $totalVotes,
            'hasVoted' => $hasVoted,
            'userVotes' => $userVotes
        ]);
        break;
    
    // 未知操作
    default:
        ajaxReturn(1, '未知操作');
        break;
}
