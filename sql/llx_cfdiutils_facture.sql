CREATE TABLE IF NOT EXISTS `llx_cfdiutils_facture` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `fk_facture` int(11) NOT NULL,
  `usocfdi` varchar(50) NOT NULL,
  `condicion_pago` varchar(50) NOT NULL,
  `forma_pago` varchar(50) NOT NULL,
  `metodo_pago` varchar(50) NOT NULL,
  `exportacion` varchar(50) NOT NULL,
  `fecha_emision` varchar(50) DEFAULT NULL,
  `fecha_timbrado` varchar(50)  DEFAULT NULL,
  `cer_csd` varchar(50) DEFAULT NULL,
  `cer_sat` varchar(50) DEFAULT NULL,
  `uuid` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`rowid`)
) ;
