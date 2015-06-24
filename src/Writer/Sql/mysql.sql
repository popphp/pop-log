CREATE TABLE `[{table}]` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `timestamp` datetime,
  `priority` int(1),
  `name` varchar(255),
  `message` text,
  PRIMARY KEY (`id`)
) ;