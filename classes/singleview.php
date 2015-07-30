<?php
/***************************************************************
*
*  (c) 2010-2012 Chi Hoang (info@chihoang.de)
*  All rights reserved
*  
***************************************************************/
require_once ( PATH_site.'typo3/sysext/cms/tslib/class.tslib_pibase.php' );
//require_once ( PATH_site.'typo3/sysext/cms/tslib/class.tslib_content.php');
require_once (PATH_site.'typo3/sysext/cms/tslib/class.tslib_fe.php');
require_once (PATH_site.'t3lib/class.t3lib_userauth.php');
require_once (PATH_site.'typo3/sysext/cms/tslib/class.tslib_feuserauth.php');
require_once (PATH_site.'t3lib/class.t3lib_cs.php');
require_once (PATH_site.'typo3/sysext/cms/tslib/class.tslib_content.php');
require_once (PATH_site.'t3lib/class.t3lib_tstemplate.php');
require_once (PATH_site.'t3lib/class.t3lib_page.php');
require_once (PATH_site.'t3lib/class.t3lib_timetrack.php');
require_once ( "JSON.php" );
 
class unsereKlasse extends tslib_pibase
{
	function &createCObj($pid = 1)
	{
		// Create the TSFE class.
		$GLOBALS['TSFE'] = t3lib_div::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], $pid, '0', 0, '','','','');

		$GLOBALS['TT'] = t3lib_div::makeInstance('t3lib_timeTrack');
		$GLOBALS['TT']->start();
		$GLOBALS['TSFE']->config['config']['language'] = $_GET['L'];

		// Fire all the required function to get the typo3 FE all set up.
		$GLOBALS['TSFE']->id = $pid;
		$GLOBALS['TSFE']->connectToDB();

		// Prevent mysql debug messages from messing up the output
		$sqlDebug = $GLOBALS['TYPO3_DB']->debugOutput;
		$GLOBALS['TYPO3_DB']->debugOutput = false;
		$GLOBALS['TSFE']->initLLVars();
		$GLOBALS['TSFE']->initFEuser();

		// Look up the page
		$GLOBALS['TSFE']->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$GLOBALS['TSFE']->sys_page->init($GLOBALS['TSFE']->showHiddenPage);

		// If the page is not found (if the page is a sysfolder, etc), then return no URL, preventing any further processing which would result in an error page.
		$page = $GLOBALS['TSFE']->sys_page->getPage($pid);

		// $GLOBALS['TSFE']->page = $page;
		if (count($page) == 0)
		{
			$GLOBALS['TYPO3_DB']->debugOutput = $sqlDebug;
			return false;
		}
		
		// If the page is a shortcut, look up the page to which the shortcut references, and do the same check as above.
		if ($page['doktype'] == 4 && count($GLOBALS['TSFE']->getPageShortcut($page['shortcut'], $page['shortcut_mode'], $page['uid'])) == 0)
		{
			$GLOBALS['TYPO3_DB']->debugOutput = $sqlDebug;
			return false;
		}
		
		// Spacer pages and sysfolders result in a page not found page too…
		if ($page['doktype'] == 199 || $page['doktype'] == 254)
		{
			$GLOBALS['TYPO3_DB']->debugOutput = $sqlDebug;
			return false;
		}
		
		$GLOBALS['TSFE']->getPageAndRootline();
		$GLOBALS['TSFE']->initTemplate();
		$GLOBALS['TSFE']->forceTemplateParsing = 1;
		
		// Find the root template
		$GLOBALS['TSFE']->tmpl->start($GLOBALS['TSFE']->rootLine);
		
		// Fill the pSetup from the same variables from the same location as where tslib_fe->getConfigArray will get them, so they can be checked before this function is called
		$GLOBALS['TSFE']->sPre = $GLOBALS['TSFE']->tmpl->setup['types.'][$GLOBALS['TSFE']->type]; // toplevel - objArrayName
		$GLOBALS['TSFE']->pSetup = $GLOBALS['TSFE']->tmpl->setup[$GLOBALS['TSFE']->sPre.'.'];
		
		// If there is no root template found, there is no point in continuing which would result in a 'template not found' page and then call exit php. Then there would be no clickmenu at all.
		// And the same applies if pSetup is empty, which would result in a \\\"The page is not configured\\\" message.
		if (!$GLOBALS['TSFE']->tmpl->loaded || ($GLOBALS['TSFE']->tmpl->loaded && !$GLOBALS['TSFE']->pSetup))
		{
			$GLOBALS['TYPO3_DB']->debugOutput = $sqlDebug;
			return false;
		}
		
		$GLOBALS['TSFE']->getConfigArray();
		$GLOBALS['TSFE']->getCompressedTCarray();
		$GLOBALS['TSFE']->inituserGroups();
		$GLOBALS['TSFE']->connectToDB();
		$GLOBALS['TSFE']->determineId();
		$GLOBALS['TSFE']->newCObj();
		return $GLOBALS['TSFE']->cObj;
	}

	function replacelinks ( $html )
	{
		/*** a new dom object ***/ 
		$dom = new domDocument; 
	
		/*** load the html into the object ***/ 
		$dom->loadHTML( $html ); 
	
		/*** discard white space ***/ 
		$dom->preserveWhiteSpace = false;
	
		 /*** get the links from the HTML ***/
		$links = $dom->getElementsByTagName('a');
	    
		$host = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : 'localhost/';
		$php_self = str_replace('index.php','', $_SERVER['PHP_SELF']);
	
		/*** loop over the links ***/
		foreach ($links as $tag)
		{
			$href = $tag->getAttribute("href");
			
			if ( strpos ( $href, ".html" ) )
			{
				$res = $GLOBALS [ 'TYPO3_DB' ]->exec_SELECTquery (
					'*',
					'link_cache V',
					'V.url="'. str_replace( ".html", "", $href ) . '"',
					'',
					''
				);
	
				if ( $row = $GLOBALS [ 'TYPO3_DB' ]->sql_fetch_assoc ( $res ) )
				{
					$params = unserialize ( $row [ "params" ] );
					
					$tag->setAttribute ( "href", "javascript:void();" );
					$tag->setAttribute ( "target", "_self" );
					$tag->setAttribute ( "onClick", "maxmedia.singleview('".$host.$php_self."','" . $params [ "id" ] ."');return false;" );
				}
			} else if ( preg_match ( "/\?id=(\d{1,4})/", $href, $params ) )
 			{
				$tag->setAttribute ( "href", "javascript:void();" );
				$tag->setAttribute ( "target", "_self" );
				$tag->setAttribute ( "onClick", "maxmedia.singleview('".$host.$php_self."','" . $params [ 1 ] ."');return false;" );
			}
		}	
		return $dom->saveHTML();
	}

	function main()
	{
		$cObj = $this->createCObj( $_GET [ "id" ] );
	
		$res = $GLOBALS [ 'TYPO3_DB' ]->exec_SELECTquery (
			'*',
			'tt_content V',
			'V.pid='. $_GET [ "id" ] . ' AND V.deleted=0 AND V.hidden=0',
			'',
			''
		);
	
		while ( $r = $GLOBALS [ 'TYPO3_DB' ]->sql_fetch_assoc ( $res ) )
		{
			$ce [] = $r;	
		}
		
		foreach ( $ce as $k => $v )
		{	
			switch ( $v [ "CType" ] )
			{
				case "menu" :
				{
					$menuconf ["special"] = "directory";
					$menuconf ["special."] [ "value" ] = $v [ "pages"];
					$menuconf [ "1" ] = "TMENU";
					$menuconf [ "1."] [ "NO" ]  = "1";
					$menuconf [ "1."] [ "NO." ][ "ATagTitle." ] [ "field" ] = "uid";
					$menuconf [ "1."] [ "NO." ][ "wrap" ] = "<ul>|</ul>";
					$menuconf [ "1."] [ "NO." ][ "allWrap" ] = "<li>|</li>";
					$menuconf [ "1."] [ "target" ] = "_blank";
 					$content .= $this->replacelinks ( utf8_decode ( $cObj->HMENU($menuconf) ) );
				}
				break;
			
				case "textpic" :
				{
					$imageTextConfiguration['text.']['10'] = 'TEXT';
					$imageTextConfiguration['text.']['10.']['value'] = $v [ "bodytext"];
					$imageTextConfiguration['textPos'] = 18;
					$imageTextConfiguration['imgList'] = $v [ "image" ];
					$imageTextConfiguration["imgPath"] = "uploads/pics/";
					$content .= $this->replacelinks( utf8_decode ( $cObj->IMGTEXT($imageTextConfiguration) ) );
				}
				break;
			
				default :
				{
					$conf = array (
							'tables' => 'tt_content',
							'source' => $v [ "uid" ],
							'dontCheckPid' => 1,
							"conf." => array (
									"tt_content" => "TEXT" ,
									"tt_content." => array (
												"field" => 'bodytext'
											),
								)
							);

					$content .= $this->replacelinks( utf8_decode ( $cObj->RECORDS($conf) ) );
				}
			}
		}
		
		echo json_encode ( array ( "bodytext" =>  $content  ) );
	}	
}

header('content-type: text/html; charset=utf-8');
header("Expires: Sat, 1 Jan 2005 00:00:00 GMT");
header("Last-Modified: ".gmdate( "D, d M Y H:i:s")."GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
	
$output = t3lib_div::makeInstance('unsereKlasse');
$output->main();
?>