<?php
/***************************************************************
*
*  (c) 2010-2012 Chi Hoang (info@chihoang.de)
*  All rights reserved
*  
***************************************************************/
require_once ( PATH_tslib.'class.tslib_pibase.php' );
require_once ( "JSON.php" );
 
class unsereKlasse extends tslib_pibase
{
	function main()
	{
		session_start();
		
		tslib_eidtools::connectDB(); //Connect to database

		if ( $_GET [ "UID" ] )
		{
			if ( $_GET [ "screen" ] == "reload" )
			{
				unset ( $_SESSION [ "Menu" ] );
				unset ( $_SESSION [ "bins" ] );
				return;
			}
			
			if ( ! $_SESSION [ "Menu" ] )
			{
				$res = $GLOBALS [ 'TYPO3_DB' ]->exec_SELECTquery (
					'V.sorting Sorting',
					'tx_charbeitsbeispiele_maxmedia V',
					'V.pid='.$_SESSION [ "sysfolder" ].' AND V.parent_id=0 AND V.deleted=0 AND V.hidden=0',
					'',
					'V.sorting ASC'
				);
			
				while ( $r = $GLOBALS [ 'TYPO3_DB' ]->sql_fetch_assoc ( $res ) )
				{
					$_SESSION [ "Menu" ] [ $r [ "Sorting" ] ] = "off";				
				}
			}
	
			$res = $GLOBALS [ 'TYPO3_DB' ]->exec_SELECTquery (
				'V.isroot Superkategorie',
				'tx_charbeitsbeispiele_maxmedia V',
				'V.pid='.$_SESSION [ "sysfolder" ].' AND V.uid='. addslashes ( $_GET [ "UID" ]) .' AND V.deleted=0 AND V.hidden=0',
				'',
				'V.sorting ASC'
			);

			$r = $GLOBALS [ 'TYPO3_DB' ]->sql_fetch_assoc ( $res );
	
			if ( $r [ "Superkategorie" ] )
			{
				$res = $GLOBALS [ 'TYPO3_DB' ] -> sql_query (
					'SELECT 
					V.uid UID, V.title Headline, V.subheadline Subheadline,
					V.text Text, V.link Link, V.image Image,
					T.sorting Sorting, T.animate,
					IF (V.target=0, "_self", "_blank") AS "Target" 
					FROM 
					tx_charbeitsbeispiele_maxmedia V 
					LEFT JOIN tx_charbeitsbeispiele_maxmedia T ON  T.uid = V.parent_id 
					WHERE V.pid='.$_SESSION [ "sysfolder" ].' AND V.parent_id!=0 AND V.deleted=0 AND V.hidden=0 
					ORDER BY T.sorting ASC'
				);
				
			} else
			{
				$res = $GLOBALS [ 'TYPO3_DB' ] -> sql_query (
					'SELECT  
					V.uid UID, V.title Headline, V.subheadline Subheadline,
					V.text Text, V.link Link,
					V.image Image, T.title Kategorie, T.uid PID, T.sorting Sorting, T.animate, 
					IF (V.target=0, "_self", "_blank") AS "Target" 
					FROM  
					tx_charbeitsbeispiele_maxmedia V  
					LEFT JOIN tx_charbeitsbeispiele_maxmedia T ON  T.uid = V.parent_id 
					WHERE V.pid='.$_SESSION [ "sysfolder" ].' AND V.parent_id='.addslashes ($_GET ["UID"]).
					' AND V.deleted=0 AND V.hidden=0 
					ORDER BY V.sorting ASC'
				);
			}
			
			$host = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : 'localhost/';
			$php_self = str_replace('index.php','', $_SERVER['PHP_SELF']);
		
			while ( $r = $GLOBALS [ 'TYPO3_DB' ]->sql_fetch_assoc ( $res ) )
			{
				list($width, $height, $type, $attr) = getimagesize("uploads/tx_charbeitsbeispiele/" .
										   $r [ "Image" ]);
				
				if ( $_GET [ "screen" ] == "clear" )
				{
					$_SESSION [ "bins" ] [ $r [ "UID" ] ] [ "screen" ] = "clear"; 
	
					
				} else if ( $_GET [ "killbit" ] == "on" || ! $_GET [ "killbit" ] )
				{
						// Erste und Noch-Nicht im Bild
					if ( ! is_array ( $_SESSION [ "bins" ] ) ||
						( ! $_SESSION [ "bins" ] [ $r [ "UID" ] ] )
					    )
					{
						// Menu sort etc.
						$_SESSION [ "Menu" ] [ $r [ "Sorting" ] ] = "on";
						
						// Screen bzw. Canvas
						$_SESSION [ "bins" ] [ $r [ "UID" ] ] = array (
							"Headline" => $r [ "Headline" ],
							"Subheadline" => $r [ "Subheadline" ],
							"Text" => $r [ "Text" ],
							"Islink" => $r [ "Link" ] == true ? "true" : "false",
							"Link" =>  $r [ "animate" ] == true ? "javascript:void(0);" : "http://".$host.$php_self.'index.php?id='.$r [ "Link" ],
							"OnClick" => $r [ "animate" ] == true ? "maxmedia.singleview('".$host.$php_self."','".$r [ "Link" ]."');return false;" : "return false;",
							"Image" => $r [ "Image" ] == true ? "uploads/tx_charbeitsbeispiele/".$r [ "Image" ] : "",
							"Isimage" => $r [ "Image" ] == true ? "true" : "false",
							"Target" => $r [ "Target" ],
							"Height" => $height,
							"Width" => $width,
							"killbit" => "on",
							"Kategorie" => $r [ "Kategorie" ],
							"Sorting" => $r [ "Sorting" ],
							"Uid" => $r [ "UID" ]
						);
						
						$p [ ] = array (
							"Headline" => $r [ "Headline" ],
							"Subheadline" => $r [ "Subheadline" ],
							"Text" => $r [ "Text" ],
							"Islink" => $r [ "Link" ] == true ? "true" : "false",
							"Link" =>  $r [ "animate" ] == true ? "javascript:void(0);" : "http://".$host.$php_self.'index.php?id='.$r [ "Link" ],
							"OnClick" => $r [ "animate" ] == true ? "maxmedia.singleview('".$host.$php_self."','".$r [ "Link" ]."');return false;" : "return false;",
							"Image" => $r [ "Image" ] == true ? "uploads/tx_charbeitsbeispiele/".$r [ "Image" ] : "",
							"Isimage" => $r [ "Image" ] == true ? "true" : "false",
							"Target" => $r [ "Target" ],
							"Height" => $height,
							"Width" => $width,
							"killbit" => "on",
							"Kategorie" => $r [ "Kategorie" ],
							"Sorting" => $r [ "Sorting" ],
							"brickid" => $r [ "UID" ]
						);
					}
				} else if ( $_GET [ "killbit" ] == "off" )
				{
					// Menu sort etc.
					$_SESSION [ "Menu" ] [ $r [ "Sorting" ] ] = "off";
						
					// Screen bzw. Canvas
					unset ( $_SESSION [ "bins" ] [ $r [ "UID" ] ] );
					
					$p [ ]  = array (
						"Headline" => $r [ "Headline" ],
						"Subheadline" => $r [ "Subheadline" ],
						"Text" => $r [ "Text" ],
						"Islink" => $r [ "Link" ] == true ? "true" : "false",
						"Link" =>  $r [ "animate" ] == true ? "javascript:void(0);" : "http://".$host.$php_self.'index.php?id='.$r [ "Link" ],
						"OnClick" => $r [ "animate" ] == true ? "maxmedia.singleview('".$host.$php_self."','".$r [ "Link" ]."');return false;" : "return false;",
						"Image" => $r [ "Image" ] == true ? "uploads/tx_charbeitsbeispiele/".$r [ "Image" ] : "",
						"Isimage" => $r [ "Image" ] == true ? "true" : "false",
						"Target" => $r [ "Target" ],
						"Height" => $height,
						"Width" => $width,
						"killbit" => "off",
						"Kategorie" => $r [ "Kategorie" ],
						"Sorting" => $r [ "Sorting" ],
						"brickid" => $r [ "UID" ]
					);						
				}
			}
			
			if ( $_GET [ "screen" ] == "clear" )
			{
				foreach ( $_SESSION [ "bins" ] as $key => $value )
				{
					if ( ! $value [ "screen" ] )
					{
						$_SESSION [ "Menu" ] [ $_SESSION [ "bins" ] [ $key ] [ "Sorting" ] ] = "off";
						unset ( $_SESSION [ "bins" ] [ $key ] );
						$value [ "killbit" ] = "off";	
						$p [ ] = $value;
					} 
				}
			}
			
			$low = 9999;
			$high = 0;
			foreach ( $_SESSION [ "Menu" ] as $a => $b )
			{
				if ( $b == "on" && $a < $low )
				{
					$low = $a;
				}
				if ( $b == "on" && $a > $high )
				{
					$high = $a;
				};
			}
				
			foreach ( $p as $key => $value )
			{
				if ( $value [ "Sorting" ] <= $low && $value [ "Sorting" ] < $high )
				{
					$p [ $key ] [ "Additem" ] = "Prepend";
				} else if ( $value [ "Sorting" ] >= $high )
				{
					$p [ $key ] [ "Additem" ] = "Prepend";
				} else {
					$p [ $key ] [ "Additem" ] = "Prepend";
				}
			}
			
			echo json_encode ( $p );
		} else
		{
			echo json_encode ( array ( "screen" => "reload" ) );	
		}
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