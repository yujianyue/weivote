/**
 * 公共JavaScript函数
 */

/**
 * Ajax通信函数
 * @param {string} url - 请求地址
 * @param {Object} data - 请求数据
 * @param {Function} callback - 成功回调函数
 * @param {Function} errorCallback - 错误回调函数
 * @param {string} method - 请求方法（GET或POST）
 */
function ajaxRequest(url, data, callback, errorCallback, method) {
    // 默认使用POST方法，除非act为get
    if (!method) {
        method = (data && data.act === 'get') ? 'GET' : 'POST';
    }
    
    // 创建XMLHttpRequest对象
    var xhr = new XMLHttpRequest();
    
    // 准备发送请求
    xhr.open(method, url, true);
    
    // 设置请求头
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    if (method === 'POST') {
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    }
    
    // 处理响应
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                var response;
                try {
                    response = JSON.parse(xhr.responseText);
                    if (callback && typeof callback === 'function') {
                        callback(response);
                    }
                } catch (e) {
                    console.error('解析JSON失败:', e);
                    if (errorCallback && typeof errorCallback === 'function') {
                        errorCallback('解析响应失败');
                    }
                }
            } else {
                console.error('请求失败，状态码:', xhr.status);
                if (errorCallback && typeof errorCallback === 'function') {
                    errorCallback('请求失败，状态码: ' + xhr.status);
                }
            }
        }
    };
    
    // 处理请求超时
    xhr.ontimeout = function() {
        console.error('请求超时');
        if (errorCallback && typeof errorCallback === 'function') {
            errorCallback('请求超时');
        }
    };
    
    // 处理网络错误
    xhr.onerror = function() {
        console.error('网络错误');
        if (errorCallback && typeof errorCallback === 'function') {
            errorCallback('网络错误');
        }
    };
    
    // 发送请求
    if (method === 'POST' && data) {
        // 将对象转换为查询字符串
        var params = Object.keys(data).map(function(key) {
            return encodeURIComponent(key) + '=' + encodeURIComponent(data[key]);
        }).join('&');
        xhr.send(params);
    } else if (method === 'GET' && data) {
        // 将查询参数添加到URL
        var queryString = Object.keys(data).map(function(key) {
            return encodeURIComponent(key) + '=' + encodeURIComponent(data[key]);
        }).join('&');
        
        var separator = url.indexOf('?') !== -1 ? '&' : '?';
        xhr.send();
    } else {
        xhr.send();
    }
}

/**
 * 分页显示函数
 * @param {string} containerId - 分页容器ID
 * @param {number} currentPage - 当前页码
 * @param {number} totalPages - 总页数
 * @param {Function} callback - 页码点击回调函数
 */
function pagination(containerId, currentPage, totalPages, callback) {
    var container = document.getElementById(containerId);
    if (!container) return;
    
    // 清空容器
    container.innerHTML = '';
    
    // 如果没有数据或只有一页，不显示分页
    if (totalPages <= 1) {
        container.classList.add('disabled');
        return;
    }
    
    container.classList.remove('disabled');
    
    // 创建分页元素
    var paginationHtml = '';
    
    // 首页按钮
    var firstDisabled = currentPage === 1 ? ' disabled' : '';
    paginationHtml += '<a href="javascript:;" class="page-item first' + firstDisabled + '" data-page="1">首页</a>';
    
    // 上一页按钮
    var prevDisabled = currentPage === 1 ? ' disabled' : '';
    var prevPage = Math.max(1, currentPage - 1);
    paginationHtml += '<a href="javascript:;" class="page-item prev' + prevDisabled + '" data-page="' + prevPage + '">上一页</a>';
    
    // 页码下拉选择
    paginationHtml += '<select class="page-select">';
    for (var i = 1; i <= totalPages; i++) {
        var selected = i === currentPage ? ' selected' : '';
        paginationHtml += '<option value="' + i + '"' + selected + '>第 ' + i + ' 页</option>';
    }
    paginationHtml += '</select>';
    
    // 下一页按钮
    var nextDisabled = currentPage === totalPages ? ' disabled' : '';
    var nextPage = Math.min(totalPages, currentPage + 1);
    paginationHtml += '<a href="javascript:;" class="page-item next' + nextDisabled + '" data-page="' + nextPage + '">下一页</a>';
    
    // 尾页按钮
    var lastDisabled = currentPage === totalPages ? ' disabled' : '';
    paginationHtml += '<a href="javascript:;" class="page-item last' + lastDisabled + '" data-page="' + totalPages + '">尾页</a>';
    
    // 设置HTML
    container.innerHTML = paginationHtml;
    
    // 绑定事件
    // 点击页码按钮
    var pageItems = container.querySelectorAll('.page-item');
    for (var j = 0; j < pageItems.length; j++) {
        var item = pageItems[j];
        if (!item.classList.contains('disabled')) {
            item.addEventListener('click', function() {
                var page = parseInt(this.getAttribute('data-page'));
                if (callback && typeof callback === 'function') {
                    callback(page);
                }
            });
        }
    }
    
    // 下拉选择页码
    var pageSelect = container.querySelector('.page-select');
    if (pageSelect) {
        pageSelect.addEventListener('change', function() {
            var page = parseInt(this.value);
            if (callback && typeof callback === 'function') {
                callback(page);
            }
        });
    }
}

/**
 * 表格排序函数
 * @param {string} tableId - 表格ID
 * @param {number} colIndex - 排序列索引
 * @param {string} type - 排序类型（string, number, date）
 */
function sortTable(tableId, colIndex, type) {
    var table = document.getElementById(tableId);
    if (!table) return;
    
    var tbody = table.getElementsByTagName('tbody')[0];
    var rows = tbody.getElementsByTagName('tr');
    var sortedRows = Array.prototype.slice.call(rows, 0);
    
    // 确定排序方向
    var sortDirection = 1; // 1为升序，-1为降序
    if (table.getAttribute('data-sort-col') == colIndex) {
        sortDirection = table.getAttribute('data-sort-dir') == 'asc' ? -1 : 1;
    }
    
    // 保存排序信息
    table.setAttribute('data-sort-col', colIndex);
    table.setAttribute('data-sort-dir', sortDirection == 1 ? 'asc' : 'desc');
    
    // 排序
    sortedRows.sort(function(a, b) {
        var cellA = a.cells[colIndex].textContent.trim();
        var cellB = b.cells[colIndex].textContent.trim();
        
        if (type === 'number') {
            return sortDirection * (parseFloat(cellA) - parseFloat(cellB));
        } else if (type === 'date') {
            var dateA = new Date(cellA);
            var dateB = new Date(cellB);
            return sortDirection * (dateA - dateB);
        } else {
            return sortDirection * cellA.localeCompare(cellB);
        }
    });
    
    // 更新表格显示
    for (var i = 0; i < sortedRows.length; i++) {
        tbody.appendChild(sortedRows[i]);
    }
    
    // 更新表格头部排序指示器
    var headers = table.getElementsByTagName('th');
    for (var j = 0; j < headers.length; j++) {
        headers[j].classList.remove('sort-asc', 'sort-desc');
    }
    
    if (headers[colIndex]) {
        headers[colIndex].classList.add(sortDirection == 1 ? 'sort-asc' : 'sort-desc');
    }
}

/**
 * 显示遮罩层
 * @param {string} title - 标题
 * @param {string} content - 内容
 * @param {Array} buttons - 按钮配置数组，格式：[{text: '按钮文字', callback: 回调函数, class: 'btn-class'}]
 */
function showMask(title, content, buttons) {
    // 移除已存在的遮罩
    closeMask();
    
    // 创建遮罩层
    var mask = document.createElement('div');
    mask.className = 'mask-container';
    
    // 创建对话框
    var dialog = document.createElement('div');
    dialog.className = 'mask-dialog';
    
    // 创建标题栏
    var titleBar = document.createElement('div');
    titleBar.className = 'mask-title';
    titleBar.innerHTML = '<h3>' + title + '</h3><span class="mask-close">&times;</span>';
    
    // 创建内容区
    var contentArea = document.createElement('div');
    contentArea.className = 'mask-content';
    contentArea.innerHTML = content;
    
    // 创建按钮区
    var buttonArea = document.createElement('div');
    buttonArea.className = 'mask-buttons';
    
    if (buttons && buttons.length > 0) {
        buttons.forEach(function(btn) {
            var button = document.createElement('button');
            button.className = 'mask-button ' + (btn.class || '');
            button.textContent = btn.text;
            
            if (btn.callback && typeof btn.callback === 'function') {
                button.addEventListener('click', function() {
                    btn.callback();
                });
            }
            
            buttonArea.appendChild(button);
        });
    }
    
    // 组装对话框
    dialog.appendChild(titleBar);
    dialog.appendChild(contentArea);
    dialog.appendChild(buttonArea);
    mask.appendChild(dialog);
    
    // 添加到页面
    document.body.appendChild(mask);
    
    // 绑定关闭事件
    var closeBtn = mask.querySelector('.mask-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeMask);
    }
    
    // 绑定点击遮罩层关闭
    mask.addEventListener('click', function(e) {
        if (e.target === mask) {
            closeMask();
        }
    });
    
    // 禁止页面滚动
    document.body.style.overflow = 'hidden';
    
    // 调整内容区域最大高度
    var contentMaxHeight = window.innerHeight * 0.7;
    contentArea.style.maxHeight = contentMaxHeight + 'px';
}

/**
 * 关闭遮罩层
 */
function closeMask() {
    var mask = document.querySelector('.mask-container');
    if (mask) {
        mask.parentNode.removeChild(mask);
    }
    
    // 恢复页面滚动
    document.body.style.overflow = '';
}

/**
 * 输入提示功能
 * @param {string} inputId - 输入框ID
 * @param {string} url - 获取提示数据的URL
 * @param {Object} params - 附加参数
 * @param {Function} callback - 选择项后的回调函数
 */
function searchSuggest(inputId, url, params, callback) {
    var input = document.getElementById(inputId);
    if (!input) return;
    
    // 创建提示容器
    var suggestContainer = document.createElement('div');
    suggestContainer.className = 'suggest-container';
    suggestContainer.style.display = 'none';
    input.parentNode.appendChild(suggestContainer);
    
    // 定位提示容器
    function positionSuggest() {
        var rect = input.getBoundingClientRect();
        suggestContainer.style.top = (rect.bottom + window.scrollY) + 'px';
        suggestContainer.style.left = (rect.left + window.scrollX) + 'px';
        suggestContainer.style.width = rect.width + 'px';
    }
    
    // 暂存上一次请求的关键词
    var lastKeyword = '';
    var debounceTimer = null;
    
    // 处理输入事件
    input.addEventListener('input', function() {
        var keyword = input.value.trim();
        
        // 清除上一次的定时器
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }
        
        // 如果关键词为空，隐藏提示
        if (!keyword) {
            suggestContainer.style.display = 'none';
            return;
        }
        
        // 如果关键词与上一次相同，不重新请求
        if (keyword === lastKeyword) {
            return;
        }
        
        // 设置300ms的防抖定时器
        debounceTimer = setTimeout(function() {
            lastKeyword = keyword;
            
            // 准备请求参数
            var requestParams = Object.assign({}, params || {}, {
                keyword: keyword
            });
            
            // 发送请求获取提示数据
            ajaxRequest(url, requestParams, function(response) {
                if (response.code === 0 && Array.isArray(response.data) && response.data.length > 0) {
                    // 清空容器
                    suggestContainer.innerHTML = '';
                    
                    // 添加关闭按钮
                    var closeButton = document.createElement('div');
                    closeButton.className = 'suggest-close';
                    closeButton.textContent = '关闭';
                    closeButton.addEventListener('click', function() {
                        suggestContainer.style.display = 'none';
                    });
                    suggestContainer.appendChild(closeButton);
                    
                    // 限制最多显示10条
                    var maxItems = Math.min(10, response.data.length);
                    
                    // 添加提示项
                    for (var i = 0; i < maxItems; i++) {
                        var item = response.data[i];
                        var suggestionItem = document.createElement('div');
                        suggestionItem.className = 'suggest-item';
                        suggestionItem.textContent = item.text || item.name || item;
                        suggestionItem.setAttribute('data-value', item.value || item.id || item);
                        
                        // 绑定点击事件
                        suggestionItem.addEventListener('click', function() {
                            var value = this.getAttribute('data-value');
                            var text = this.textContent;
                            
                            // 设置输入框值
                            input.value = text;
                            
                            // 隐藏提示
                            suggestContainer.style.display = 'none';
                            
                            // 触发回调
                            if (callback && typeof callback === 'function') {
                                callback(value, text);
                            }
                        });
                        
                        suggestContainer.appendChild(suggestionItem);
                    }
                    
                    // 显示提示容器
                    positionSuggest();
                    suggestContainer.style.display = 'block';
                } else {
                    suggestContainer.style.display = 'none';
                }
            }, function() {
                suggestContainer.style.display = 'none';
            });
        }, 300);
    });
    
    // 点击输入框时，如果有数据则显示提示
    input.addEventListener('click', function() {
        if (lastKeyword && suggestContainer.childElementCount > 1) {
            positionSuggest();
            suggestContainer.style.display = 'block';
        }
    });
    
    // 点击页面其他区域隐藏提示
    document.addEventListener('click', function(e) {
        if (e.target !== input && !suggestContainer.contains(e.target)) {
            suggestContainer.style.display = 'none';
        }
    });
    
    // 窗口调整大小时重新定位
    window.addEventListener('resize', function() {
        if (suggestContainer.style.display === 'block') {
            positionSuggest();
        }
    });
}
