CREATE TABLE `llx_cfdiutils_product` (
	`rowid` INT NOT NULL AUTO_INCREMENT,
	`fk_product` INT NOT NULL UNIQUE,
	`umed` VARCHAR(50) NOT NULL DEFAULT '',
	`claveprodserv` VARCHAR(50) NOT NULL DEFAULT '',
	`objetoimp` VARCHAR(2) NOT NULL DEFAULT '',
	`unidad` VARCHAR(50) DEFAULT '',
    `entity` INT NOT NULL DEFAULT 0,
	PRIMARY KEY (`rowid`)
) ENGINE=innodb;
