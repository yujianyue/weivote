<?php

// 费心PHP+mysql极简通用多主题投票系统 V2025.05.08
// 演示地址1: http://demo.fxtp.cn
// 演示地址2: http://wevote.chalide.cn
// 文件路径: api/topic.php
// 文件大小: 13171 字节
// 最后修改时间: 2025-05-09 06:58:02
// 作者: yujianyue
// 邮件: 15058593138@qq.com Bug反馈或意见建议
// 版权所有,保留发行权和署名权
/**
 * 投票主题相关API
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
    // 获取投票主题列表
    case 'getList':
        // 验证管理员权限
        $user = checkAuth('getList', true);
        if (!$user) {
            exit;
        }
        
        // 获取分页参数
        $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $pageSize = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 10;
        
        // 获取筛选参数
        $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
        $keyword = isset($_REQUEST['keyword']) ? safeFilter($_REQUEST['keyword']) : '';
        
        // 构建查询条件
        $whereConditions = [];
        if ($status !== '') {
            $whereConditions[] = "status = " . intval($status);
        }
        if ($type !== '') {
            $whereConditions[] = "itype = " . intval($type);
        }
        if (!empty($keyword)) {
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
        
        // 格式化日期
        foreach ($voteList as &$vote) {
            $vote['statime_formatted'] = date('Y-m-d H:i', strtotime($vote['statime']));
            $vote['endtime_formatted'] = date('Y-m-d H:i', strtotime($vote['endtime']));
            $vote['addtime_formatted'] = date('Y-m-d H:i', strtotime($vote['addtime']));
        }
        
        ajaxReturn(0, '获取成功', [
            'list' => $voteList,
            'pagination' => $pagination
        ]);
        break;
    
    // 获取投票主题详情
    case 'getDetail':
        // 获取参数
        $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        
        // 参数验证
        if (empty($id)) {
            ajaxReturn(1, '参数错误');
        }
        
        // 获取投票详情
        $vote = $db->getOne('vote', "id = $id");
        if (!$vote) {
            ajaxReturn(1, '投票不存在');
        }
        
        // 获取选项列表
        $options = $db->getAll('xuan', "topic_id = $id", '*', 'sort ASC, id ASC');
        
        ajaxReturn(0, '获取成功', [
            'vote' => $vote,
            'options' => $options
        ]);
        break;
    
    // 添加投票主题
    case 'add':
        // 验证管理员权限
        $user = checkAuth('add', true);
        if (!$user) {
            exit;
        }
        
        // 获取参数
        $title = isset($_POST['title']) ? safeFilter($_POST['title']) : '';
        $desc = isset($_POST['desc']) ? safeFilter($_POST['desc']) : '';
        $statime = isset($_POST['statime']) ? $_POST['statime'] : '';
        $endtime = isset($_POST['endtime']) ? $_POST['endtime'] : '';
        $type = isset($_POST['type']) ? intval($_POST['type']) : 0;
        $maxtime = isset($_POST['maxtime']) ? intval($_POST['maxtime']) : 1;
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;
        
        // 参数验证
        if (empty($title)) {
            ajaxReturn(1, '请输入投票标题');
        }
        
        if (empty($statime) || empty($endtime)) {
            ajaxReturn(1, '请选择开始和结束时间');
        }
        
        if (strtotime($statime) >= strtotime($endtime)) {
            ajaxReturn(1, '结束时间必须晚于开始时间');
        }
        
        if ($type == 1 && $maxtime < 2) {
            ajaxReturn(1, '多选投票最少可选2项');
        }
        
        // 添加投票主题
        $topicId = $db->insert('vote', [
            'title' => $title,
            'idesc' => $desc,
            'statime' => $statime,
            'endtime' => $endtime,
            'itype' => $type,
            'maxtime' => $maxtime,
            'status' => $status,
            'addtime' => date('Y-m-d H:i:s'),
            'adduser' => $user['id'],
            'iview' => 0
        ]);
        
        if (!$topicId) {
            ajaxReturn(1, '添加失败，请稍后重试');
        }
        
        // 记录日志
        writeLog($user['id'], 'add_topic', "添加投票：$title");
        
        ajaxReturn(0, '添加成功', ['id' => $topicId]);
        break;
    
    // 更新投票主题
    case 'update':
        // 验证管理员权限
        $user = checkAuth('update', true);
        if (!$user) {
            exit;
        }
        
        // 获取参数
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $title = isset($_POST['title']) ? safeFilter($_POST['title']) : '';
        $desc = isset($_POST['desc']) ? safeFilter($_POST['desc']) : '';
        $statime = isset($_POST['statime']) ? $_POST['statime'] : '';
        $endtime = isset($_POST['endtime']) ? $_POST['endtime'] : '';
        $type = isset($_POST['type']) ? intval($_POST['type']) : 0;
        $maxtime = isset($_POST['maxtime']) ? intval($_POST['maxtime']) : 1;
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;
        
        // 参数验证
        if (empty($id)) {
            ajaxReturn(1, '参数错误');
        }
        
        if (empty($title)) {
            ajaxReturn(1, '请输入投票标题');
        }
        
        if (empty($statime) || empty($endtime)) {
            ajaxReturn(1, '请选择开始和结束时间');
        }
        
        if (strtotime($statime) >= strtotime($endtime)) {
            ajaxReturn(1, '结束时间必须晚于开始时间');
        }
        
        if ($type == 1 && $maxtime < 2) {
            ajaxReturn(1, '多选投票最少可选2项');
        }
        
        // 检查投票是否存在
        $vote = $db->getOne('vote', "id = $id");
        if (!$vote) {
            ajaxReturn(1, '投票不存在');
        }
        
        // 更新投票主题
        $result = $db->update('vote', [
            'title' => $title,
            'idesc' => $desc,
            'statime' => $statime,
            'endtime' => $endtime,
            'itype' => $type,
            'maxtime' => $maxtime,
            'status' => $status
        ], "id = $id");
        
        if (!$result) {
            ajaxReturn(1, '更新失败，请稍后重试');
        }
        
        // 记录日志
        writeLog($user['id'], 'update_topic', "更新投票：$title");
        
        ajaxReturn(0, '更新成功');
        break;
    
    // 删除投票主题
    case 'delete':
        // 验证管理员权限
        $user = checkAuth('delete', true);
        if (!$user) {
            exit;
        }
        
        // 获取参数
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        // 参数验证
        if (empty($id)) {
            ajaxReturn(1, '参数错误');
        }
        
        // 检查投票是否存在
        $vote = $db->getOne('vote', "id = $id");
        if (!$vote) {
            ajaxReturn(1, '投票不存在');
        }
        
        // 开始事务
        $db->startTransaction();
        
        try {
            // 删除相关记录
            $db->delete('recs', "topic_id = $id");
            $db->delete('xuan', "topic_id = $id");
            $db->delete('vote', "id = $id");
            
            // 记录日志
            writeLog($user['id'], 'delete_topic', "删除投票：{$vote['title']}");
            
            // 提交事务
            $db->commit();
            
            ajaxReturn(0, '删除成功');
        } catch (Exception $e) {
            // 回滚事务
            $db->rollback();
            ajaxReturn(1, '删除失败：' . $e->getMessage());
        }
        break;
    
    // 获取投票选项
    case 'getOptions':
        // 获取参数
        $topicId = isset($_REQUEST['topic_id']) ? intval($_REQUEST['topic_id']) : 0;
        
        // 参数验证
        if (empty($topicId)) {
            ajaxReturn(1, '参数错误');
        }
        
        // 获取选项列表
        $options = $db->getAll('xuan', "topic_id = $topicId", '*', 'sort ASC, id ASC');
        
        ajaxReturn(0, '获取成功', $options);
        break;
    
    // 添加投票选项
    case 'addOption':
        // 验证管理员权限
        $user = checkAuth('addOption', true);
        if (!$user) {
            exit;
        }
        
        // 获取参数
        $topicId = isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0;
        $name = isset($_POST['name']) ? safeFilter($_POST['name']) : '';
        $imgs = isset($_POST['imgs']) ? safeFilter($_POST['imgs']) : '';
        $desc = isset($_POST['desc']) ? safeFilter($_POST['desc']) : '';
        $sort = isset($_POST['sort']) ? intval($_POST['sort']) : 0;
        
        // 参数验证
        if (empty($topicId) || empty($name)) {
            ajaxReturn(1, '请填写必要参数');
        }
        
        // 检查投票是否存在
        $vote = $db->getOne('vote', "id = $topicId");
        if (!$vote) {
            ajaxReturn(1, '投票不存在');
        }
        
        // 添加选项
        $optionId = $db->insert('xuan', [
            'topic_id' => $topicId,
            'name' => $name,
            'imgs' => $imgs,
            'idesc' => $desc,
            'sort' => $sort,
            'addtime' => date('Y-m-d H:i:s'),
            'adduser' => $user['id']
        ]);
        
        if (!$optionId) {
            ajaxReturn(1, '添加失败，请稍后重试');
        }
        
        // 记录日志
        writeLog($user['id'], 'add_option', "添加投票选项：$name");
        
        ajaxReturn(0, '添加成功', ['id' => $optionId]);
        break;
    
    // 更新投票选项
    case 'updateOption':
        // 验证管理员权限
        $user = checkAuth('updateOption', true);
        if (!$user) {
            exit;
        }
        
        // 获取参数
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $name = isset($_POST['name']) ? safeFilter($_POST['name']) : '';
        $imgs = isset($_POST['imgs']) ? safeFilter($_POST['imgs']) : '';
        $desc = isset($_POST['desc']) ? safeFilter($_POST['desc']) : '';
        $sort = isset($_POST['sort']) ? intval($_POST['sort']) : 0;
        
        // 参数验证
        if (empty($id) || empty($name)) {
            ajaxReturn(1, '请填写必要参数');
        }
        
        // 检查选项是否存在
        $option = $db->getOne('xuan', "id = $id");
        if (!$option) {
            ajaxReturn(1, '选项不存在');
        }
        
        // 更新选项
        $result = $db->update('xuan', [
            'name' => $name,
            'imgs' => $imgs,
            'idesc' => $desc,
            'sort' => $sort
        ], "id = $id");
        
        if (!$result) {
            ajaxReturn(1, '更新失败，请稍后重试');
        }
        
        // 记录日志
        writeLog($user['id'], 'update_option', "更新投票选项：$name");
        
        ajaxReturn(0, '更新成功');
        break;
    
    // 删除投票选项
    case 'deleteOption':
        // 验证管理员权限
        $user = checkAuth('deleteOption', true);
        if (!$user) {
            exit;
        }
        
        // 获取参数
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        // 参数验证
        if (empty($id)) {
            ajaxReturn(1, '参数错误');
        }
        
        // 检查选项是否存在
        $option = $db->getOne('xuan', "id = $id");
        if (!$option) {
            ajaxReturn(1, '选项不存在');
        }
        
        // 开始事务
        $db->startTransaction();
        
        try {
            // 删除投票记录
            $db->delete('recs', "option_id = $id");
            
            // 删除选项
            $db->delete('xuan', "id = $id");
            
            // 记录日志
            writeLog($user['id'], 'delete_option', "删除投票选项：{$option['name']}");
            
            // 提交事务
            $db->commit();
            
            ajaxReturn(0, '删除成功');
        } catch (Exception $e) {
            // 回滚事务
            $db->rollback();
            ajaxReturn(1, '删除失败：' . $e->getMessage());
        }
        break;
    
    // 未知操作
    default:
        ajaxReturn(1, '未知操作');
        break;
}
