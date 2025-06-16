<?php

// 费心PHP+mysql极简通用多主题投票系统 V2025.05.08
// 演示地址1: http://demo.fxtp.cn
// 演示地址2: http://wevote.chalide.cn
// 文件路径: admin/topic_options.php
// 文件大小: 23769 字节
// 最后修改时间: 2025-05-09 10:46:46
// 作者: yujianyue
// 邮件: 15058593138@qq.com Bug反馈或意见建议
// 版权所有,保留发行权和署名权
/**
 * 投票选项管理页面
 * 该文件由topic.php包含使用
 */

// 检查是否已经引入必要文件
if (!isset($db) || !isset($user) || !isset($vote) || !isset($options)) {
    header('Location: topic.php');
    exit;
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
        
        .options-list {
            margin-bottom: 30px;
        }
        
        .option-item {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin-bottom: 15px;
            position: relative;
        }
        
        .option-actions {
            position: absolute;
            top: 15px;
            right: 15px;
        }
        
        .option-image {
            max-width: 100px;
            max-height: 100px;
            margin-top: 10px;
            border-radius: 3px;
        }
        
        .operation-btns {
            display: flex;
            gap: 5px;
        }
        
        .vote-info {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #3498db;
        }
      
            .progress-bar {
            height: 20px;
            background-color: #f0f0f0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 5px;
        }
        
        .progress-fill {
            height: 100%;
            background-color: #4CAF50;
            transition: width 0.3s ease;
        }
        
        .progress-text {
            font-size: 14px;
            color: #555;
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
                <h2>管理投票选项</h2>
                <div>
                    <a href="topic.php" class="btn btn-gray">返回列表</a>
                    <a href="topic.php?act=edit&id=<?php echo $vote['id']; ?>" class="btn btn-blue">编辑投票</a>
                    <a href="../vote.php?id=<?php echo $vote['id']; ?>" class="btn btn-green" target="_blank">查看投票</a>
                </div>
            </div>
            
            <!-- 投票信息 -->
            <div class="vote-info">
                <h3><?php echo htmlspecialchars($vote['title']); ?></h3>
                <p><strong>类型：</strong><?php echo $vote['itype'] == 0 ? '单选' : "多选（最多选{$vote['maxtime']}项）"; ?></p>
                <p><strong>时间：</strong><?php echo date('Y-m-d H:i', strtotime($vote['statime'])); ?> 至 <?php echo date('Y-m-d H:i', strtotime($vote['endtime'])); ?></p>
                <p><strong>状态：</strong><?php echo $vote['status'] == 1 ? '正常' : '禁用'; ?></p>
            </div>
            
            <!-- 选项列表 -->
            <div class="options-list">
                <h3>现有选项（<?php echo count($options); ?>）</h3>
                
                <?php if (!empty($options)): ?>
                    <?php foreach ($options as $option): ?>
                        <div class="option-item">
                            <div class="option-actions">
                                <button class="btn btn-blue btn-sm" onclick="editOption(<?php echo $option['id']; ?>)">编辑</button>
                                <button class="btn btn-red btn-sm" onclick="deleteOption(<?php echo $option['id']; ?>, '<?php echo htmlspecialchars(addslashes($option['name'])); ?>')">删除</button>
                            </div>
                            
                            <h4><?php echo htmlspecialchars($option['name']); ?></h4>
                            
                            <?php if (!empty($option['imgs'])): ?>
                                <div><img src="<?php echo htmlspecialchars($option['imgs']); ?>" alt="<?php echo htmlspecialchars($option['name']); ?>" class="option-image"></div>
                            <?php endif; ?>
                            
                            <?php if (!empty($option['idesc'])): ?>
                                <p><?php echo nl2br(htmlspecialchars($option['idesc'])); ?></p>
                            <?php endif; ?>
                            
                            <p><small>排序：<?php echo $option['sort']; ?></small></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body" style="text-align: center; padding: 30px;">
                            <p>暂无选项数据</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- 添加选项表单 -->
            <div class="form-card">
                <h3 id="formTitle">添加新选项</h3>
                <form id="optionForm">
                    <input type="hidden" id="optionId" name="id" value="0">
                    <input type="hidden" name="topic_id" value="<?php echo $vote['id']; ?>">
                    
                    <div class="form-group">
                        <label for="name" class="form-label">选项名称</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="imgs" class="form-label">选项图片URL（可选）</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="imgs" name="imgs" class="form-control" placeholder="选项图片相对路径">
                            <button type="button" id="uploadImgBtn" class="btn btn-green">传图</button>
                        </div>
                        <input type="file" id="imgUploader" style="display: none;" accept="image/*">
                        <div id="uploadProgress" style="display: none; margin-top: 10px;">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 0%;"></div>
                            </div>
                            <div class="progress-text">上传中 0%</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="desc" class="form-label">选项描述（可选）</label>
                        <textarea id="desc" name="desc" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="sort" class="form-label">排序（数字越小越靠前）</label>
                        <input type="number" id="sort" name="sort" class="form-control" value="0">
                    </div>
                    
                    <div style="margin-top: 20px; text-align: center;">
                        <button type="submit" class="btn btn-blue btn-lg" id="submitBtn">添加选项</button>
                        <button type="button" class="btn btn-gray btn-lg" id="resetBtn" style="display: none;">取消编辑</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../inc/js.js"></script>
    <script>
      
        // 图片上传相关
        document.getElementById('uploadImgBtn').addEventListener('click', function() {
            document.getElementById('imgUploader').click();
        });
        
        document.getElementById('imgUploader').addEventListener('change', function(e) {
            if (this.files.length === 0) return;
            
            var file = this.files[0];
            if (!file.type.match('image.*')) {
                showMask('错误', '请选择图片文件', [{ text: '确定', class: 'btn-blue' }]);
                return;
            }
            
            // 显示上传进度条
            var progressBar = document.getElementById('uploadProgress');
            var progressFill = progressBar.querySelector('.progress-fill');
            var progressText = progressBar.querySelector('.progress-text');
            progressBar.style.display = 'block';
            progressFill.style.width = '0%';
            progressText.textContent = '上传中 0%';
            
            // 如果图片较大，先在客户端进行预处理
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = new Image();
                img.onload = function() {
                    // 检查图片尺寸，如果宽度大于1280，则调整为1024宽度
                    var canvas = document.createElement('canvas');
                    var ctx = canvas.getContext('2d');
                    var width = img.width;
                    var height = img.height;
                    
                    if (width > 1280) {
                        height = Math.round(height * (1024 / width));
                        width = 1024;
                    }
                    
                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    // 转换为Blob格式
                    canvas.toBlob(function(blob) {
                        // 创建FormData对象上传文件
                        var formData = new FormData();
                        formData.append('image', blob, file.name);
                        
                        // 创建XMLHttpRequest对象
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', '../api/upload.php', true);
                        
                        // 上传进度
                        xhr.upload.onprogress = function(e) {
                            if (e.lengthComputable) {
                                var percentage = Math.round((e.loaded / e.total) * 100);
                                progressFill.style.width = percentage + '%';
                                progressText.textContent = '上传中 ' + percentage + '%';
                            }
                        };
                        
                        // 上传完成
                        xhr.onload = function() {
                            progressBar.style.display = 'none';
                            
                            if (xhr.status === 200) {
                                try {
                                    var response = JSON.parse(xhr.responseText);
                                    if (response.code === 0) {
                                        // 上传成功，将图片URL填入输入框
                                        document.getElementById('imgs').value = response.data.url;
                                        showMask('成功', '图片上传成功', [{ text: '确定', class: 'btn-blue' }]);
                                    } else {
                                        showMask('错误', '上传失败: ' + response.msg, [{ text: '确定', class: 'btn-blue' }]);
                                    }
                                } catch (e) {
                                    showMask('错误', '服务器返回异常', [{ text: '确定', class: 'btn-blue' }]);
                                }
                            } else {
                                showMask('错误', '上传失败，服务器返回状态码: ' + xhr.status, [{ text: '确定', class: 'btn-blue' }]);
                            }
                        };
                        
                        // 上传错误
                        xhr.onerror = function() {
                            progressBar.style.display = 'none';
                            showMask('错误', '网络错误，上传失败', [{ text: '确定', class: 'btn-blue' }]);
                        };
                        
                        // 发送请求
                        xhr.send(formData);
                    }, 'image/jpeg', 0.92);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
        // 表单提交
        document.getElementById('optionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // 获取表单数据
            var form = this;
            var optionId = form.elements['id'].value;
            var topicId = form.elements['topic_id'].value;
            var name = form.elements['name'].value.trim();
            var imgs = form.elements['imgs'].value.trim();
            var desc = form.elements['desc'].value.trim();
            var sort = form.elements['sort'].value;
            
            // 验证表单
            if (!name) {
                showMask('提示', '请输入选项名称', [
                    {
                        text: '确定',
                        class: 'btn-primary',
                        callback: function() {
                            closeMask();
                            form.elements['name'].focus();
                        }
                    }
                ]);
                return;
            }
            
            // 准备提交数据
            var postData = {
                topic_id: topicId,
                name: name,
                imgs: imgs,
                desc: desc,
                sort: sort
            };
            
            // 根据是新增还是编辑，设置不同的请求参数
            if (optionId > 0) {
                postData.act = 'updateOption';
                postData.id = optionId;
            } else {
                postData.act = 'addOption';
            }
            
            // 发送请求
            ajaxRequest('../api/topic.php', postData, function(response) {
                if (response.code === 0) {
                    showMask('成功', optionId > 0 ? '选项更新成功' : '选项添加成功', [
                        {
                            text: '确定',
                            class: 'btn-primary',
                            callback: function() {
                                window.location.reload();
                            }
                        }
                    ]);
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
        
        // 重置表单按钮点击事件
        document.getElementById('resetBtn').addEventListener('click', function() {
            resetForm();
        });
        
        // 重置表单
        function resetForm() {
            var form = document.getElementById('optionForm');
            form.reset();
            form.elements['id'].value = 0;
            
            document.getElementById('formTitle').textContent = '添加新选项';
            document.getElementById('submitBtn').textContent = '添加选项';
            document.getElementById('resetBtn').style.display = 'none';
        }
        
        // 编辑选项
        function editOption(id) {
            // 发送请求获取选项详情
            ajaxRequest('../api/topic.php', {
                act: 'getOptions',
                topic_id: <?php echo $vote['id']; ?>
            }, function(response) {
                if (response.code === 0) {
                    var options = response.data;
                    var option = options.find(function(item) {
                        return item.id == id;
                    });
                    
                    if (option) {
                        // 填充表单
                        var form = document.getElementById('optionForm');
                        form.elements['id'].value = option.id;
                        form.elements['name'].value = option.name;
                        form.elements['imgs'].value = option.imgs;
                        form.elements['desc'].value = option.idesc;
                        form.elements['sort'].value = option.sort;
                        
                        // 更新UI
                        document.getElementById('formTitle').textContent = '编辑选项';
                        document.getElementById('submitBtn').textContent = '更新选项';
                        document.getElementById('resetBtn').style.display = 'inline-block';
                        
                        // 滚动到表单位置
                        document.querySelector('.form-card').scrollIntoView({ behavior: 'smooth' });
                    }
                } else {
                    showMask('错误', response.msg || '获取选项信息失败', [
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
        }
        
        // 删除选项
        function deleteOption(id, name) {
            showMask('确认删除', '确定要删除选项 "' + name + '" 吗？<br><span style="color: #e74c3c;">注意：删除后该选项相关的投票记录也将被删除，且不可恢复！</span>', [
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
                            act: 'deleteOption',
                            id: id
                        }, function(response) {
                            if (response.code === 0) {
                                // 删除成功，刷新页面
                                showMask('操作成功', '选项删除成功', [
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
