CREATE TABLE IF NOT EXISTS `llx_c_cfdiutils_exportacion` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`rowid`)
);
