<?php
/***************************************************************
*
*  (c) 2005-2012 Chi Hoang (info@chihoang.de)
*  All rights reserved
*
***************************************************************/
require_once(PATH_t3lib.'class.t3lib_treeview.php');

class tx_ch1_browseTree extends t3lib_treeView
{
	var $useXajax = false;
	var $xajax;
	
		// is able to generate a browasable tree
	var $isTreeViewClass = TRUE;

		// is able to generate a tree for a select field in TCEForms
	var $isTCEFormsSelectClass = false;
	var $tceformsSelect_prefixTreeName = false;

		// is able to handle mount points
	var $supportMounts = false;

	/**
	 * element browser mode
	 */
	var $mode = 'browse';

	/**
	 * enables selection icons: + = -
	 */	
	var $clickMenuScript=true;

	/**
	 * indicates if we need to output a root icon
	 */	
	var $rootIconIsSet = false;
	var $tree;


	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$cmd: ...
	 * @return	[type]		...
	 */
	 function sendResponse ( $cmd )
	 {
		if ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['ch_arbeitsbeispiele'])
		{
			$this->confArr = unserialize ( $GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['ch_arbeitsbeispiele'] );
		}
	
		$objResponse = new tx_xajax_response ( );

		t3lib_div::_GETset ( $cmd, 'PM' );
		
		list (,,$uid,$tree) = explode ( '_', $cmd );
		
		$response = "";
		
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
			$this->title = $row [ "title" ];
			$this->clause = ' AND deleted=0 AND hidden=0 AND pid=' . $row [ "pid" ];
			$this->treeid = $row [ "pid" ];
			$objResponse->addAssign ( $this->marker . $row [ "pid" ],
									 'innerHTML', $this->ajaxtree ($uid) );
		}	
		
		return $objResponse->getXML ( );
	}
	
	function bootstraptree ( $uid, $highlight )
	{
			// put a table around it with IDs to access the rows from JS
			// not a problem if you don't need it
			// In XHTML there is no "name" attribute of <td> elements - but Mozilla will not be able to highlight rows if the name attribute is NOT there.
		$out = '<ul class="tree" id="treeRoot'.$this->treeid.'" style="padding:0px;margin:0px">';
		$arr =  $this->getBrowsableTree ( $uid );
		$out .= $this->printTree( $arr, $uid, $highlight );
		$out .= '</ul>';
		return $out;
	}
	
	function ajaxtree ( $uid )
	{
		$arr =  $this->getBrowsableTree ( $uid );
		return $this->printTree( $arr, $uid );	
	}
	
	/**
	 * [Describe function...]
	 * 
	 * @param	[t]		$row: ...
	 * @param	[t]		$command: ...
	 * @return	[t]		...
	 */
	function getJumpToParam($row, $command='SELECT')
	{
		return '&SLCMD['.$command.']['.$this->treeName.']['.rawurlencode($row['uid']).']=1';
	}
	
	/**
	 * [Describe function...]
	 * 
	 * @param	[t]		$rec: ...
	 * @return	[t]		...
	 */
	function getRootIcon($row)
	{
		global $BACK_PATH;

		if ($this->rootIcon)
		{
			$icon = $this->wrapIcon('<img src="'.$this->rootIcon.
					'" width="18" height="16" align="top" alt="" />',$row);
		} else
		{
			$icon = parent::getRootIcon($row);
		}
		$this->rootIconIsSet = true;
		return $icon;
	}
	
	/**
	 * Get icon for the row.
	 * If $this->iconPath and $this->iconName is set, try to get icon based on those values.
	 *
	 * @param	array		Item row.
	 * @return	string		Image tag.
	 */
	function getIcon($row)
	{
		if ($this->iconPath && $this->iconName)
		{
			$icon = '<img' . t3lib_iconWorks::skinImg('', $this->iconPath .
						( $row ["doktype"] == "254" ? $this->iconSys : $this->iconName ),
						( $row ["doktype"] == "254" ? '': 'style="padding-right:2px;"' ) ) .
						' alt=""' .
						($this->showDefaultTitleAttribute ? ' title="UID: ' .
						 $row['uid'] . '"' : '') . ' />';
			
		} else
		{
			$icon = t3lib_iconWorks::getSpriteIconForRecord($this->table,
							$row,
							array( 'title' => ($this->showDefaultTitleAttribute ? 'UID: ' .
									$row['uid'] : $this->getTitleAttrib($row)),
								'class' => 'c-recIcon'
							));
		}
		return $this->wrapIcon ( $icon, $row );
	}
	
	
	function wrapClickMenuOnIcon($str,$table,$uid='',$listFr=1,$addParams='',$enDisItems='', $returnOnClick=FALSE)
	{
		$backPath = rawurlencode($this->backPath).'|'.t3lib_div::shortMD5($this->backPath.'|'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']);
		$onClick = 'if(event.stopPropagation){event.stopPropagation;}event.cancelBubble=true;showClickmenu("'.$table.
		'","'.$uid.'","'.$listFr.'","'.
		str_replace('+','%2B',$enDisItems).'","'.
		str_replace('&','&amp;',addcslashes($backPath,'"')).'","'.
		str_replace('&','&amp;',addcslashes($addParams,'"')).'");return false;';
		return $returnOnClick ? $onClick : '<a href="javascript:void(0);" onclick="'.
							htmlspecialchars($onClick).'" oncontextmenu="'.
							htmlspecialchars($onClick).'">'.$str.'</a>';
	}
    
	/**
	 * Wrapping the image tag, $icon, for the row, $row (except for mount points)
	 *
	 * @param	string		The image tag for the icon
	 * @param	array		The row for the current element
	 * @return	string		The processed icon input value.
	 * @access private
	 */
	function wrapIcon($icon,$row)
	{
			// Add title attribute to input icon tag
		$theIcon = $this->addTagAttributes($icon,($this->titleAttrib ? $this->titleAttrib.'="'.$this->getTitleAttrib($row).'"' : ''));

			// Wrap icon in click-menu link.
		if (!$this->ext_IconMode)
		{	
			$theIcon = $this->wrapClickMenuOnIcon($theIcon,
				($row['table'] ? $row['table'] : $this->table),$this->getId($row),1,'','+new,edit,delete');

		} elseif (!strcmp($this->ext_IconMode,'titlelink'))
		{	
				// unused for now
			$aOnClick = 'return jumpTo(\''.$this->getJumpToParam($row).'\',this,\''.$this->domIdPrefix.$this->getId($row).'_'.$this->bank.'\');';
			$theIcon='<a href="javascript:void(0);" onclick="'.htmlspecialchars($aOnClick).'return false;">'.$theIcon.'</a>';
		}
		return $theIcon;
	}
	

	/**
	 * @param	[t]		$title: ...
	 * @param	[t]		$row: ...
	 * @return	[t]		...
	 */
	function wrapTitle ( $title, $row )
	{
		global $SOBE;
		
		$aOnClick = 'hilight_row(\''.$row['uid'].'\',\''.$this->domIdPrefix.$this->getId($row).'_'.$this->bank.'\');';
		
		if ( $this->usage == "htmlform" )
		{
			return ' onclick="'.htmlspecialchars($aOnClick).'return false;"';	
		}
		
		//$aOnClick = 'jumpTo(\''.$this->getJumpToParam($row).'\',this,\''. $this->domIdPrefix.$this->getId($row).'\');'.$aOnClick;
		
		if ($row['uid'] && !$row['editOnClick'] )
		{
			return ' onclick="'.htmlspecialchars($aOnClick).'return false;"';	
		}
		$aOnClick = 'top.content.list_frame.location.href=top.TS.PATH_typo3+\'alt_doc.php?returnUrl=\'+top.rawurlencode(top.content.list_frame.document.location)+\'&edit['.
		$row['table'].']['.$this->getId($row).']=edit\';'.$aOnClick;
			
		if($row['editOnClick'])
		{
			return ' onclick="'.htmlspecialchars($aOnClick).'return false;"';	
		} else
		{
			return $title;
		}
	}
	
	/**
	 * Wrap the plus/minus icon in a link
	 *
	 * @param	string		HTML string to wrap, probably an image tag.
	 * @param	string		Command for 'PM' get var
	 * @param	boolean		If set, the link will have a anchor point (=$bMark) and a name attribute (=$bMark)
	 * @return	string		Link-wrapped input string
	 * @access private
	 */
	function PM_ATagWrap($icon, $cmd, $bMark = '')
	{	
		if ($this->useXajax)
		{
			list(,$expand) = explode ( '_', $cmd );
			if ($expand == '1')
			{
				$title = 'Expand';
			} else
			{
				$title = 'Collapse';
			}
			return '<span onclick="if(event.stopPropagation){event.stopPropagation;}event.cancelBubble=true;'.$this->marker.'_sendResponse(\'' . $cmd
			. '\');return false;" style="cursor:pointer;" title="' . $title . '">' . $icon . '</span>';
		}
		
			// Probably obsolete
		if ($this->thisScript)
		{
			if ($bMark)
			{
				$anchor = '#' . $bMark;
				$name = ' name="' . $bMark . '"';
			}
			return '<a href="javascript:void(0);" onClick="if(event.stopPropagation){event.stopPropagation;}event.cancelBubble=true;set' . $this->treeName . 'PM(\'' .
			$cmd . '\');TBE_EDITOR_submitForm();return false;"' . $name . '>' . $icon . '</a>';
		} else
		{
			return $icon;
		}
	}
    
	/**
	 * Create the folder navigation tree in HTML
	 * 
	 * @param	mixed		Input tree array. If not array, then $this->tree is used.
	 * @return	string		HTML output of the tree.
	 */
	function printTree($treeArr = '', $open=0, $highlight)
	{	
		$titleLen = intval ( $this->BE_USER->uc ['titleLen'] );
		if (! is_array ( $treeArr ))
			$treeArr = $this->tree;
			
		$c = 0;
		foreach ( $treeArr as $k => $v )
		{	
			$alt = $c % 2;
			if ($alt == 0)
			{
				$v ['row'] ['_CSSCLASS'] = "#f8f8f8";
			}
			
				//check & mark selected
			if ( $v ['row'] ['uid'] == $highlight && $highlight != 0)
			{
				$v ['row'] ['_CSSCLASS'] = "#d0e4c9";
			}
	
			$classAttr = $v [ 'row' ] [ '_CSSCLASS' ];
			$uid	   = $v [ 'row' ][ 'uid' ];
			$idAttr	= htmlspecialchars($this->domIdPrefix.$this->getId($v['row']).'_'.$v['bank']);

			// if this item is the start of a new level,
			// then a new level <ul> is needed, but not in ajax mode
			if($v['isFirst'] && $open != $uid)
			{
				$out .= '<ul style="padding:0px;margin:0px;">';
			} elseif ($open == $uid)
			{
				$out .= '<ul style="padding:0px;margin:0px;">';
			}
			
			// add CSS classes to the list item
			if($v['hasSub'])
			{
				$classAttr .= ($classAttr) ? ' expanded' : 'expanded';
			}
			if($v['isLast'])
			{
				$classAttr .= ($classAttr) ? ' last' : 'last';
			}
	
			//$out  .= '
			//	<li id="' . $idAttr . '"' . ($v ['row'] ['_CSSCLASS'] ? ' style="background-color:'
			//	. $v ['row'] ['_CSSCLASS'] . '"' : ' style="background-color:#ffffff"') . '><div class="treeLinkItem">' . $v ['HTML'] .
			//	$this->wrapTitle ( $this->getTitleStr ( $v ['row'], $titleLen ), $v ['row'], $v ['bank'] ) . '</div>
			//	';
			
			$v ['row'] ['_CSSCLASS'] = $v ['row'] ['_CSSCLASS'] ? $v ['row'] ['_CSSCLASS'] : "#ffffff";
	
			$out  .= '<li id="' . $idAttr .
					 '" style="cursor:pointer;margin:0px;padding:0px;height:16px;display:block;background-color:'. $v ['row'] ['_CSSCLASS'] .
					 '" onMouseOver="this.style.backgroundColor=\'' . ( $v ['row'] ['_CSSCLASS'] == "#d0e4c9" ? "#d0e4c9" : "#ebebeb" ) .
					 '\'" onMouseOut="this.style.backgroundColor=\'' .
					 $v['row']['_CSSCLASS'] . '\'" '.
					 $this->wrapTitle ( $this->getTitleStr ( $v ['row'], $titleLen ), $v ['row'] ).'>'. $v ['HTML'] .
					 $this->getTitleStr ( $v ['row'], $titleLen ). '</li>';
			
			if(!$v['hasSub'])
			{
				$out  .= '</li>';
			}

			// we have to remember if this is the last one
			// on level X so the last child on level X+1 closes the <ul>-tag
			if($v['isLast'] && $open != $uid)
			{
				$closeDepth[$v['invertedDepth']] = 1;
			}

			// if this is the last one and does not have subitems, we need to close
			// the tree as long as the upper levels have last items too
			if($v['isLast'] && !$v['hasSub'] && $open != $uid)
			{
				for ($i = $v['invertedDepth']; $closeDepth[$i] == 1; $i++)
				{
					$closeDepth[$i] = 0;
					$out  .= '</ul></li>';
				}
			}
			$c ++;
		}
		
		return $out;
	}
	
	/**
	 * Returns the id from the record (typ. uid)
	 *
	 * @param	array		Record array
	 * @return	integer		The "uid" field value.
	 */
	function getId($row)
	{
		if ($row['uid'] > 2000)
		{
			return $row['uid']-2000;
		} elseif ($row['uid'] > 1000)
		{
			return $row['uid']-1000;
		} else
		{	
			return $row['uid'];
		}
	}

	function initializePositionSaving ( )
	{	
		$backup = $this->treeName;
		$this->treeName = $fake = preg_replace ( '/_.{4}_.{1,3}/', '', $this->treeName );
		
			// Get stored tree structure:			 
		$this->stored = unserialize ( $this->BE_USER->uc ['browseTrees'] [$this->treeName] );
		
		$this->treeName = $backup;
		
		// PM action
		// (If an plus/minus icon has been clicked, the PM GET var is sent and we must update the stored positions in the tree):
		
			// Todo: Thinking
		if ($this->useXajax)
		{	
			$PM = explode ( '_', t3lib_div::_GET ( 'PM' ) ); // 0: mount key, 1: set/clear boolean, 2: item ID (cannot contain "_"), 3: treeName
		} else
		{
			$PM = explode ( '_', t3lib_div::_GET ( 'PM' ) ); // 0: mount key, 1: set/clear boolean, 2: item ID (cannot contain "_"), 3: treeName
		}
		
		if ( count ( $PM ) >= 5 && $PM [3] == $fake && $PM [0] == 'X' && $PM [1])
		{	
				// expand root
			$this->expandFirst = 1;
			$this->TCEforms_Xitem = $PM [7];
			
				// close all nodes
			$this->stored [0] = array ();
			$rootline = $this->getRootline ( $PM [2], preg_replace ( '/\+/', '_', $PM [5] ), preg_replace ( '/\+/', '_', $PM [6] ) );
			
			foreach ( $rootline as $k => $v )
			{
				$this->stored [0] [$v ['uid']] = 1;
			}
			
			if (is_array($this->stored [$PM [0]] [$PM [2]]) )
			{
				$this->stored [0] [$PM [2]] = 1;
			}
			$this->savePosition ( );
			
		} elseif (isset ( $this->MOUNTS [$PM [0]] ) && $PM [1])
		{
			
			$this->stored [$PM [0]] [$PM [2]] = 1;
			$this->savePosition ();
		
		} else
		{ // clear
			
			if (isset($this->stored [$PM [0]] [$PM [2]]) )
			{
				unset ( $this->stored [$PM [0]] [$PM [2]] );
			}
			$this->savePosition ( );
		}
	}
	
	/**
	 * Returns array with fields of the pages from here ($uid) and back to the root
	 * NOTICE: This function only takes deleted pages into account! So hidden, starttime and endtime restricted pages are included no matter what.
	 * Further: If any "recycler" page is found (doktype=255) then it will also block for the rootline)
	 * If you want more fields in the rootline records than default such can be added by listing them in $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields']
	 *
	 * @param	integer		The page uid for which to seek back to the page tree root.
	 * @param	string		The pid-field.
	 * @param	string		The table.
	 * @return	array		Array with page records from the root line as values. The array is ordered with the outer records first and root record in the bottom. The keys are numeric but in reverse order. So if you traverse/sort the array by the numeric keys order you will get the order from root and out. If an error is found (like eternal looping or invalid mountpoint) it will return an empty array.
	 * @see tslib_fe::getPageAndRootline()
	 */
	function getRootLine($uid, $pid, $table = '')
	{	
			// Initialize:
		$selFields = t3lib_div::uniqueList ( 'uid,pid,title,parent_uid' );
		$this->error_getRootLine = '';
		$this->error_getRootLine_failPid = $loopCheck = 0;
		
		$theRowArray = Array ();
		$uid = intval ( $uid );
		
		while ( $uid != 0 && $loopCheck < 20 )
		{ // Max 20 levels in the page tree.
			$res = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( $selFields, $table, 'uid=' .
				intval ( $uid ) . " AND $table.deleted=0 AND $table.hidden=0" );
			if ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $res ))
			{
				if ( is_array ( $row ) )
				{
					$uid = $row ['parent_uid']; // Next uid
				}
				$theRowArray [] = $row;
			} else
			{
				$this->error_getRootLine = 'Broken rootline';
				$this->error_getRootLine_failPid = $uid;
				return array (); // broken rootline.
			}
			$loopCheck ++;
		}
		
		// Create output array (with reversed order of numeric keys):
		$output = Array ();
		$c = count ( $theRowArray );
		foreach ( $theRowArray as $key => $val )
		{
			$c --;
			$output [$c] = $val;
		}
		return $output;
	}
	
	/**
	 * Saves the content of ->stored (keeps track of expanded positions in the tree)
	 * $this->treeName will be used as key for BE_USER->uc[] to store it in
	 *
	 * @return	void
	 * @access private
	 */
 	function savePosition()
	{
		$backup = $this->treeName;
		$this->treeName = preg_replace ( '/_.{4}_.{1,3}/', '', $this->treeName );
		$this->BE_USER->uc ['browseTrees'] [$this->treeName] = serialize ( $this->stored );
		$this->BE_USER->writeUC ();
		$this->treeName = $backup;
	}
	
	/**
	 * Fetches the data for the tree
	 *
	 * @param	integer		item id for which to select subitems (parent id)
	 * @param	integer		Max depth (recursivity limit)
	 * @param	string		HTML-code prefix for recursive calls.
	 * @param	string		? (internal)
	 * @param	string		CSS class to use for <td> sub-elements
	 * @return	integer		The count of items on the level
	 */
	function getTree($uid, $depth = 999, $depthData = '', $blankLineCode = '', $subCSSclass = '')
	{
			// Buffer for id hierarchy is reset:
		$this->buffer_idH = array();

			// Init vars
		$depth = intval($depth);
		$HTML = '';
		$a = 0;

		$res = $this->getDataInit($uid, $subCSSclass);
		$c = $this->getDataCount($res);
		$crazyRecursionLimiter = 999;

		$idH = array();

			// Traverse the records:
		while ($crazyRecursionLimiter > 0 && $row = $this->getDataNext($res, $subCSSclass))
		{
			$a++;
			$crazyRecursionLimiter--;

			$newID = $row['uid'];

			if ($newID == 0)
			{
				throw new RuntimeException('Endless recursion detected: TYPO3 has detected an error in the database. Please fix it manually (e.g. using phpMyAdmin) and change the UID of ' . $this->table . ':0 to a new value.<br /><br />See <a href="http://bugs.typo3.org/view.php?id=3495" target="_blank">bugs.typo3.org/view.php?id=3495</a> to get more information about a possible cause.', 1294586383);
			}

			$this->tree[] = array(); // Reserve space.
			end($this->tree);
			$treeKey = key($this->tree); // Get the key for this space
			$LN = ($a == $c) ? 'blank' : 'line';

				// If records should be accumulated, do so
			if ($this->setRecs)
			{
				$this->recs[$row['uid']] = $row;
			}

				// Accumulate the id of the element in the internal arrays
			$this->ids_hierarchy[$depth][] = $this->ids[] = $idH[$row['uid']]['uid'] = $row['uid'];
			$this->orig_ids_hierarchy[$depth][] = $row['_ORIG_uid'] ? $row['_ORIG_uid'] : $row['uid'];

				// Make a recursive call to the next level
			$HTML_depthData = $depthData . '<img' . t3lib_iconWorks::skinImg($this->backPath, 'gfx/ol/' . $LN . '.gif', 'width="18" height="16"') . ' alt="" />';
			if ($depth > 1 && $this->expandNext($newID) && !$row['php_tree_stop'])
			{
				$nextCount = $this->getTree(
					$newID,
					$depth - 1,
					$this->makeHTML ? $HTML_depthData : '',
					$blankLineCode . ',' . $LN,
					$row['_SUBCSSCLASS']
				);
				if (count($this->buffer_idH))
				{
					$idH[$row['uid']]['subrow'] = $this->buffer_idH;
				}
				$exp = 1; // Set "did expand" flag
			} else
			{
				$nextCount = $this->getCount($newID);
				$exp = 0; // Clear "did expand" flag
			}

				// Set HTML-icons, if any:
			if ($this->makeHTML)
			{
				$HTML = $depthData . $this->PMicon($row, $a, $c, $nextCount, $exp);
				$HTML .= $this->wrapStop($this->getIcon($row), $row);
				#	$HTML.=$this->wrapStop($this->wrapIcon($this->getIcon($row),$row),$row);
			}

				// Finally, add the row/HTML content to the ->tree array in the reserved key.
			$this->tree[$treeKey] = array(
				'row' => $row,
				'HTML' => $HTML,
				'HTML_depthData' => $this->makeHTML == 2 ? $HTML_depthData : '',
				'invertedDepth' => $depth,
				'blankLineCode' => $blankLineCode,
				'bank' => $this->bank,
				'hasSub' => $nextCount&&$this->expandNext($newID),
				'isFirst'=> $a==1,
				'isLast' => FALSE,	
			);
		}
		
		if($a) { $this->tree[$treeKey]['isLast'] = TRUE; }

		$this->getDataFree($res);
		$this->buffer_idH = $idH;
		return $c;
	}
	
	function getBrowsableTree ( $subtree_Uid = 0, $maxDepth = 999)
	{
			// Get stored tree structure AND updating it if needed according to incoming PM GET var.
		$this->initializePositionSaving();

			// Init done:
		$titleLen = intval($this->BE_USER->uc['titleLen']);
		$treeArr = array();

		$this->MOUNTS [0] = 0;
		
			// Traverse mounts:
		foreach($this->MOUNTS as $idx => $uid)
		{
				// Set first:
			$this->bank = $idx;
			$isOpen = $this->stored[$idx][$uid] || $this->expandFirst || $uid === '0';

				// Save ids while resetting everything else.
			$curIds = $this->ids;
			$this->reset();
			$this->ids = $curIds;

				// Set PM icon for root of mount:
			$cmd = $this->bank.'_'.($isOpen? "0_" : "1_").$uid.'_'.$this->treeName;
				// only, if not for uid 0
			if ($uid)
			{
				$icon = '<img' . t3lib_iconWorks::skinImg($this->backPath,'gfx/ol/' .
							($isOpen ? 'minus' :'plus' ) . 'only.gif') . ' alt="" />';
				$firstHtml = $this->PMiconATagWrap($icon, $cmd, !$isOpen);
			}

				// Preparing rootRec for the mount
			if ($uid)
			{
				$rootRec = $this->getRecord($uid);
				$firstHtml.=$this->getIcon($rootRec);
			} else
			{
				// Artificial record for the tree root, id=0
				$rootRec = $this->getRootRecord($uid);
				$firstHtml.=$this->getRootIcon($rootRec);
			}

			if (is_array($rootRec))
			{
					// In case it was swapped inside getRecord due to workspaces.
				$uid = $rootRec['uid'];

					// Add the root of the mount to ->tree
				$this->tree[] = array(	'HTML'=>$firstHtml,
										'row'=>$rootRec,
										'bank'=>$this->bank,
										'hasSub'=>TRUE,
										'invertedDepth'=>1000);

					// If the mount is expanded, go down:
				if ($isOpen)
				{
						// Set depth:
					if ($this->addSelfId)
					{
						$this->ids[] = $uid;
					}
					$this->getTree($uid, 999, '', $rootRec['_SUBCSSCLASS']);
				}
					// Add tree:
				$treeArr=array_merge($treeArr,$this->tree);
			}
		}
		return $treeArr;
	}
	
	function printRootOnly()
	{
			// Artificial record for the tree root, id=0
		$rootRec = $this->getRootRecord(0);
		$firstHtml = $this->getRootIcon($rootRec);
		$treeArr[] = array('HTML'=>$firstHtml,'row'=>$rootRec,'bank'=>0);
		$this->rootIconIsSet = true;
		return $this->printTree($treeArr);
	}

	function setMounts($mountpoints)
	{
		if (is_array($mountpoints))
		{
			$this->MOUNTS = $mountpoints;
		}
	}

	/********************************
	 *
	 * fix for non-trees - mabye not needed in the future
	 *
	 ********************************/


	/**
	 * Getting the tree data: Counting elements in resource
	 *
	 * @param	mixed		data handle
	 * @return	integer		number of items
	 * @access private
	 * @see getDataInit()
	 */
	function getDataCount($res)
	{
		if ($res)
		{
			return parent::getDataCount($res);
		}
		return 0;
	}


	/**
	 * Getting the tree data: frees data handle
	 *
	 * @param	mixed		data handle
	 * @return	void
	 * @access private
	 */
	function getDataFree($res)
	{
		if ($res)
		{
			return parent::getDataFree($res);
		}
	}


	/********************************
	 *
	 * DAM specific functions
	 *
	 ********************************/

	/**
	 * @return	[t]		...
	 */
	function dam_defaultIcon()
	{
		return $this->iconPath.$this->iconName;
	}

	/**
	 * Returns the title for the tree
	 * 
	 * @return	string		
	 */
	function dam_treeTitle()
	{
		return $this->title;
	}

	/**
	 * Returns the treename (used for storage of expanded levels)
	 * 
	 * @return	string		
	 */
	function dam_treeName()
	{
		return $this->treeName;
	}

	/**
	 * Returns the title of an item
	 * 
	 * @param	[t]		$id: ...
	 * @return	string		
	 */
	function dam_itemTitle($id)
	{
		$itemTitle=$id;

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(implode(',',$this->fieldArray), $this->table, 'uid='.intval($id));
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
		{
			$itemTitle = $this->getTitleStr($row);
		}
		return $itemTitle;
	}

	/**
	 * Function, processing the query part for selecting/filtering records in DAM
	 * Called from DAM
	 * 
	 * @param	string		Query type: AND, OR, ...
	 * @param	string		Operator, eg. '!=' - see DAM Documentation
	 * @param	string		Category - corresponds to the "treename" used for the category tree in the nav. frame
	 * @param	string		The select value/id
	 * @param	string		The select value (true/false,...)
	 * @param	object		Reference to the parent DAM object.
	 * @return	string		
	 * @see tx_dam_SCbase::getWhereClausePart()
	 */
	function dam_selectProc($queryType, $operator, $cat, $id, $value, &$damObj)
	{
#		return array($queryType,$query);
	}
}

?>