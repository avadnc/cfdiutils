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
require_once '../class/cfdiutils.class.php';

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
$value = GETPOST('value', 'alpha');


/* Actions */
if ($action == 'updateMask') {
	$maskconstinvoice = GETPOST('maskconstinvoice', 'alpha');
	$maskconstreplacement = GETPOST('maskconstreplacement', 'alpha');
	$maskconstcredit = GETPOST('maskconstcredit', 'alpha');
	$maskconstdeposit = GETPOST('maskconstdeposit', 'alpha');
	$maskinvoice = GETPOST('maskinvoice', 'alpha');
	$maskreplacement = GETPOST('maskreplacement', 'alpha');
	$maskcredit = GETPOST('maskcredit', 'alpha');
	$maskdeposit = GETPOST('maskdeposit', 'alpha');
	if ($maskconstinvoice) {
		$res = dolibarr_set_const($db, $maskconstinvoice, $maskinvoice, 'chaine', 0, '', $conf->entity);
	}
	if ($maskconstcredit) {
		$res = dolibarr_set_const($db, $maskconstcredit, $maskcredit, 'chaine', 0, '', $conf->entity);
	}
	if ($maskconstdeposit) {
		$res = dolibarr_set_const($db, $maskconstdeposit, $maskdeposit, 'chaine', 0, '', $conf->entity);
	}

	if (!($res > 0)) {
		$error++;
	}

	if (!$error) {
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	} else {
		setEventMessages($langs->trans("Error"), null, 'errors');
	}
}
if ($action == 'setmod') {
	// TODO Verifier si module numerotation choisi peut etre active
	// par appel methode canBeActivated

	dolibarr_set_const($db, "CFDI_FACTURE_ADDON", $value, 'chaine', 0, '', $conf->entity);
}




$form = new Form($db);

$help_url = '';

$page_name = "CfdiutilsSetup";
$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
llxHeader('', $langs->trans($page_name), $help_url);

// Subheader
$linkback = '<a href="' . ($backtopage ? $backtopage : DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1') . '">' . $langs->trans("BackToModuleList") . '</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Configuration header
$head = cfdiutilsAdminPrepareHead();
print dol_get_fiche_head($head, 'documents', $langs->trans($page_name), -1, 'cfdiutils@cfdiutils');

clearstatcache();

/*
 *  Numbering module
 */

print load_fiche_titre($langs->trans("BillsNumberingModule"), '', '');

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Name") . '</td>';
print '<td>' . $langs->trans("Description") . '</td>';
print '<td class="nowrap">' . $langs->trans("Example") . '</td>';
print '<td class="center" width="60">' . $langs->trans("Status") . '</td>';
print '<td class="center" width="16">' . $langs->trans("ShortInfo") . '</td>';
print '</tr>' . "\n";

clearstatcache();

foreach ($conf->file->dol_document_root as $dirroot) {

	$dir = $dirroot . "/custom/cfdiutils/core/modules/cfdiutils/";
	if (is_dir($dir)) {
		$handle = opendir($dir);
		if (is_resource($handle)) {
			while (($file = readdir($handle)) !== false) {
				if (!is_dir($dir . $file) || (substr($file, 0, 1) <> '.' && substr($file, 0, 3) <> 'CVS')) {
					$filebis = $file;
					$classname = preg_replace('/\.php$/', '', $file);
					// For compatibility
					if (!is_file($dir . $filebis)) {
						$filebis = $file . "/" . $file . ".modules.php";
						$classname = "mod_cfdifacture_" . $file;
					}
					// Check if there is a filter on country
					preg_match('/\-(.*)_(.*)$/', $classname, $reg);
					if (!empty($reg[2]) && $reg[2] != strtoupper($mysoc->country_code)) {
						continue;
					}

					$classname = preg_replace('/\-.*$/', '', $classname);
					if (!class_exists($classname) && is_readable($dir . $filebis) && (preg_match('/mod_/', $filebis) || preg_match('/mod_/', $classname)) && substr($filebis, dol_strlen($filebis) - 3, 3) == 'php') {
						// Charging the numbering class
						require_once $dir . $filebis;

						$module = new $classname($db);

						// Show modules according to features level
						if ($module->version == 'development' && $conf->global->MAIN_FEATURES_LEVEL < 2) {
							continue;
						}
						if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) {
							continue;
						}

						if ($module->isEnabled()) {
							print '<tr class="oddeven"><td width="100">';
							echo preg_replace('/\-.*$/', '', preg_replace('/mod_cfdifacture_/', '', preg_replace('/\.php$/', '', $file)));
							print "</td><td>\n";

							print $module->info();

							print '</td>';

							// Show example of numbering module
							print '<td class="nowrap">';
							$tmp = $module->getExample();
							if (preg_match('/^Error/', $tmp)) {
								$langs->load("errors");
								print '<div class="error">' . $langs->trans($tmp) . '</div>';
							} elseif ($tmp == 'NotConfigured') {
								print '<span class="opacitymedium">' . $langs->trans($tmp) . '</span>';
							} else {
								print $tmp;
							}
							print '</td>' . "\n";

							print '<td class="center">';
							//print "> ".$conf->global->FACTURE_ADDON." - ".$file;
							if ($conf->global->CFDI_FACTURE_ADDON == $file || $conf->global->CFDI_FACTURE_ADDON . '.php' == $file) {
								print img_picto($langs->trans("Activated"), 'switch_on');
							} else {
								print '<a class="reposition" href="' . $_SERVER["PHP_SELF"] . '?action=setmod&token=' . newToken() . '&value=' . preg_replace('/\.php$/', '', $file) . '&scan_dir=' . $module->scandir . '&label=' . urlencode($module->name) . '" alt="' . $langs->trans("Default") . '">' . img_picto($langs->trans("Disabled"), 'switch_off') . '</a>';
							}
							print '</td>';

							$cfdi = new Cfdiutils($db);
							$cfdi->initAsSpecimen();

							// Example for standard invoice
							$htmltooltip = '';
							$htmltooltip .= '' . $langs->trans("Version") . ': <b>' . $module->getVersion() . '</b><br>';
							$nextval = $module->getNextValue($mysoc, $cfdi);
							if ("$nextval" != $langs->trans("NotAvailable")) {  // Keep " on nextval
								$htmltooltip .= $langs->trans("NextValueForInvoices") . ': ';
								if ($nextval) {
									if (preg_match('/^Error/', $nextval) || $nextval == 'NotConfigured') {
										$nextval = $langs->trans($nextval);
									}
									$htmltooltip .= $nextval . '<br>';
								} else {
									$htmltooltip .= $langs->trans($module->error) . '<br>';
								}
							}



							print '<td class="center">';
							print $form->textwithpicto('', $htmltooltip, 1, 0);

							if ($conf->global->CFDI_FACTURE_ADDON . '.php' == $file) {  // If module is the one used, we show existing errors
								if (!empty($module->error)) {
									dol_htmloutput_mesg($module->error, '', 'error', 1);
								}
							}

							print '</td>';

							print "</tr>\n";
						}
					}
				}
			}
			closedir($handle);
		}
	}
}

print '</table>';
print '</div>';


/*
 *  Document templates generators
 */
print '<br>';
print load_fiche_titre($langs->trans("BillsPDFModules"), '', '');

// Load array def with activated templates
$type = 'invoice';
$def = array();
$sql = "SELECT nom";
$sql .= " FROM " . MAIN_DB_PREFIX . "document_model";
$sql .= " WHERE type = '" . $db->escape($type) . "'";
$sql .= " AND entity = " . $conf->entity;
$resql = $db->query($sql);
if ($resql) {
	$i = 0;
	$num_rows = $db->num_rows($resql);
	while ($i < $num_rows) {
		$array = $db->fetch_array($resql);
		array_push($def, $array[0]);
		$i++;
	}
} else {
	dol_print_error($db);
}

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Name") . '</td>';
print '<td>' . $langs->trans("Description") . '</td>';
print '<td class="center" width="60">' . $langs->trans("Status") . '</td>';
print '<td class="center" width="60">' . $langs->trans("Default") . '</td>';
print '<td class="center" width="32">' . $langs->trans("ShortInfo") . '</td>';
print '<td class="center" width="32">' . $langs->trans("Preview") . '</td>';
print "</tr>\n";

clearstatcache();

$activatedModels = array();

foreach ($dirmodels as $reldir) {
	// echo $reldir;exit;
	foreach (array('', '/doc') as $valdir) {
		$realpath = $reldir . "custom/cfdiutils/core/modules/cfdiutils" . $valdir;
		$dir = dol_buildpath($realpath);

		if (is_dir($dir)) {
			$handle = opendir($dir);
			if (is_resource($handle)) {
				while (($file = readdir($handle)) !== false) {
					$filelist[] = $file;
				}
				closedir($handle);
				arsort($filelist);

				foreach ($filelist as $file) {
					if (preg_match('/\.modules\.php$/i', $file) && preg_match('/^(pdf_|doc_)/', $file)) {
						if (file_exists($dir . '/' . $file)) {
							$name = substr($file, 4, dol_strlen($file) - 16);
							$classname = substr($file, 0, dol_strlen($file) - 12);

							require_once $dir . '/' . $file;
							$module = new $classname($db);

							$modulequalified = 1;
							if ($module->version == 'development' && $conf->global->MAIN_FEATURES_LEVEL < 2) {
								$modulequalified = 0;
							}
							if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) {
								$modulequalified = 0;
							}

							if ($modulequalified) {
								print '<tr class="oddeven"><td width="100">';
								print(empty($module->name) ? $name : $module->name);
								print "</td><td>\n";
								if (method_exists($module, 'info')) {
									print $module->info($langs);
								} else {
									print $module->description;
								}
								print '</td>';

								// Active
								if (in_array($name, $def)) {
									print '<td class="center">' . "\n";
									print '<a class="reposition" href="' . $_SERVER["PHP_SELF"] . '?action=del&token=' . newToken() . '&value=' . urlencode($name) . '">';
									print img_picto($langs->trans("Enabled"), 'switch_on');
									print '</a>';
									print '</td>';
								} else {
									print '<td class="center">' . "\n";
									print '<a class="reposition" href="' . $_SERVER["PHP_SELF"] . '?action=set&token=' . newToken() . '&value=' . urlencode($name) . '&scan_dir=' . urlencode($module->scandir) . '&label=' . urlencode($module->name) . '">' . img_picto($langs->trans("SetAsDefault"), 'switch_off') . '</a>';
									print "</td>";
								}

								// Defaut
								print '<td class="center">';
								if ($conf->global->CFDI_FACTURE_ADDON == "$name") {
									print img_picto($langs->trans("Default"), 'on');
								} else {
									print '<a class="reposition" href="' . $_SERVER["PHP_SELF"] . '?action=setdoc&token=' . newToken() . '&value=' . urlencode($name) . '&scan_dir=' . urlencode($module->scandir) . '&label=' . urlencode($module->name) . '" alt="' . $langs->trans("Default") . '">' . img_picto($langs->trans("SetAsDefault"), 'off') . '</a>';
								}
								print '</td>';

								// Info
								$htmltooltip = '' . $langs->trans("Name") . ': ' . $module->name;
								$htmltooltip .= '<br>' . $langs->trans("Type") . ': ' . ($module->type ? $module->type : $langs->trans("Unknown"));
								if ($module->type == 'pdf') {
									$htmltooltip .= '<br>' . $langs->trans("Width") . '/' . $langs->trans("Height") . ': ' . $module->page_largeur . '/' . $module->page_hauteur;
								}
								$htmltooltip .= '<br>' . $langs->trans("Path") . ': ' . preg_replace('/^\//', '', $realpath) . '/' . $file;

								$htmltooltip .= '<br><br><u>' . $langs->trans("FeaturesSupported") . ':</u>';
								$htmltooltip .= '<br>' . $langs->trans("Logo") . ': ' . yn($module->option_logo, 1, 1);
								$htmltooltip .= '<br>' . $langs->trans("PaymentMode") . ': ' . yn($module->option_modereg, 1, 1);
								$htmltooltip .= '<br>' . $langs->trans("PaymentConditions") . ': ' . yn($module->option_condreg, 1, 1);
								$htmltooltip .= '<br>' . $langs->trans("Discounts") . ': ' . yn($module->option_escompte, 1, 1);
								$htmltooltip .= '<br>' . $langs->trans("CreditNote") . ': ' . yn($module->option_credit_note, 1, 1);
								$htmltooltip .= '<br>' . $langs->trans("MultiLanguage") . ': ' . yn($module->option_multilang, 1, 1);
								$htmltooltip .= '<br>' . $langs->trans("WatermarkOnDraftInvoices") . ': ' . yn($module->option_draft_watermark, 1, 1);


								print '<td class="center">';
								print $form->textwithpicto('', $htmltooltip, 1, 0);
								print '</td>';

								// Preview
								print '<td class="center">';
								if ($module->type == 'pdf') {
									print '<a href="' . $_SERVER["PHP_SELF"] . '?action=specimen&module=' . $name . '">' . img_object($langs->trans("Preview"), 'pdf') . '</a>';
								} else {
									print img_object($langs->trans("PreviewNotAvailable"), 'generic');
								}
								print '</td>';

								print "</tr>\n";
							}
						}
					}
				}
			}
		}
	}
}
print '</table>';
print '</div>';


print '</table>';
print "</div>\n";

// Page end
print dol_get_fiche_end();
llxFooter();
$db->close();
