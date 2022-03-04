CREATE TABLE IF NOT EXISTS `llx_cfdiutils_societe` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `fk_soc` int(11) NOT NULL,
  `fiscal_name` varchar(255)  NOT NULL,
  `municipio` varchar(100)  DEFAULT NULL,
  `cod_municipio` varchar(5) DEFAULT NULL,
  `localidad` varchar(100) DEFAULT NULL,
  `cod_localidad` varchar(5)  DEFAULT NULL,
  `colonia` varchar(100)  DEFAULT NULL,
  `cod_colonia` varchar(5)  DEFAULT NULL,
  PRIMARY KEY (`rowid`)
) ENGINE=InnoDB ;
