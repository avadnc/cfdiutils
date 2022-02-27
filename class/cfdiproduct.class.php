<?php

require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

class Cfdiproduct extends Product
{

	public $umed;
	public $fk_product;
	public $claveprodserv;
	public $objetoimp;
	public $unidad;

	public $fields = array(
		'rowid' => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 10),
		'fk_product' => array('type' => 'integer:Product:product/class/product.class.php:1', 'label' => 'Fkproduct', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 15),
		'umed' => array('type' => 'varchar(50)', 'label' => 'Umed', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 20),
		'claveprodserv' => array('type' => 'varchar(50)', 'label' => 'Claveprodserv', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 25),
		'objetoimp' => array('type' => 'varchar(2)', 'label' => 'ObjetoImp', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 30),
		'unidad' => array('type' => 'varchar(50)', 'label' => 'Unidad', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 40),
		'entity' => array('type'=>'integer','label'=>'entity', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 45)
	);


	public function createFiscal()
	{
		$error = 0;
		$this->error = [];

		$this->fk_product = $this->id;

		if (!empty($this->umed)) {
			$this->umed = dol_sanitizeFileName(dol_string_nospecial(trim($this->umed)));

		} else {
			$error++;
			$this->error = 'FailUmed';
		}
		if (!empty($this->claveprodserv)) {

			$this->claveprodserv = dol_sanitizeFileName(dol_string_nospecial(trim($this->claveprodserv)));
		} else {
			$error++;
			$this->error = 'FailClaveprodserv';
		}
		if (!empty($this->objetoimp)) {
			$this->objetoimp = dol_sanitizeFileName(dol_string_nospecial(trim($this->objetoimp)));
		} else {
			$error++;
			$this->error = 'FailObjetoimp';
		}
		if (!empty($this->unidad)) {
			$this->unidad = dol_sanitizeFileName(dol_string_nospecial(trim($this->unidad)));
		} else {
			$error++;
			$this->error = 'Failunidad';
		}

		if ($error != 0) {
			dol_syslog(get_class($this) . "::Create fails verify " . join(',', $this->error), LOG_WARNING);
			return -$error;
		}

		$this->db->begin();
		$sql = "SELECT count(*) as nb FROM " . MAIN_DB_PREFIX . "cfdiutils_product where fk_product = " . $this->fk_product;
		$result = $this->db->query($sql);
		if ($result) {
			$obj = $this->db->fetch_object($result);
			if ($obj->nb == 0) {
				//No existe registro fiscal del producto
				$sql = "INSERT INTO " . MAIN_DB_PREFIX . "cfdiutils_product (";
				$sql .= "fk_product";
				$sql .= ", umed";
				$sql .= ", claveprodserv";
				$sql .= ", objetoimp";
				$sql .= ", unidad";
				$sql .= ",entity";
				$sql .= ") VALUES (";
				$sql .= $this->fk_product;
				$sql .= ",'" . $this->umed . "'";
				$sql .= ",'" . $this->claveprodserv . "'";
				$sql .= ",'" . $this->objetoimp . "'";
				$sql .= ",'" . $this->unidad . "'";
				$sql .= ",".$this->entity;
				$sql .= ")";

				dol_syslog(get_class($this) . "::Create", LOG_DEBUG);

				$result = $this->db->query($sql);
				if (!$result) {
					$error++;
					$this->error = $this->db->lasterror();
				}

				if (!$error) {
					$this->db->commit();
					return 'InsertSuccess';
				} else {
					$this->db->rollback();
					return -$error;
				}
			}
		}
	}

	public function updateFiscal()
	{
	}

	public function deleteFiscal()
	{
	}

	public function getFiscal()
	{

		$sql = "SELECT umed, claveprodserv, objetoimp, unidad FROM " . MAIN_DB_PREFIX . "cfdiutils_product WHERE fk_product = " . $this->id;
		$resql = $this->db->query($sql);
		$num_rows = $this->db->num_rows($resql);

		if ($num_rows > 0) {
			$obj = $this->db->fetch_object($resql);

			$this->umed = $obj->umed;
			$this->claveprodserv = $obj->claveprodserv;
			$this->objetoimp = $obj->objetoimp;
			$this->unidad = $obj->unidad;
		}
	}
}
