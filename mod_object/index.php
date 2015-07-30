<?php
/***************************************************************
*
*  (c) 2005-2011 Chi Hoang (info@chihoang.de)
*  All rights reserved
*
***************************************************************/

	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require ("conf.php");
require ($BACK_PATH."init.php");
require ($BACK_PATH."template.php");
$LANG->includeLLFile("EXT:ch_arbeitsbeispiele/mod_object/locallang.php");
require_once (t3lib_extMgm::extPath ( 'ch_arbeitsbeispiele' ) . 'classes/class.tx_ch1_scriptclasses.php');

$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

class tx_ch1_object extends tx_ch1_scriptclasses
{	
		// lookup tables
	var $lookup = array ();
	var $prefix = array ();

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 */
	function menuConfig()
	{
		global $LANG;
        
		$this->MOD_MENU = Array (
			"functionx" => Array (
				"1" => $LANG->getLL("functionx0"),
			)
		);
		parent::menuConfig();
	}

		// If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	/**
	 * Main function of the module. Write the content to $this->content
	 */
	function main()
	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

			// Access check!
			// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user["admin"] && !$this->id))
		{
				// Draw the header.
			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form = '<form name="ch_arbeitsbeispiele" action="index.php" method="post" enctype="'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['form_enctype'].'">
						<input type="hidden" name="id" value="'.$this->id.'" />';
            
			//$this->doc->JScode .= $this->doc->getDynTabMenuJScode();

    				// JavaScript
			$this->doc->JScodeArray['redirectUrls'] = $this->doc->redirectUrls(t3lib_extMgm::extRelPath('ch_arbeitsbeispiele').'mod_object/index.php'.$addParams);
            
				/*
			$this->doc->JScodeArray['jumpExt'] = '
				function jumpExt(URL,anchor)	{
					var anc = anchor?anchor:"";
					document.location = URL+(T3_THIS_LOCATION?"&returnUrl="+T3_THIS_LOCATION:"")+anc;
				}
			';
			*/

			$this->doc->JScodeArray['jumpExt'] = '
				function jumpToUrl(URL)	{
						document.location = URL;
					}';

			$this->doc->JScodeArray['highlight'] = '
					/*
					Highlight Link script
					By JavaScript Kit (http://javascriptkit.com)
					Over 400+ free scripts here!
					Above notice MUST stay entact for use
					*/

					function highlight(which,color){
						if (document.all||document.getElementById)
							which.style.backgroundColor=color
						}
				 	';
							
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
				</script>
			';
			
			$this->content.=$this->doc->startPage("arbeitsbeispiele: XML");
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			//$this->content.=$this->doc->header($LANG->getLL("object").$title);
			$this->content.=$this->doc->divider(5);
            
				// Render content:
			$this->moduleContent();

				// ShortCut
			if ($BE_USER->mayMakeShortcut())
			{
				$this->content.=$this->doc->spacer(20).$this->doc->section("",$this->doc->makeShortcutIcon("id",implode(",",array_keys($this->MOD_MENU)),$this->MCONF["name"]));
			}

			$this->content.=$this->doc->spacer(10);
		
        } else
	{			
                // If no access or if ID == zero
			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;

			$this->content.=$this->doc->startPage();
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 */
	function printContent()
	{
		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 */
	function moduleContent()
	{
		global $LANG, $TYPO3_CONF_VARS,$BACK_PATH;
		
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ch_arbeitsbeispiele/mod_object/index.php'])
{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ch_arbeitsbeispiele/mod_object/index.php']);
}

	// Make instance:
$SOBE = t3lib_div::makeInstance('tx_ch1_object');
$SOBE->init();

	// Include files?
foreach($SOBE->include_once as $INC_FILE) include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>