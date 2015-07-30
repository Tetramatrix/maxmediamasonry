<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Chi Hoang <info@chihoang.de>
*  All rights reserved
*
***************************************************************/
require_once ( t3lib_extMgm::extPath ( 'ch_arbeitsbeispiele' ) . 'classes/class.tx_ch1_tested.php');

/**
 * Plugin 'ch_arbeitsbeispiele' for the 'ch_arbeitsbeispiele' extension.
 *
 * @author	Chi Hoang <info@chihoang.de>
 * @package	TYPO3
 * @subpackage	tx_charbeitsbeispiele
 */
class tx_charbeitsbeispiele_pi1 extends tx_ch1_tested
{
	var $prefixId      = 'tx_charbeitsbeispiele_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_charbeitsbeispiele_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'ch_arbeitsbeispiele';	// The extension key.
	
	// Meine Variablen
	var $lConf;
		
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf)
	{	
		session_start();
		$_SESSION [ "bins" ] = array ();
		
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
	
			// Conf
		$this->confArray = unserialize ( $GLOBALS [ 'TYPO3_CONF_VARS' ][ 'EXT' ][ 'extConf' ] [ $this->extKey ] );	
		$this->pi_initPIflexForm ( );			// Init and get the flexform data of the plugin

			// Assign the flexform data to a local variable for easier access
		$piFlexForm = $this->cObj->data [ 'pi_flexform' ];
		$index = $this->lang [ $GLOBALS [ 'TSFE' ]->sys_language_uid ] == null ? 0 : $this->lang [ $GLOBALS [ 'TSFE' ]->sys_language_uid ];
		$sDef = current ( $piFlexForm [ 'data' ] );
		$lDef = array_keys ( $sDef );
        
		foreach ( $piFlexForm [ 'data' ] as $sheet => $data )
		{
			foreach ( $data [ $lDef [ $index ] ] as $key => $val )
			{
				$this->lConf [ $key ] = $this->pi_getFFvalue ( $piFlexForm, $key, $sheet,
									      $lDef[$index] );
			}
		}

		$_SESSION [ "sysfolder" ] = $this->lConf [ "sysfolder" ];
		
		$js .= $this->getCSSInclude ( t3lib_extMgm::siteRelPath ( 'ch_arbeitsbeispiele' ) . 'res/',
					     'system.css' );
		$js .= $this->getJavascriptInclude ( t3lib_extMgm::siteRelPath ( 'ch_arbeitsbeispiele' ) .
						    'res/', 'jquery-1.7.2.min.js' );
		$js .= $this->getJavascriptInclude ( t3lib_extMgm::siteRelPath ( 'ch_arbeitsbeispiele' ) .
						    'res/', 'jquery-ui-1.8.22.custom.min.js' );
		$js .= $this->getJavascriptInclude ( t3lib_extMgm::siteRelPath ( 'ch_arbeitsbeispiele' ) .
						    'res/', 'jquery.masonry.min.js' );
		$js .= $this->getJavascriptInclude ( t3lib_extMgm::siteRelPath ( 'ch_arbeitsbeispiele' ) .
						    'res/', 'modernizr.custom.94949.js' );
		$js .= $this->getJavascriptInclude ( t3lib_extMgm::siteRelPath ( 'ch_arbeitsbeispiele' ) .
						    'res/', 'jquery.tmpl.min.js' );
		$js .= $this->getJavascriptInclude ( t3lib_extMgm::siteRelPath ( 'ch_arbeitsbeispiele' ) .
						     'res/', 'jquery.imagesloaded.js' );
		$js .= $this->getJavascriptInclude ( t3lib_extMgm::siteRelPath ( 'ch_arbeitsbeispiele' ) .
						    'res/', 'masonry.js' );
		
		$GLOBALS [ 'TSFE' ]->additionalHeaderData [ $this->prefixId ] = $js;

			// Get the template
 		$this->templateCode = $this->cObj->fileResource ( $this->confArray [ 'uploadPath' ] . '/' .
								$this->lConf [ 'template_file' ] );
	
		$this->template [ 'beispiel' ] = $this->cObj->getSubpart ( $this->templateCode, '###BEISPIELE###' );
		
		$this->template [ 'menu' ] = $this->cObj->getSubpart ( $this->templateCode, '###MENU###' );
		$this->template [ 'menu_item' ] = $this->cObj->getSubpart ( $this->template [ 'menu' ], '###ITEM_NO###' );
		
		$res = $GLOBALS [ 'TYPO3_DB' ]->exec_SELECTquery (
			'V.title VT, V.uid UID',
			'tx_charbeitsbeispiele_maxmedia V',
			'pid='.$_SESSION [ "sysfolder" ].' AND parent_id=0 AND V.deleted=0 AND V.hidden=0',
			'',
			'sorting ASC'
		);

		while ( $record = $GLOBALS [ 'TYPO3_DB' ]->sql_fetch_assoc ( $res ) )
		{
			$m [ "###TITLE###" ] = $record [ "VT" ];
			$m [ "###URL###" ] = $this->pi_getPageLink ( $GLOBALS [ 'TSFE' ]->id, '',
				array ( "UID" => $record [ "UID" ],
				        "eID" => "ch_arbeitsbeispiele"
				       )
				);
			$t [ ] = $this->cObj->substituteMarkerArray ( $this->template [ 'menu_item' ], $m );
		}

		unset ( $s );
		$s ["###ITEM_NO###"] = is_array ( $t ) ? implode ( '', $t ) : 'Keine Kategorie eingegeben. Bitte Browser erneut starten.';

		unset ( $m );
		$m ["###NAVIGATION###"] = $this->cObj->substituteMarkerArrayCached ( $this->template [ "menu" ], array(), $s );
		$m ["###FORM###"] = $this->pi_getPageLink ( $GLOBALS [ 'TSFE' ]->id, '', array () );
		
		$content = $this->cObj->substituteMarkerArray ( $this->template [ 'beispiel' ], $m );
	
		return $this->pi_wrapInBaseClass($content);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ch_arbeitsbeispiele/pi1/class.tx_charbeitsbeispiele_pi1.php'])
{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ch_arbeitsbeispiele/pi1/class.tx_charbeitsbeispiele_pi1.php']);
}

?>