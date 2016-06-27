CREATE TABLE `[{table}]` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `timestamp` datetime,
  `level` int(1),
  `name` varchar(255),
  `message` text,
  `context` text,
  PRIMARY KEY (`id`)
) ;