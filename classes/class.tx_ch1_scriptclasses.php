<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Chi Hoang (info@chihoang.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once (PATH_t3lib."class.t3lib_scbase.php");

class tx_ch1_scriptclasses extends t3lib_SCbase {
	
	/**
	 *
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		parent::init();
	}

    	/**
	 * Appends ".location" to input string
	 *
	 * @param	string		Input string, probably a JavaScript document reference
	 * @return	string
	 */
	function frameLocation($str)	{
		return $str.'.location';
	}
    
    	/**
	 * Adding CM element for regular editing of the element!
	 *
	 * @param	string		Table name
	 * @param	integer		UID for the current record.
	 * @return	array		Item array, element in $menuItems
	 * @internal
	 */
	function DB_edit($table,$uid)
	{
		global $BE_USER;
			// If another module was specified, replace the default Page module with the new one
		$newPageModule = trim($BE_USER->getTSConfigVal('options.overridePageModule'));
		$pageModule = t3lib_BEfunc::isModuleSetInTBE_MODULES($newPageModule) ? $newPageModule : 'web_layout';

		$editOnClick='';
		$loc='top.content'.($this->listFrame && !$this->alwaysContentFrame ?'.list_frame':'');
		$addParam='';
		$theIcon = t3lib_iconWorks::skinImg($this->PH_backPath,'gfx/edit2.gif','width="11" height="12"');
		if (
				$this->iParts[0]=='pages' &&
				$this->iParts[1] &&
				$BE_USER->check('modules', $pageModule)
			)	{
			$theIcon = t3lib_iconWorks::skinImg($this->PH_backPath,'gfx/edit_page.gif','width="12" height="12"');
			$this->editPageIconSet=1;
			if ($BE_USER->uc['classicPageEditMode'] || !t3lib_extMgm::isLoaded('cms'))	{
				$addParam='&editRegularContentFromId='.intval($this->iParts[1]);
			} else {
				$editOnClick="top.fsMod.recentIds['web']=".intval($this->iParts[1]).";top.goToModule('".$pageModule."',1);";
			}
		}
		if (!$editOnClick)
		{
			//$editOnClick='if('.$loc.'){'.$loc.".document.location=top.TS.PATH_typo3+'alt_doc.php?returnUrl='+top.rawurlencode(".$this->frameLocation($loc.'.document').")+'&edit[".$table."][".$uid."]=edit".$addParam."';}";
			//$editOnClick='if('.$loc.'){'.$loc.".document.location=top.TS.PATH_typo3+'alt_doc.php?returnUrl='+top.rawurlencode(".($loc.'.document').")+'&edit[".$table."][".$uid."]=edit".$addParam."';}";
			//$editOnClick="document.location='../../../../typo3/alt_doc.php?returnUrl='+T3_THIS_LOCATION+'&edit[".$table."][".$uid."]=edit;".$addParam."'";
			$editOnClick="document.location=top.TS.PATH_typo3+'alt_doc.php?returnUrl='+T3_THIS_LOCATION+'&edit[".$table."][".$uid."]=edit'; return false;";
			//$editOnClick="document.location='../../../../typo3/alt_doc.php?edit[".$table."][".$uid."]=edit'; return false;";
		}

        	return $editOnClick;
        
		#return $this->linkItem(
		#	$this->label('edit'),
		#	$this->excludeIcon('<img'.$theIcon.' alt="" />'),
		#	$editOnClick.'return hideCM();'
		#);
	}
}

?>
