CREATE TABLE IF NOT EXISTS `llx_cfdiutils_payment_det` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `fk_cfdipay` int(11) DEFAULT NULL,
  `fk_paiement_facture` int(11) DEFAULT NULL,
  `parcialidad` int(11) DEFAULT NULL,
  `moneda` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `saldo_anterior` float DEFAULT NULL,
  `pago` float DEFAULT NULL,
  `saldo_insoluto` float DEFAULT NULL,
  `equivalencia` float DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  PRIMARY KEY (`rowid`)
);
