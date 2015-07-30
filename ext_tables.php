<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_extMgm::addStaticFile($_EXTKEY,'static','json');

$TCA['tx_charbeitsbeispiele_maxmedia'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:ch_arbeitsbeispiele/locallang_db.xml:tx_charbeitsbeispiele_maxmedia',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'delete' => 'deleted',
		"default_sortby" => "ORDER BY sorting ASC",
		'treeParentField' => 'parent_id',
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_charbeitsbeispiele_maxmedia.gif',
	),
);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . '_pi1'] = 'pi_flexform';

t3lib_extMgm::addPiFlexFormValue (
	$_EXTKEY . '_pi1',
	'FILE:EXT:ch_arbeitsbeispiele/flexform_ds_pi1.xml'
);

t3lib_extMgm::addPlugin(array(
	'LLL:EXT:ch_arbeitsbeispiele/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');


if (TYPO3_MODE == 'BE')
{
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_charbeitsbeispiele_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_charbeitsbeispiele_pi1_wizicon.php';
	
		// add module after 'Tools'
	if (!isset($TBE_MODULES['txcharbeitsbeispieleM1']))
	{
		$temp_TBE_MODULES = array();
		foreach($TBE_MODULES as $key => $val)
		{
			if ($key=='web')
			{
				$temp_TBE_MODULES[$key] = $val;
				$temp_TBE_MODULES['txcharbeitsbeispieleM1'] = $val;
			} else
			{
				$temp_TBE_MODULES[$key] = $val;
			}
		}
		$TBE_MODULES = $temp_TBE_MODULES;
	}

	      	// add main module
	t3lib_extMgm::addModule('txcharbeitsbeispieleM1','','',t3lib_extMgm::extPath($_EXTKEY)."mod_main/");
    
		// add object module
	t3lib_extMgm::addModule('txcharbeitsbeispieleM1','object','',t3lib_extMgm::extPath($_EXTKEY).'mod_object/');
}
?>