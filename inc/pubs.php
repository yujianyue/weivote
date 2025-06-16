<?php

// 费心PHP+mysql极简通用多主题投票系统 V2025.05.08
// 演示地址1: http://demo.fxtp.cn
// 演示地址2: http://wevote.chalide.cn
// 文件路径: inc/pubs.php
// 文件大小: 3521 字节
// 最后修改时间: 2025-05-09 06:50:26
// 作者: yujianyue
// 邮件: 15058593138@qq.com Bug反馈或意见建议
// 版权所有,保留发行权和署名权
/**
 * 公共函数文件
 */

// 引入数据库连接
require_once 'conn.php';

/**
 * 输出JSON格式响应
 * @param int $code 状态码，0表示成功，非0表示失败
 * @param string $msg 提示信息
 * @param array $data 返回数据
 */
function ajaxReturn($code = 0, $msg = '', $data = []) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => $code,
        'msg' => $msg,
        'data' => $data
    ]);
    exit;
}

/**
 * 检查用户登录状态
 * @return array|bool 已登录返回用户信息数组，未登录返回false
 */
function checkLogin() {
    session_start();
    if (isset($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
        return $_SESSION['user'];
    }
    return false;
}

/**
 * 检查管理员权限
 * @return bool 是否具有管理员权限
 */
function checkAdmin() {
    $user = checkLogin();
    if ($user && $user['irole'] == 1) {
        return true;
    }
    return false;
}

/**
 * 输入安全过滤
 * @param mixed $data 需要过滤的数据
 * @return mixed 过滤后的数据
 */
function safeFilter($data) {
    if (is_array($data)) {
        foreach ($data as $key => $val) {
            $data[$key] = safeFilter($val);
        }
    } else {
        // 去除空格
        $data = trim($data);
        // 转义特殊字符
        $data = htmlspecialchars($data, ENT_QUOTES);
        // 防止SQL注入
        $data = addslashes($data);
    }
    return $data;
}

/**
 * 系统日志记录
 * @param int $userId 用户ID
 * @param string $action 操作类型
 * @param string $content 操作内容
 */
function writeLog($userId, $action, $content = '') {
    $conn = dbConnect();
    $table = getTable('logs');
    $userId = (int)$userId;
    $action = $conn->real_escape_string($action);
    $content = $conn->real_escape_string($content);
    $ip = $_SERVER['REMOTE_ADDR'];
    $time = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO {$table} (user_id, action, idesc, ip, logtime) VALUES ($userId, '$action', '$content', '$ip', '$time')";
    $conn->query($sql);
    $conn->close();
}

/**
 * 权限检查函数
 * @param string $action 操作名称
 * @param bool $adminRequired 是否需要管理员权限
 * @return bool|array 成功返回用户信息，失败返回false
 */
function checkAuth($action, $adminRequired = false) {
    $user = checkLogin();
    
    // 未登录
    if (!$user) {
        ajaxReturn(403, '请先登录后再操作');
        return false;
    }
    
    // 需要管理员权限但不是管理员
    if ($adminRequired && $user['irole'] != 1) {
        ajaxReturn(403, '权限不足，需要管理员权限');
        return false;
    }
    
    return $user;
}

/**
 * 获取分页参数
 * @param int $total 总记录数
 * @param int $page 当前页码
 * @param int $pageSize 每页记录数
 * @return array 分页信息
 */
function getPagination($total, $page = 1, $pageSize = 10) {
    $page = max(1, intval($page));
    $pageSize = max(1, intval($pageSize));
    
    $totalPage = ceil($total / $pageSize);
    $totalPage = max(1, $totalPage);
    $page = min($page, $totalPage);
    
    $offset = ($page - 1) * $pageSize;
    
    return [
        'total' => $total,
        'page' => $page,
        'pageSize' => $pageSize,
        'totalPage' => $totalPage,
        'offset' => $offset
    ];
}

/**
 * 获取当前时间
 * @return string 格式化的时间字符串
 */
function getCurrentTime() {
    return date('Y-m-d H:i:s');
}
