<?php

// 费心PHP+mysql极简通用多主题投票系统 V2025.05.08
// 演示地址1: http://demo.fxtp.cn
// 演示地址2: http://wevote.chalide.cn
// 文件路径: api/upload.php
// 文件大小: 5744 字节
// 最后修改时间: 2025-05-09 10:45:58
// 作者: yujianyue
// 邮件: 15058593138@qq.com Bug反馈或意见建议
// 版权所有,保留发行权和署名权
/**
 * 图片上传处理API
 * 处理图片上传，支持自动调整大于1280宽度的图片为1024宽度
 */

// 引入必要文件
require_once '../inc/pubs.php';
require_once '../inc/sqls.php';

// 验证用户登录状态
if (!checkLogin()) {
    exit(json_encode([
        'code' => 1001,
        'msg' => '请先登录'
    ]));
}

// 检查请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode([
        'code' => 1002,
        'msg' => '请求方法错误'
    ]));
}

// 检查是否有文件上传
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    exit(json_encode([
        'code' => 1003,
        'msg' => '上传失败: ' . uploadErrorMessage($_FILES['image']['error'])
    ]));
}

// 验证文件类型
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$fileType = $_FILES['image']['type'];

if (!in_array($fileType, $allowedTypes)) {
    exit(json_encode([
        'code' => 1004,
        'msg' => '只允许上传JPG、PNG、GIF和WEBP格式的图片'
    ]));
}

// 验证文件大小（最大10MB）
$maxFileSize = 10 * 1024 * 1024; // 10MB
if ($_FILES['image']['size'] > $maxFileSize) {
    exit(json_encode([
        'code' => 1005,
        'msg' => '文件大小超过限制，最大允许10MB'
    ]));
}

// 准备上传路径
$uploadDir = '../uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// 生成唯一文件名
$extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$fileName = uniqid('img_') . '_' . date('Ymd') . '.' . $extension;
$uploadPath = $uploadDir . $fileName;
$relativeUrl = 'uploads/' . $fileName;

// 处理图片上传
$uploaded = false;

// 获取图片信息
$imageInfo = getimagesize($_FILES['image']['tmp_name']);
if (!$imageInfo) {
    exit(json_encode([
        'code' => 1006,
        'msg' => '无效的图片文件'
    ]));
}

$width = $imageInfo[0];
$height = $imageInfo[1];

// 如果图片宽度大于1280，则调整为1024宽度（尽管前端已经处理，这里作为后端保障）
if ($width > 1280) {
    // 计算新高度，保持比例
    $newWidth = 1024;
    $newHeight = intval($height * ($newWidth / $width));
    
    // 根据图片类型创建图像
    switch ($imageInfo[2]) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($_FILES['image']['tmp_name']);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($_FILES['image']['tmp_name']);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($_FILES['image']['tmp_name']);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($_FILES['image']['tmp_name']);
            break;
        default:
            exit(json_encode([
                'code' => 1007,
                'msg' => '不支持的图片格式'
            ]));
    }
    
    // 创建新图像
    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    
    // 保持PNG和WebP的透明度
    if ($imageInfo[2] == IMAGETYPE_PNG || $imageInfo[2] == IMAGETYPE_WEBP) {
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
        imagefilledrectangle($thumb, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    // 调整图像大小
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // 保存图像
    switch ($imageInfo[2]) {
        case IMAGETYPE_JPEG:
            $uploaded = imagejpeg($thumb, $uploadPath, 90);
            break;
        case IMAGETYPE_PNG:
            $uploaded = imagepng($thumb, $uploadPath, 9);
            break;
        case IMAGETYPE_GIF:
            $uploaded = imagegif($thumb, $uploadPath);
            break;
        case IMAGETYPE_WEBP:
            $uploaded = imagewebp($thumb, $uploadPath, 90);
            break;
    }
    
    // 释放内存
    imagedestroy($source);
    imagedestroy($thumb);
} else {
    // 直接移动上传的文件
    $uploaded = move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);
}

// 检查上传是否成功
if (!$uploaded) {
    exit(json_encode([
        'code' => 1008,
        'msg' => '文件保存失败'
    ]));
}

// 记录日志
$user = $_SESSION['user'];
$ip = "".$_SERVER['REMOTE_ADDR'];
$db = new DB();
$db->insert('logs', [
    'user_id' => $user['id'],
    'action' => '上传图片',
    'idesc' => '上传了图片: ' . $fileName,
    'ip' => $ip,
    'logtime' => date('Y-m-d H:i:s')
]);

// 返回成功信息和图片URL
exit(json_encode([
    'code' => 0,
    'msg' => '上传成功',
    'data' => [
        'url' => $relativeUrl,
        'width' => $width,
        'height' => $height
    ]
]));

/**
 * 获取上传错误消息
 * @param int $errorCode 上传错误代码
 * @return string 错误消息
 */
function uploadErrorMessage($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
            return '上传的文件超过了php.ini中upload_max_filesize指令限制的大小';
        case UPLOAD_ERR_FORM_SIZE:
            return '上传的文件超过了HTML表单中MAX_FILE_SIZE指令指定的大小';
        case UPLOAD_ERR_PARTIAL:
            return '文件只有部分被上传';
        case UPLOAD_ERR_NO_FILE:
            return '没有文件被上传';
        case UPLOAD_ERR_NO_TMP_DIR:
            return '找不到临时文件夹';
        case UPLOAD_ERR_CANT_WRITE:
            return '文件写入失败';
        case UPLOAD_ERR_EXTENSION:
            return '文件上传被PHP扩展程序中断';
        default:
            return '未知上传错误';
    }
}
