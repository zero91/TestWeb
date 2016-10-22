DROP TABLE IF EXISTS `jhz_ucenter_user`;
CREATE TABLE `jhz_ucenter_user` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID号',
  `username` CHAR(32) NOT NULL COMMENT '用户名',
  `password` CHAR(32) NOT NULL COMMENT '密码',
  `email` VARCHAR(64) NOT NULL COMMENT '邮件',
  `gender` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '性别(男:0,女:1)',
  `birthday` DATE DEFAULT NULL COMMENT '出生日期',
  `mobile` CHAR(15) DEFAULT NULL COMMENT '用户手机',
  `qq` CHAR(10) DEFAULT NULL COMMENT 'QQ号',
  `wechat` VARCHAR(32) DEFAULT NULL COMMENT '微信号',
  `authstr` VARCHAR(100) DEFAULT NULL COMMENT '用户验证类操作的认证字符串',
  `regtime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '注册时间',
  `regip` BIGINT(20) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `last_login_time` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `last_login_ip` BIGINT(20) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` TINYINT(4) DEFAULT '0' COMMENT '用户状态',

  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `mobile` (`mobile`),
  KEY `qq` (`qq`),
  KEY `wechat` (`wechat`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8 COMMENT='用户全局信息表';

INSERT INTO jhz_ucenter_user VALUES (10001, "admin", "64cfe8bcc08876df5ac00d0a0278d918",
        "1651372471@qq.com", 0, NULL, NULL, NULL, NULL, NULL, 1439135030, 0, 1439135030, 0, 0, 1);

DROP TABLE IF EXISTS jhz_user;
CREATE TABLE jhz_user (
  `uid` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID号',
  `nickname` CHAR(32) NOT NULL DEFAULT '' COMMENT '昵称',
  `login` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '登录次数',
  `score` MEDIUMINT(8) NOT NULL DEFAULT '0' COMMENT '用户积分',
  `questions` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发帖数量',
  `answers` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '回帖数量',
  `reg_time` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '注册时间',
  `reg_ip` BIGINT(20) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `last_login_time` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `last_login_ip` BIGINT(20) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `status` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '用户状态',

  PRIMARY KEY (`uid`),
  KEY `nickname` (`nickname`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8 COMMENT='用户应用信息表';
