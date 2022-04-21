CREATE TABLE IF NOT EXISTS `llx_cfdiutils_payment_rel` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `fk_paiement` int(11) DEFAULT NULL,
  `extuuid` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `fk_tipo_rel` int(11) NOT NULL,
  `fk_cfdutils_payment_rel` int(11) NOT NULL,
  PRIMARY KEY (`rowid`)
);
