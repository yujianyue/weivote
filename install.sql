-- 投票系统数据库安装脚本
-- PHP7 + MySQL5.6 简洁实用的投票系统
-- 适用于MySQL 5.6及以上版本

-- 创建数据库(如果不存在)
-- CREATE DATABASE IF NOT EXISTS `toupiao` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

-- 使用数据库
-- USE `toupiao`;

-- 删除已存在的表(如果存在)
DROP TABLE IF EXISTS `tp_logs`;
DROP TABLE IF EXISTS `tp_recs`;
DROP TABLE IF EXISTS `tp_xuan`;
DROP TABLE IF EXISTS `tp_vote`;
DROP TABLE IF EXISTS `tp_users`;

-- 创建用户表
CREATE TABLE `tp_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` varchar(50) NOT NULL COMMENT '用户名(手机号)',
  `password` varchar(255) NOT NULL COMMENT '密码（使用哈希加密）',
  `irole` tinyint(1) DEFAULT '0' COMMENT '角色（0:普通用户,1:管理员）',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态（0:禁用,1:正常）',
  `regtime` datetime NOT NULL COMMENT '注册时间',
  `logtime` datetime DEFAULT NULL COMMENT '最后登录时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户表';

-- 创建投票主题表
CREATE TABLE `tp_vote` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主题ID',
  `title` varchar(255) NOT NULL COMMENT '投票标题',
  `idesc` text COMMENT '投票描述',
  `statime` datetime NOT NULL COMMENT '开始时间',
  `endtime` datetime NOT NULL COMMENT '结束时间',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态（0:禁用,1:正常）',
  `itype` tinyint(1) DEFAULT '0' COMMENT '类型（0:单选,1:多选）',
  `maxtime` int(11) DEFAULT '1' COMMENT '多选时最多可选几项',
  `addtime` datetime NOT NULL COMMENT '创建时间',
  `adduser` int(11) NOT NULL COMMENT '创建用户ID',
  `iview` int(11) DEFAULT '0' COMMENT '查看次数',
  PRIMARY KEY (`id`),
  KEY `adduser` (`adduser`),
  KEY `status` (`status`),
  KEY `itype` (`itype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投票主题表';

-- 创建投票选项表
CREATE TABLE `tp_xuan` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '选项ID',
  `topic_id` int(11) NOT NULL COMMENT '所属主题ID',
  `name` varchar(64) NOT NULL COMMENT '选项名称',
  `imgs` varchar(64) DEFAULT NULL COMMENT '选项图片',
  `idesc` varchar(4096) DEFAULT NULL COMMENT '选项描述',
  `addtime` datetime NOT NULL COMMENT '创建时间',
  `adduser` int(11) NOT NULL COMMENT '创建用户ID',
  `sort` int(11) DEFAULT '0' COMMENT '排序值',
  PRIMARY KEY (`id`),
  KEY `topic_id` (`topic_id`),
  KEY `sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投票选项表';

-- 创建投票记录表
CREATE TABLE `tp_recs` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '投票记录ID',
  `topic_id` int(11) NOT NULL COMMENT '主题ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `option_id` int(11) NOT NULL COMMENT '选项ID',
  `vote_time` datetime NOT NULL COMMENT '投票时间',
  `ip` varchar(50) NOT NULL COMMENT '投票IP',
  PRIMARY KEY (`id`),
  KEY `topic_id` (`topic_id`),
  KEY `user_id` (`user_id`),
  KEY `option_id` (`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投票记录表';

-- 创建日志表
CREATE TABLE `tp_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `action` varchar(50) NOT NULL COMMENT '操作类型',
  `idesc` text COMMENT '操作内容',
  `ip` varchar(50) NOT NULL COMMENT 'IP地址',
  `logtime` datetime NOT NULL COMMENT '日志时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `logtime` (`logtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='日志表';

-- 创建默认管理员账号 (用户名: 13800000000, 密码: admin123)
INSERT INTO `tp_users` (`username`, `password`, `irole`, `status`, `regtime`) VALUES
('13800000000', '$2y$10$QlxApDyBa0WU9g/LSqA5TOtRYrxC8oe9qJSHQTuYUOTl2VGtvH.1W', 1, 1, NOW());

-- 添加一些示例数据

-- 添加示例投票
INSERT INTO `tp_vote` (`title`, `idesc`, `statime`, `endtime`, `status`, `itype`, `maxtime`, `addtime`, `adduser`, `iview`) VALUES
('最喜欢的水果', '请选择您最喜欢的水果', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 7 DAY), 1, 0, 1, NOW(), 1, 0),
('最想去的旅游目的地', '如果有机会，您最想去哪里旅游？', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 7 DAY), 1, 1, 3, NOW(), 1, 0);

-- 添加示例选项 - 水果投票
INSERT INTO `tp_xuan` (`topic_id`, `name`, `imgs`, `idesc`, `addtime`, `adduser`, `sort`) VALUES
(1, '苹果', '', '又脆又甜的红富士苹果', NOW(), 1, 1),
(1, '香蕉', '', '富含钾元素的香蕉', NOW(), 1, 2),
(1, '橙子', '', '维C丰富的橙子', NOW(), 1, 3),
(1, '葡萄', '', '一颗颗紧密相连的紫葡萄', NOW(), 1, 4),
(1, '西瓜', '', '夏日最佳消暑水果', NOW(), 1, 5);

-- 添加示例选项 - 旅游目的地投票
INSERT INTO `tp_xuan` (`topic_id`, `name`, `imgs`, `idesc`, `addtime`, `adduser`, `sort`) VALUES
(2, '巴黎', '', '浪漫之都，艺术之城', NOW(), 1, 1),
(2, '东京', '', '现代与传统并存的国际大都市', NOW(), 1, 2),
(2, '威尼斯', '', '水城威尼斯，乘坐贡多拉小船游览', NOW(), 1, 3),
(2, '夏威夷', '', '阳光、沙滩、冲浪天堂', NOW(), 1, 4),
(2, '北京', '', '中国首都，历史悠久的文化古城', NOW(), 1, 5);
