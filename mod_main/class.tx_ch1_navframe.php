<?php
/***************************************************************
*
*  (c) 2010-2011 Chi Hoang (info@chihoang.de)
*  All rights reserved

***************************************************************/

unset($MCONF);
include ('conf.php');
include ($BACK_PATH.'init.php');
include ($BACK_PATH.'template.php');

define('PATH_txcharbeitsbeispiele', t3lib_extMgm::extPath('ch_arbeitsbeispiele'));
require_once(PATH_txcharbeitsbeispiele.'mod_main/class.tx_ch1_beform.php');

class tx_ch1_navframe extends tx_ch1_beform {

		// Constructor:
	function init()
	{
		global $MCONF;
		list($this->mainModule) = explode('_', $MCONF['name']); 
		parent::init();
	}
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ch_arbeitsbeispiele/mod_main/class.tx_ch1_navframe.php'])
{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ch_arbeitsbeispiele/mod_main/class.tx_ch1_navframe.php']);
}

// Make instance:

$SOBE = t3lib_div::makeInstance('tx_ch1_navframe');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();

?>
