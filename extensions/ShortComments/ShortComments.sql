//page_addons ���¸��ӱ�����wiki����ӣ�

CREATE TABLE IF NOT EXISTS `page_addons` (
  `page_id` INT(11) NOT NULL DEFAULT '0' COMMENT '����id',
  `like_count` INT(11) NOT NULL DEFAULT '0' COMMENT '���µ���',
  `short_comment_count` INT(11) NOT NULL DEFAULT '0' COMMENT '��������',
  PRIMARY KEY (`page_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='���¸��ӱ�';
//page_commentary ���¶���������wiki����ӣ�

CREATE TABLE IF NOT EXISTS `page_short_comment` (
  `psc_id` INT(11) NOT NULL AUTO_INCREMENT,
  `page_id` INT(11) NOT NULL DEFAULT '0' COMMENT '����id',
  `like_count` INT(11) NOT NULL DEFAULT '0' COMMENT '��������',
  `body` VARCHAR(100) NOT NULL DEFAULT '0' COMMENT '���¶���',
  PRIMARY KEY (`psc_id`),INDEX(page_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='���¶���';