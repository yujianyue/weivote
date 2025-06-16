<?php

// 费心PHP+mysql极简通用多主题投票系统 V2025.05.08
// 演示地址1: http://demo.fxtp.cn
// 演示地址2: http://wevote.chalide.cn
// 文件路径: inc/sqls.php
// 文件大小: 7347 字节
// 最后修改时间: 2025-05-09 06:50:58
// 作者: yujianyue
// 邮件: 15058593138@qq.com Bug反馈或意见建议
// 版权所有,保留发行权和署名权
/**
 * 数据库操作类
 */

require_once 'conn.php';

class DB {
    private $conn;
    private $prefix;
    
    /**
     * 构造函数，初始化数据库连接
     */
    public function __construct() {
        global $CONFIG;
        $this->conn = dbConnect();
        $this->prefix = $CONFIG['db']['prefix'];
    }
    
    /**
     * 析构函数，关闭数据库连接
     */
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
    
    /**
     * 转义字符串
     * @param string $str 需要转义的字符串
     * @return string 转义后的字符串
     */
    public function escape($str) {
        return $this->conn->real_escape_string($str);
    }
    
    /**
     * 获取带前缀的表名
     * @param string $table 表名
     * @return string 带前缀的表名
     */
    public function table($table) {
        return $this->prefix . $table;
    }
    
    /**
     * 执行SQL查询
     * @param string $sql SQL语句
     * @return mysqli_result|bool 查询结果
     */
    public function query($sql) {
        return $this->conn->query($sql);
    }
    
    /**
     * 获取一条记录
     * @param string $table 表名（不带前缀）
     * @param string|array $where 查询条件
     * @param string $fields 返回字段
     * @return array|null 查询结果
     */
    public function getOne($table, $where = '', $fields = '*') {
        $table = $this->table($table);
        $whereStr = $this->parseWhere($where);
        
        $sql = "SELECT {$fields} FROM {$table} {$whereStr} LIMIT 1";
        $result = $this->query($sql);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * 获取多条记录
     * @param string $table 表名（不带前缀）
     * @param string|array $where 查询条件
     * @param string $fields 返回字段
     * @param string $order 排序方式
     * @param string $limit 限制条数
     * @return array 查询结果
     */
    public function getAll($table, $where = '', $fields = '*', $order = '', $limit = '') {
        $table = $this->table($table);
        $whereStr = $this->parseWhere($where);
        $orderStr = $order ? "ORDER BY {$order}" : '';
        $limitStr = $limit ? "LIMIT {$limit}" : '';
        
        $sql = "SELECT {$fields} FROM {$table} {$whereStr} {$orderStr} {$limitStr}";
        $result = $this->query($sql);
        
        $rows = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        
        return $rows;
    }
    
    /**
     * 插入数据
     * @param string $table 表名（不带前缀）
     * @param array $data 数据数组
     * @return int|bool 成功返回插入ID，失败返回false
     */
    public function insert($table, $data) {
        $table = $this->table($table);
        
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "`{$key}`";
            if ($value === null) {
                $values[] = "NULL";
            } else {
                $values[] = "'" . $this->escape($value) . "'";
            }
        }
        
        $fieldsStr = implode(', ', $fields);
        $valuesStr = implode(', ', $values);
        
        $sql = "INSERT INTO {$table} ({$fieldsStr}) VALUES ({$valuesStr})";
        $result = $this->query($sql);
        
        if ($result) {
            return $this->conn->insert_id;
        }
        
        return false;
    }
    
    /**
     * 更新数据
     * @param string $table 表名（不带前缀）
     * @param array $data 数据数组
     * @param string|array $where 更新条件
     * @return bool 更新结果
     */
    public function update($table, $data, $where) {
        $table = $this->table($table);
        $whereStr = $this->parseWhere($where);
        
        $set = [];
        foreach ($data as $key => $value) {
            if ($value === null) {
                $set[] = "`{$key}` = NULL";
            } else {
                $set[] = "`{$key}` = '" . $this->escape($value) . "'";
            }
        }
        
        $setStr = implode(', ', $set);
        
        $sql = "UPDATE {$table} SET {$setStr} {$whereStr}";
        $result = $this->query($sql);
        
        return $result !== false;
    }
    
    /**
     * 删除数据
     * @param string $table 表名（不带前缀）
     * @param string|array $where 删除条件
     * @return bool 删除结果
     */
    public function delete($table, $where) {
        $table = $this->table($table);
        $whereStr = $this->parseWhere($where);
        
        if (empty($whereStr)) {
            return false; // 防止误删除全表
        }
        
        $sql = "DELETE FROM {$table} {$whereStr}";
        $result = $this->query($sql);
        
        return $result !== false;
    }
    
    /**
     * 获取记录数量
     * @param string $table 表名（不带前缀）
     * @param string|array $where 查询条件
     * @return int 记录数量
     */
    public function count($table, $where = '') {
        $table = $this->table($table);
        $whereStr = $this->parseWhere($where);
        
        $sql = "SELECT COUNT(*) AS count FROM {$table} {$whereStr}";
        $result = $this->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return (int) $row['count'];
        }
        
        return 0;
    }
    
    /**
     * 开始事务
     */
    public function startTransaction() {
        $this->conn->autocommit(false);
    }
    
    /**
     * 提交事务
     */
    public function commit() {
        $this->conn->commit();
        $this->conn->autocommit(true);
    }
    
    /**
     * 回滚事务
     */
    public function rollback() {
        $this->conn->rollback();
        $this->conn->autocommit(true);
    }
    
    /**
     * 解析查询条件
     * @param string|array $where 查询条件
     * @return string 解析后的WHERE子句
     */
    private function parseWhere($where) {
        if (empty($where)) {
            return '';
        }
        
        // 如果是字符串，直接返回
        if (is_string($where)) {
            return "WHERE {$where}";
        }
        
        // 如果是数组，解析为查询条件
        if (is_array($where)) {
            $conditions = [];
            
            foreach ($where as $key => $value) {
                if ($value === null) {
                    $conditions[] = "`{$key}` IS NULL";
                } else {
                    $conditions[] = "`{$key}` = '" . $this->escape($value) . "'";
                }
            }
            
            return "WHERE " . implode(' AND ', $conditions);
        }
        
        return '';
    }
    
    /**
     * 获取最后的错误信息
     * @return string 错误信息
     */
    public function getError() {
        return $this->conn->error;
    }
    
    /**
     * 获取最后执行的SQL影响行数
     * @return int 影响行数
     */
    public function affectedRows() {
        return $this->conn->affected_rows;
    }
}
