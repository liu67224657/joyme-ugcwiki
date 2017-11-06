--
-- Table structure for table 私信表
--

CREATE TABLE user_board_list (
  `ubl_id` int(11) PRIMARY KEY auto_increment,
  `ub_user_id` int(11) NOT NULL default '0',
  `ub_friend_id` int(11) NOT NULL default '0',
  `ub_id` int(10) NOT NULL default '0',
  `ub_msg_count` tinyint(100) NOT NULL default '0',
  `ub_date` datetime default NULL,
  `ub_isfollow` tinyint(10) NOT NULL default '1'/* 1关注  2未关注 */
) /*$wgDBTableOptions*/;
CREATE INDEX ub_id ON user_board_list (ub_id);
CREATE INDEX ub_user_id ON user_board_list (ub_user_id);
CREATE UNIQUE INDEX ub_user_frend_id ON user_board_list (ub_user_id,ub_friend_id);

CREATE TABLE user_board (
  `ub_id` int(11) PRIMARY KEY auto_increment,
  `ub_session_id` char(32) NOT NULL default '',
  `ub_sender_uid` int(11) NOT NULL default '0',/*发送者*/
  `ub_receiver_uid` int(11) NOT NULL default '0',/*接收者*/
  `ub_message` text NOT NULL,
  `ub_date` datetime default NULL,
  `ub_status` tinyint(10) NOT NULL default '0', /* 消息状态 0未读 1已读 */
) /*$wgDBTableOptions*/;
CREATE INDEX ub_session_id ON user_board (ub_session_id);