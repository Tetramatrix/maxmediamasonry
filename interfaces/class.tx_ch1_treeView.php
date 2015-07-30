<?php
/***************************************************************
*
*  (c) 2010-2012 Chi Hoang (info@chihoang.de)
*  All rights reserved
*
***************************************************************/
require_once(t3lib_extMgm::extPath ( 'xajax' ) . 'class.tx_xajax.php');
require_once(PATH_t3lib.'class.t3lib_foldertree.php');
require_once(t3lib_extMgm::extPath('ch_arbeitsbeispiele').'mod_main/class.tx_ch1_browseTree.php');

class tx_ch1_treeConfigObject extends tx_ch1_browseTree
{
	var $isTCEFormsSelectClass = true;
	var $supportMounts = true;
	var $usage;
	var $marker;
	
	function tx_ch1_treeConfigObject()
	{
		global $LANG, $BACK_PATH;

		$this->title = $LANG->sL('LLL:EXT:ch_arbeitsbeispiele/mod_main/locallang.php:object',1);
		$this->treeName = 'txchvaObject';
		$this->domIdPrefix = $this->treeName;
		$this->stdselection = 'tree=object';
		$this->mode = 'elbrowser';
		$this->usage = "navbrowser";
		$this->expandFirst = "1";
		
		$this->table='tx_charbeitsbeispiele_maxmedia';
		$this->parentField = 'parent_id';
		$this->typeField = $GLOBALS['TCA'][$this->table]['ctrl']['type'];

		$this->iconName = 'cat.gif';
		$this->iconPath = $BACK_PATH.PATH_txchva_rel.'res/';
		$this->rootIcon = $BACK_PATH.PATH_txchva_rel.'res/catfolder.gif';

		$this->fieldArray = Array ( 'uid', 'title' );
		if($this->parentField) $this->fieldArray[] = $this->parentField;
		if($this->typeField) $this->fieldArray[] = $this->typeField;
		$this->defaultList = 'uid,pid,tstamp';

		$this->clause = ' AND deleted=0 AND hidden=0';
		$this->orderByFields = 'title';

		$this->ext_IconMode = '0'; // no context menu on icons
	}
}

class tx_ch1_treeView
{
	var $useXajax = false;
	var $xajax;
	var $obj;
	var $pObj;

	/**
	 * initialize the browsable trees
	 * 
	 * @param	string		script name to link to
	 * @param	boolean		Element browser mode
	 * @return	void		
	 */

	function init ($pObj, $thisScript, $mode='browse', $tree="object", $marker="treeRoot")
	{
		global $BE_USER,$LANG,$BACK_PATH,$TYPO3_CONF_VARS;
		
		if ( ! $getParams = preg_replace ( '/\?.+/','', t3lib_div::_GP ('tree') ) )
		{
			$Ref = 'tx_ch1_treeConfig'.ucfirst($tree);		
		} else
		{
			$Ref = 'tx_ch1_treeConfig'.ucfirst($getParams);
		}

		if ( is_object($this->obj = t3lib_div::makeInstance ($Ref) ) )
		{
			$this->obj->thisScript = $thisScript;
			$this->obj->BE_USER = $BE_USER;
			$this->obj->mode = $mode;
			$this->obj->marker = $marker;
		
			if (!$this->obj->isPureSelectionClass)
			{
				if ($this->obj->isTreeViewClass)
				{							
					$this->obj->init ();
				} 

				if ( t3lib_extMgm::isLoaded ( 'xajax' ) && ! is_object ( $this->obj->xajax ) )
				{		
					$pObj->useXajax = $this->obj->useXajax = TRUE;
					
					if ($TYPO3_CONF_VARS ['BE'] ['forceCharset'])
					{
						define ( 'XAJAX_DEFAULT_CHAR_ENCODING', $TYPO3_CONF_VARS ['BE'] ['forceCharset'] );
						$pObj->xajax = t3lib_div::makeInstance ( 'tx_xajax' );							
						$pObj->xajax->cleanBufferOn();
						$pObj->xajax->decodeUTF8InputOn();
						$pObj->xajax->setCharEncoding('utf-8');
					} else
					{
						define ( 'XAJAX_DEFAULT_CHAR_ENCODING', 'iso-8859-15' );
						$pObj->xajax = t3lib_div::makeInstance ( 'tx_xajax' );							
						$pObj->xajax->cleanBufferOn();
						$pObj->xajax->setCharEncoding('iso-8859-15');
					}
				}
				
				$pObj->xajax->setWrapperPrefix ( $marker."_" );
				$pObj->xajax->registerFunction ( array ( 'sendResponse', &$this->obj, 'sendResponse' ) );
				
				if ( $_POST ["xajax"] == "sendResponse" )
				{
					list (,,$uid,$tree) = explode ( '_', $_POST [ "xajaxargs" ] [ "0" ] );
					if ( $tree == $this->obj->treeName )
					{
						$pObj->xajax->processRequests ( );	
					}
				}
			}
		}
	}

	/**
	 * rendering the browsable trees
	 * 
	 * @return	string		tree HTML content
	 */
	function getTrees( $uid = 0, $highlight = 0 )
	{
		global $LANG, $BACK_PATH;
		
		$tree = "";
		
		$res = $GLOBALS [ 'TYPO3_DB' ] -> sql_query (
			'SELECT V.pid, U.uid, U.title title 
			 FROM 
			tx_charbeitsbeispiele_maxmedia V
			LEFT JOIN pages U ON U.uid=V.pid
			 WHERE V.deleted=0 AND V.hidden=0 
			GROUP BY V.pid'
		);
	
		while ( $row = $GLOBALS [ 'TYPO3_DB' ]->sql_fetch_assoc ( $res ) )
		{
			$this->obj->title = $row [ "title" ];
			$this->obj->clause = ' AND deleted=0 AND hidden=0 AND pid=' . $row [ "pid" ];
			$this->obj->treeid = $row [ "pid" ];
			$tree .= "<div style=\"padding-left:10px;padding-top:10px;\">".
						$this->obj->bootstraptree( $uid, s)."</div>";
		}	
		return $tree;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ch_1/interfaces/class.tx_ch1_treeView.php'])
{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ch_1/interfaces/class.tx_ch1_treeView.php']);
}

?>