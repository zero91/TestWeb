/* DROP TABLE IF EXISTS `jhz_ucenter_user`; */
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


/* DROP TABLE IF EXISTS jhz_user; */
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


/* DROP TABLE IF EXISTS jhz_user_tag; */
CREATE TABLE jhz_user_tag (
  `uid` INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `tag` VARCHAR(64) NOT NULL COMMENT '标签',

  UNIQUE KEY `uid_tag` (`uid`, `tag`),
  KEY `uid` (`uid`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '用户兴趣标签';


/* DROP TABLE IF EXISTS jhz_article; */
CREATE TABLE jhz_article (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `nickname` CHAR(32) NOT NULL COMMENT '用户昵称',
  `author` CHAR(32) DEFAULT NULL COMMENT '原文作者，若为原创，则为NULL',
  `source` VARCHAR(512) DEFAULT NULL COMMENT '原文地址',
  `title` VARCHAR(128) NOT NULL COMMENT '标题',
  `content` MEDIUMTEXT NOT NULL COMMENT '详细内容',
  `views` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '阅读量',
  `ip` BIGINT(20) NOT NULL DEFAULT '0' COMMENT '用户发表时的IP',
  `status` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '文章状态',
  `create_time` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `comments` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '回复数',
  `collects` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '收藏量',

  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `author` (`author`),
  KEY `views` (`views`),
  KEY `status` (`status`),
  KEY `create_time` (`create_time`),
  KEY `update_time` (`update_time`),
  KEY `comments` (`comments`),
  KEY `collects` (`collects`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '文章表';


/* DROP TABLE IF EXISTS jhz_article_tag; */
CREATE TABLE jhz_article_tag (
  `aid` INT(10) UNSIGNED NOT NULL COMMENT '文章ID',
  `tag` VARCHAR(64) NOT NULL COMMENT '标签',

  UNIQUE KEY `aid_tag` (`aid`, `tag`),
  KEY `aid` (`aid`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '文章标签表';


DROP TABLE IF EXISTS jhz_user_action;
CREATE TABLE jhz_user_action (
  `uid` INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `aid` INT(10) UNSIGNED NOT NULL COMMENT '动作对象的ID',
  `atype` CHAR(16) NOT NULL DEFAULT 'ARTICLE' COMMENT '阅读对象的类型，对应到数据库表',
  `read` SMALLINT(5) NOT NULL DEFAULT '0' COMMENT '阅读次数',
  `collect` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '是否收藏',
  `create_time` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '首次阅读时间',
  `update_time` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最近更新时间',

  UNIQUE KEY `key` (`uid`, `aid`, `atype`),
  KEY `uid` (`uid`),
  KEY `read_target` (`aid`, `atype`),
  KEY `read` (`read`),
  KEY `create_time` (`create_time`),
  KEY `update_time` (`update_time`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '用户动作表';


DROP TABLE IF EXISTS jhz_article_comment;
CREATE TABLE jhz_article_comment (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '文章评论ID号',
  `uid` INT(10) UNSIGNED NOT NULL COMMENT '用户ID号',
  `nickname` CHAR(16) NOT NULL COMMENT '用户昵称',
  `aid` INT(10) UNSIGNED NOT NULL COMMENT '文章ID号',
  `content` TEXT NOT NULL COMMENT '评论内容',
  `create_time` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `up` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点赞数',
  `down` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点鄙视数',

  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `aid` (`aid`),
  KEY `create_time` (`create_time`),
  KEY `update_time` (`update_time`),
  KEY `up`(`up`),
  KEY `down`(`down`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT="服务评论表";


/* DROP TABLE IF EXISTS jhz_material; */
CREATE TABLE jhz_material (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '资料ID号',
  `uid` INT(10) UNSIGNED NOT NULL COMMENT '用户ID号',
  `url` VARCHAR(512) DEFAULT NULL COMMENT '访问链接',
  `password` VARCHAR(32) DEFAULT NULL COMMENT '访问密码',
  `title` VARCHAR(64) NOT NULL COMMENT '资料主题',
  `reason` TEXT NOT NULL COMMENT '推荐理由',
  `views` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '访问数量',
  `ip` BIGINT(20) NOT NULL DEFAULT '0' COMMENT '用户IP',
  `status` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '资料状态',
  `create_time` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间',

  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `views` (`views`),
  KEY `create_time` (`create_time`),
  KEY `update_time` (`update_time`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT="学习资料表";


/* DROP TABLE IF EXISTS jhz_material_tag; */
CREATE TABLE jhz_material_tag (
  `mid` INT(10) UNSIGNED NOT NULL COMMENT '资料ID号',
  `tag` VARCHAR(64) NOT NULL COMMENT '标签',

  UNIQUE KEY `mid_tag` (`mid`, `tag`),
  KEY `mid`(`mid`),
  KEY `tag`(`tag`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT="资料标签表";

