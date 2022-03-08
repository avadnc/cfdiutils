CREATE TABLE `llx_cfdiutils_facture_rel` (
	`rowid` INT(11) NOT NULL AUTO_INCREMENT,
	`fk_rel` INT NOT NULL,
    `fk_facture` INT DEFAULT NULL,
	`uuid` VARCHAR(50) DEFAULT NULL,
	PRIMARY KEY (`rowid`)
);
