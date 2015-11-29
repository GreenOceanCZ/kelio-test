DROP TABLE IF EXISTS `ax12_article`;
DROP TABLE IF EXISTS `ax12_menu`;
DROP TABLE IF EXISTS `ax12_users`;
DROP TABLE IF EXISTS `ax12_typ`;


CREATE TABLE `ax12_typ` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `user_add` tinyint(1) NOT NULL,
  `user_edit` tinyint(1) NOT NULL,
  `user_delete` tinyint(1) NOT NULL,
  `menu_add` tinyint(1) NOT NULL,
  `menu_edit` tinyint(1) NOT NULL,
  `menu_delete` tinyint(1) NOT NULL,
  `article_add` tinyint(1) NOT NULL,
  `article_edit` tinyint(1) NOT NULL,
  `article_delete` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ax12_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL,
  `password` text NOT NULL,
  `email` text NOT NULL,
  `ax12_typ_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`),
  KEY `ax12_typ_id` (`ax12_typ_id`),
  CONSTRAINT `ax12_users_ibfk_1` FOREIGN KEY (`ax12_typ_id`) REFERENCES `ax12_typ` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ax12_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sortid` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `name` text NOT NULL,
  `title` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `ax12_article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `text` text NOT NULL,
  `lastedit` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `ax12_menu_id` int(11) NOT NULL,
  `ax12_users_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ax12_menu_id` (`ax12_menu_id`),
  KEY `ax12_users_id` (`ax12_users_id`),
  CONSTRAINT `ax12_article_ibfk_1` FOREIGN KEY (`ax12_menu_id`) REFERENCES `ax12_menu` (`id`),
  CONSTRAINT `ax12_article_ibfk_2` FOREIGN KEY (`ax12_users_id`) REFERENCES `ax12_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `ax12_typ` (`id`, `name`, `user_add`, `user_edit`, `user_delete`, `menu_add`, `menu_edit`, `menu_delete`, `article_add`, `article_edit`, `article_delete`) VALUES
(1,	'Správce',	1,	1,	1,	1,	1,	1,	1,	1,	1),
(2,	'Host',	0,	0,	0,	0,	0,	0,	0,	0,	0);
INSERT INTO `ax12_users` (`id`, `login`, `password`, `email`, `ax12_typ_id`) VALUES
(1,	'admin',	'$2y$10$37ns89SWIU83ngyTjdvmsuhGPh5T2rghrGlp5SXpR5HFyh7EXM/k6',	'bgthnev@gmail.com',	1),
(2,	'demo',	'$2y$10$W0DYZx5GzqQxxSUAn3a7q.8G7xZL2/77GoYhDtx7uSRpmMzoZ8k5e',	'bgthnev@gmail.com',	2);
