CREATE TABLE `llx_cfdiutils_facture_rel_piv` (
	`rowid` INT(11) NOT NULL AUTO_INCREMENT,
	`fk_facture` INT NOT NULL,
	`fk_facture_rel` INT NOT NULL,
	PRIMARY KEY (`rowid`)
);
