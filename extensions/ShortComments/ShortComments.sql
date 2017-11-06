//page_addons 文章附加表（单个wiki库添加）

CREATE TABLE IF NOT EXISTS `page_addons` (
  `page_id` INT(11) NOT NULL DEFAULT '0' COMMENT '文章id',
  `like_count` INT(11) NOT NULL DEFAULT '0' COMMENT '文章点赞',
  `short_comment_count` INT(11) NOT NULL DEFAULT '0' COMMENT '短评总数',
  PRIMARY KEY (`page_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='文章附加表';
//page_commentary 文章短评（单个wiki库添加）

CREATE TABLE IF NOT EXISTS `page_short_comment` (
  `psc_id` INT(11) NOT NULL AUTO_INCREMENT,
  `page_id` INT(11) NOT NULL DEFAULT '0' COMMENT '文章id',
  `like_count` INT(11) NOT NULL DEFAULT '0' COMMENT '短评点赞',
  `body` VARCHAR(100) NOT NULL DEFAULT '0' COMMENT '文章短评',
  PRIMARY KEY (`psc_id`),INDEX(page_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='文章短评';