CREATE TABLE `llx_cfdiutils_facture_rel` (
	`rowid` INT(11) NOT NULL AUTO_INCREMENT,
	`fk_facture` INT DEFAULT NULL,
	`extuuid` VARCHAR(50) DEFAULT NULL,
	`fk_tipo_rel` INT NOT NULL,
	`fk_facture_rel` INT NOT NULL,
	PRIMARY KEY (`rowid`)
);
