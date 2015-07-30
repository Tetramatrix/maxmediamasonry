<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_charbeitsbeispiele_maxmedia=1
');

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_charbeitsbeispiele_pi1.php', '_pi1', 'list_type', 0);

$TYPO3_CONF_VARS['FE']['eID_include']['ch_arbeitsbeispiele'] = 'EXT:ch_arbeitsbeispiele/classes/ajax.php';
$TYPO3_CONF_VARS['FE']['eID_include']['ch_arbeitsbeispiele_singleview'] = 'EXT:ch_arbeitsbeispiele/classes/singleview.php';
?>