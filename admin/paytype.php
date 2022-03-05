<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2022 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    cfdiutils/admin/about.php
 * \ingroup cfdiutils
 * \brief   About page of module Cfdiutils.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

// Libraries
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once '../lib/cfdiutils.lib.php';
dol_include_once('/cfdiutils/class/cfdifacture.class.php');

// Translations
// $langs->loadLangs(array('bills', 'companies', 'compta', 'products', 'banks', 'main', 'withdrawals'));
$langs->loadLangs(array("errors", "bills", "cfdiutils@cfdiutils"));

// Access control
if (!$user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');


/*
 * Actions
 */
if ($action == "save") {
	foreach ($_POST as $field => $value) {
		if (is_numeric($value)) {
			if (strpos($field, 'cod_') !==	false) {
				$code = explode('cod_', $field);

				$sql = "SELECT count(*) nb FROM " . MAIN_DB_PREFIX . "c_cfdiutils_formapago WHERE fk_code = " . $code[1];
				$result = $db->query($sql);
				if ($result) {
					$obj = $db->fetch_object($result);
					if ($obj->nb == 0) {
						$sql =  "INSERT INTO " . MAIN_DB_PREFIX . "c_cfdiutils_formapago (";
						$sql .= "fk_code";
						$sql .= ",code";
						$sql .= ") VALUES (";
						$sql .= $code[1];
						$sql .= ",'" . $value . "'";
						$sql .= ")";

						$result = $db->query($sql);
					} else {

						$sql = "UPDATE " . MAIN_DB_PREFIX . "c_cfdiutils_formapago";
						$sql .= " SET code = '" . $value . "' WHERE fk_code =" . $code[1];

						$result = $db->query($sql);
					}
				}
			}
		}
	}
}

// None


/*
 * View
 */
$paytype = new Paytype($db);
$payments = $paytype->getDictionary('paiement');

$form = new Form($db);

$help_url = '';

$page_name = "CfdiutilsPaytype";

llxHeader('', $langs->trans($page_name), $help_url);

// Subheader
$linkback = '<a href="' . ($backtopage ? $backtopage : DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1') . '">' . $langs->trans("BackToModuleList") . '</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Configuration header
$head = cfdiutilsAdminPrepareHead();
print dol_get_fiche_head($head, 'paytype', $langs->trans($page_name), -1, 'cfdiutils@cfdiutils');

//Body
print '<span class="opacitymedium">' . $langs->trans('Configuración Formas de pago SAT') . '</span><br><br>';
print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="save">';
print '<table class="noborder centpercent">'; //Init Table
print '<tr class="liste_titre"><td class="titlefield">Forma de Pago</td><td>Código SAT</td></tr>'; //Title Table

foreach ($payments as $cod => $id) {

	$sql = "SELECT code FROM " . MAIN_DB_PREFIX . "c_cfdiutils_formapago WHERE fk_code = " . $id;
	$resql = $db->query($sql);

	if ($resql) {
		$obj = $db->fetch_object($resql);

		print '<tr><td>' . $langs->trans("PaymentType" . strtoupper($cod)) . '</td><td><input type="text" name="cod_' . $id . '" value="' . $obj->code . '"></td></tr>';
	} else {
		print '<tr><td>' . $langs->trans("PaymentType" . strtoupper($cod)) . '</td><td><input type="text" name="cod_' . $id . '" value=""></td></tr>';
	}
}

print '</table>';
print '<br><div class="center">';
print '<input class="button button-save" type="submit" value="' . $langs->trans("Save") . '">';
print '</div>';
print '</form>';

// echo '<pre>';var_dump($payments);echo '<pre>';

// Page end
print dol_get_fiche_end();
llxFooter();
$db->close();
