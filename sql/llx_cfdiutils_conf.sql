CREATE TABLE IF NOT EXISTS `llx_cfdiutils_conf` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(10) DEFAULT NULL, /* CSD - KEY - PASS */
  `value` varchar(255) DEFAULT NULL, 
  `entity` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rowid`)
);
