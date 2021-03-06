CREATE TABLE IF NOT EXISTS `llx_cfdiutils_payment` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `fk_payment` int(11) NOT NULL,
  `usocfdi` varchar(50) NOT NULL,
  `tipodecambio` float DEFAULT NULL,
  `fecha_emision` varchar(50) DEFAULT NULL,
  `fecha_timbrado` varchar(50) DEFAULT NULL,
  `cer_csd` varchar(50) DEFAULT NULL,
  `cer_sat` varchar(50) DEFAULT NULL,
  `uuid` varchar(50) DEFAULT NULL,
  `NumOperacion` varchar(50) DEFAULT NULL,
  `RfcEmisorCtaOrd` varchar(50) DEFAULT NULL,
  `NomBancoOrdExt` varchar(50) DEFAULT NULL,
  `CtaOrdenante` varchar(50) DEFAULT NULL,
  `RfcEmisorCtaBen` varchar(50) DEFAULT NULL,
  `CtaBeneficiario` varchar(50) DEFAULT NULL,
  `TipoCadPago` varchar(50) DEFAULT NULL,
  `CertPago` text DEFAULT NULL,
  `CadPago` text DEFAULT NULL,
  `SelloPago` text DEFAULT NULL,
  `pac` varchar(255) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `error` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rowid`)
);
