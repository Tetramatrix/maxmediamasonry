<?php
/***************************************************************
*
*  (c) 2005-2012 Chi Hoang (info@chihoang.de)
*  All rights reserved
*
***************************************************************/
require_once(t3lib_extMgm::extPath('ch_arbeitsbeispiele').'interfaces/class.tx_ch1_treeView.php');

class tx_ch1_beform
{
	var $doc;
	var $content;
	var $hiddenMenu;

		// Internal, static: _GP
	var $currentSubScript;
	var $mainModule ='';
	var $xajax;
	var $defaultMod;

		// Constructor:
	function init($defaultMod = "")
	{
		global $MCONF,$AB,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$HTTP_GET_VARS,$HTTP_POST_VARS,$CLIENT,$TYPO3_CONF_VARS;

		$this->defaultMod = $defaultMod; 
		$this->currentSubScript = t3lib_div::_GP('currentSubScript');

			// Setting highlight mode:
		$this->doHighlight = !$BE_USER->getTSConfigVal('options.pageTree.disableTitleHighlight');

		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->backPath = $BACK_PATH;
		list ($js,$this->doc->bodyTagAdditions,$this->hiddenMenu) = $this->doc->getContextMenuCode();

			// clear JS
		$this->doc->JScode='';

			// Setting JavaScript for menu.
		$this->doc->JScode=$js.$this->doc->wrapScriptTags(
			($this->currentSubScript?'top.currentSubScript=unescape("'.rawurlencode($this->currentSubScript).'");':'').'

			function jumpTo(params,linkObj,highLightID)
			{
				var theUrl = top.TS.PATH_typo3+top.currentSubScript+"?"+params;

				if (top.condensedMode)
				{
					top.content.document.location=theUrl;
				} else
				{
					parent.list_frame.document.location=theUrl;
				}
				'.($this->doHighlight?'hilight_row("row"+top.fsMod.recentIds["'.$this->mainModule.'"],highLightID);':'').'
				'.(!$GLOBALS['CLIENT']['FORMSTYLE'] ? '' : 'if (linkObj) {linkObj.blur();}').'
				return false;
			}
				// Call this function, refresh_nav(), from another script in the backend if you want to refresh the navigation frame (eg. after having changed a page title or moved pages etc.)
				// See t3lib_BEfunc::getSetUpdateSignal()
			function refresh_nav()
			{
				window.setTimeout("_refresh_nav();",0);
			}

				/**
				* [Describe function...]
				* 
				* @return	[t]		...
				*/
			function _refresh_nav()
			{
				document.location="'.htmlspecialchars(t3lib_div::getIndpEnv('SCRIPT_NAME').'?unique='.time()).'";
			}

				// Highlighting rows in the page tree:
			var hilight_old;
			function hilight_row(frameSetModule,highLightID)
			{	//
					// Remove old:
				theObj = document.getElementById(top.fsMod.navFrameHighlightedID["navframe"]);
				if (theObj)
				{
					//theObj.style.backgroundColor=hilight_old;
				}

					// Set new:
				top.fsMod.navFrameHighlightedID["navframe"] = highLightID;
				theObj = document.getElementById(highLightID);
				if (theObj)
				{
					hilight_old = theObj.style.backgroundColor;
					theObj.style.backgroundColor="#d0e4c9";
				}
			}
		');
		
		if ( t3lib_extMgm::isLoaded ( 'xajax' ) )
		{
				// the trees
			$this->view = t3lib_div::makeInstance('tx_ch1_treeView');
			$this->view->init ( $this, t3lib_div::getIndpEnv ( 'SCRIPT_NAME' ), 'elbrowser', $this->defaultMod );
			$this->doc->JScode .= $this->xajax->getJavascript ( "../../../../" . t3lib_extMgm::siteRelPath ( 'xajax' ) );
		}
		

			// should be float but gives bad results
		$this->doc->inDocStyles .= '
			.txdam-editbar, .txdam-editbar > a >img
			{
				background-color:'.t3lib_div::modifyHTMLcolor($this->doc->bgColor,-15,-15,-15).';
			}
			';
	}

	/**
	 * Main function, rendering the browsable page tree
	 * 
	 * @return	void		
	 */
	function main()
	{
		global $TYPO3_CONF_VARS, $LANG, $BACK_PATH;
		
		$this->content = $this->doc->startPage('Navigation');
		$this->content.= $this->hiddenMenu;
		$this->content.= $this->view->getTrees();

			// Adding highlight - JavaScript
		if ($this->doHighlight)	$this->content .=$this->doc->wrapScriptTags('
			hilight_row("",top.fsMod.navFrameHighlightedID["web"]);
		');
	}

	/**
	 * Outputting the accumulated content to screen
	 * 
	 * @return	void		
	 */
	function printContent()
	{
		$this->content.= $this->doc->endPage();
		echo $this->content;
	}

}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ch_arbeitsbeispiele/mod_main/class.tx_ch1_beform.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ch_arbeitsbeispiele/mod_main/class.tx_ch1_beform.php']);
}
?>