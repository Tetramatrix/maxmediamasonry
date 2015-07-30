<?php
/***************************************************************
*  
*  (c) 2010-2011 Chi Hoang (info@chihoang.de)
*  All rights reserved
*
***************************************************************/
require_once ( PATH_tslib.'class.tslib_pibase.php' );

class tx_ch1_tested extends tslib_pibase { 
	
	function array2json($arr) {
		if(function_exists('json_encode')) return json_encode($arr); //Lastest versions of PHP already has this functionality.
		$parts = array();
		$is_list = false;
	    
		//Find out if the given array is a numerical array
		$keys = array_keys($arr);
		$max_length = count($arr)-1;
		if(($keys[0] == 0) and ($keys[$max_length] == $max_length)) {//See if the first key is 0 and last key is length - 1
		    $is_list = true;
		    for($i=0; $i<count($keys); $i++) { //See if each key correspondes to its position
			if($i != $keys[$i]) { //A key fails at position check.
			    $is_list = false; //It is an associative array.
			    break;
			}
		    }
		}
	    
		foreach($arr as $key=>$value) {
		    if(is_array($value)) { //Custom handling for arrays
			if($is_list) $parts[] = array2json($value); /* :RECURSION: */
			else $parts[] = '"' . $key . '":' . array2json($value); /* :RECURSION: */
		    } else {
			$str = '';
			if(!$is_list) $str = '"' . $key . '":';
	    
			//Custom handling for multiple data types
			if(is_numeric($value)) $str .= $value; //Numbers
			elseif($value === false) $str .= 'false'; //The booleans
			elseif($value === true) $str .= 'true';
			else $str .= '"' . addslashes($value) . '"'; //All other things
			// :TODO: Is there any more datatype we should be in the lookout for? (Object?)
	    
			$parts[] = $str;
		    }
		}
		$json = implode(',',$parts);
		
		if($is_list) return '[' . $json . ']';//Return numerical JSON
		return '{' . $json . '}';//Return associative JSON
	    }

	function getPrefix( $arr, &$c )
	{
		foreach ( $arr as $k => $v )
		{
			if (is_array($v['fieldset']))
			{
				$obj = $this->getPrefix ( array ( $v['colname'] => $v['fieldset']), $c );
			}
			if (!is_null($v['prefix']))
			{
				$c[$v['prefix']]['prefix'] = $v['prefix'];
				$c[$v['prefix']]['cmd'] = $v['cmd'];
				$c[$v['prefix']]['break'] = $v['break'];
				$c[$v['prefix']]['fieldset'] = true;
				$c[$v['prefix']]['id'] = $v['id'];
				$c[$v['prefix']]['node'] = count($v['path']);
			}
			if (is_array($v['tab']))
			{
				foreach ($v['tab'] as $kT => $vT)
				{
					$c[$v['prefix']]['tab'][$kT]['prefix'] = $vT['prefix'];
					$c[$v['prefix']]['tab'][$kT]['cmd'] = $vT['cmd'];
					$c[$v['prefix']]['tab'][$kT]['break'] = $vT['break'];
					$c[$v['prefix']]['tab'][$kT]['node'] = count($v['path']);
				}
			}
		}
	}
	
	function array_depth ( $array )
	{
		$max_indentation = 1;

		$array_str = print_r ( $array, true );
		$lines = explode ( "\n", $array_str );

		foreach ($lines as $line)
		{
			$indentation = (strlen($line) - strlen(ltrim($line))) / 4;

			if ($indentation > $max_indentation)
			{
				$max_indentation = $indentation;
			}
		}
		return ceil(($max_indentation - 1) / 2) + 1;
	}

	/**
	 * Returns a string containing a Javascript include of the xajax.js file
	 * along with a check to see if the file loaded after six seconds
	 * (typically called internally by xajax from get/printJavascript).
	 * 
	 * @param string the relative address of the folder where xajax has been
	 *               installed. For instance, if your PHP file is
	 *               "http://www.myserver.com/myfolder/mypage.php"
	 *               and xajax was installed in
	 *               "http://www.myserver.com/anotherfolder", then $sJsURI
	 *               should be set to "../anotherfolder". Defaults to assuming
	 *               xajax is in the same folder as your PHP file.
	 * @param string the relative folder/file pair of the xajax Javascript
	 *               engine located within the xajax installation folder.
	 *               Defaults to xajax_js/xajax.js.
	 * @return string
	 */
	function getJavascriptInclude($sJsURI="", $sJsFile=NULL)
	{
		if ($sJsURI != "" && substr($sJsURI, -1) != "/") $sJsURI .= "/";
		$html = "\t<script type=\"text/javascript\" src=\"" . $sJsURI . $sJsFile . "\"></script>\n";
		return $html;
	}

	/**
	 * Returns a string containing a Javascript include of the xajax.js file
	 * along with a check to see if the file loaded after six seconds
	 * (typically called internally by xajax from get/printJavascript).
	 * 
	 * @param string the relative address of the folder where xajax has been
	 *               installed. For instance, if your PHP file is
	 *               "http://www.myserver.com/myfolder/mypage.php"
	 *               and xajax was installed in
	 *               "http://www.myserver.com/anotherfolder", then $sJsURI
	 *               should be set to "../anotherfolder". Defaults to assuming
	 *               xajax is in the same folder as your PHP file.
	 * @param string the relative folder/file pair of the xajax Javascript
	 *               engine located within the xajax installation folder.
	 *               Defaults to xajax_js/xajax.js.
	 * @return string
	 */
	function getCSSInclude ($sCSSURI="", $sCSSFile=NULL)
	{
		if ($sCSSURI != "" && substr($sCSSURI, -1) != "/") $sCSSURI .= "/";
		$html = "\t<link rel=\"stylesheet\" href=\"" . $sCSSURI . $sCSSFile . "\" media=\"screen\">\n";
		return $html;
	}
	
		/* Convert Extended ASCII Characters to HTML Entities */
        function ascii2utf8($string)
	{
            for($i=128;$i<=255;$i++)
	    {
                $entity = htmlentities(chr($i), ENT_QUOTES, 'cp1252');
                $entity_utf8 = utf8_encode(chr($i));
                $temp = substr($entity, 0, 1);
                $temp .= substr($entity, -1, 1);
                if ($temp != '&;')
		{
                    $string = str_replace(chr($i), '', $string);
                } else
		{
                    $string = str_replace(chr($i), $entity_utf8, $string);
            
		}
	    }
            return $string;
        }
			
	function ascii2symbol ( $string, $symbol )
	{
		for ( $i=128; $i <= 255; $i++ )
		{
			$entity = htmlentities ( chr($i), ENT_QUOTES, 'cp1252' );
			$temp = substr ($entity, 0, 1);
			$temp .= substr ($entity, -1, 1);
			if ( $temp != '&;' )
			{
			    $string = str_replace ( chr($i), '', $string);
			} else
			{
			    $string = str_replace ( chr($i), $symbol, $string);
			}
		}
	    return $string;
	}
	
	/* Convert Extended ASCII Characters to HTML Entities */
	function ascii2entities ( $string )
	{
		for( $i=128;$i <= 255; $i++ )
		{
			$entity = htmlentities( chr($i), ENT_QUOTES, 'cp1252');
			$temp = substr ($entity, 0, 1 );
			$temp .= substr ( $entity, -1, 1);
			if ( $temp != '&;' )
			{
			    $string = str_replace ( chr($i), '', $string );
			} else
			{
			    $string = str_replace ( chr($i), $entity, $string );
			}
		}
	    return $string;
	}

	function __unserialize ( $string )
	{
		$unserialized = html_entity_decode ( $this->ascii2entities($string), ENT_QUOTES, 'cp1252' );
		$unserialized = preg_replace( '!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $unserialized );
		return unserialize( $unserialized );
	}
	
	function sGetFormResult ( )
	{
		$sResult = '%s'; $sContent = '<strong>' . $this->pi_getLL('form_result', '', TRUE) .'</strong>';
		$this->piVars [ 'mtf' ] = $this->desti; 
		foreach ($this->piVars as $k => $v)
		{
			$tmp [ $k ] = is_array ( $v ) ? $v : wordwrap( $v, 53, "\n", 1 );
		}
		$sContent .= t3lib_utility_Debug::viewArray ( $tmp);
		$sContent .= t3lib_utility_Debug::viewArray ( $this->andWhere );
		$sContent .= t3lib_utility_Debug::viewArray ( $this->orWhere );
		$sContent .= t3lib_utility_Debug::viewArray ( $this->having );
		$sContent .= t3lib_utility_Debug::viewArray ( array ( $this->parent_uid,
				 $GLOBALS['TSFE']->fe_user->user['uid'], session_id ( ) )
			);
		$sContent .= t3lib_utility_Debug::viewArray ( $_SESSION );
		$sContent .= t3lib_utility_Debug::viewArray ( $this->record );
		$sContent .= t3lib_utility_Debug::viewArray ( $this->lConf );
		$sContent = preg_replace ( '/table/', 'table width="400"',$sContent);
		$sResult = '<div style="clear:both"></div><div id="formResult">%s</div>';
		$sResult = sprintf(
			$sResult,
			$sContent
		);
		return $sResult;
	}  

	function quicksort(&$a, $al, $ar, $index)
	{
	        $links = $al; $rechts = $ar; $k = array_keys($a); $d = rand($al,$ar);
	        $pivot = $a[$k[$d]][$index];
	        do {
	                while ($a[$k[$links]][$index] < $pivot) ++$links;
	                while ($a[$k[$rechts]][$index] > $pivot) --$rechts;
	                if ($links <= $rechts) {
	                        $tmp = $a[$k[$links]];
	                        $a[$k[$links]] = $a[$k[$rechts]];
	                        $a[$k[$rechts]] = $tmp;
	                        ++$links;
	                        --$rechts;                
	                }               
	        } while($links < $rechts);
	        if ($al < $rechts) $this->quicksort($a, $al, $rechts,$index);
	        if ($links < $ar) $this->quicksort($a, $links, $ar,$index);        
	}
	
	function natSortKey(&$arrIn)
	{
		$key_array = $arrOut = array();
		if (count($arrIn)>1)
		{
			foreach ($arrIn as $key => $value)
			{
				$key_array[] = trim($key);
			}
			$res = natsort($key_array);
			foreach ($key_array as $key => $value)
			{
				$arrOut[$value] = $arrIn[$value];
			}
			$arrIn = $arrOut;
		}
	}
	
	function mtf(&$tab, $source, &$destination)
	{
		$len = count($source); $tabLen = count($tab);
		for ( $i = 0 ; $i < $len; ++$i)
		{   // parcourt du buffer source
			if ($source[$i]['name'] == $tab[0]['name'] || $source[$i]['name'] == $tab[0]['type']['name'])
			{ // la valeur courante est elle egale a l'index 0   
				$destination[$i] = 0; 	// oui ecrire 0
			} else
			{ // sinon
				for($pos = 0; $i < $tabLen && $pos < $tabLen; ++$pos)
				{ // recherche du nouvelle l'index
					if ($tab[$pos]['name'] == $source[$i]['name'] || $tab[$pos]['type']['name'] == $source[$i]['name'])
					{
						break;
					}
				}
				if($pos == $tabLen)
				{
					continue;
				}
					// backup found value
				$source[$i] = $tab[$pos];
					// rotate index one to the right
				for($index = $pos; $index > 0; --$index)
				{
					$tab[$index] = $tab[$index-1]; // index trouve rotation des index.
				}	           
					// mtf
				$tab[0] = $source[$i];
					 // save pos	           
				$destination[$i] = $pos; // ecriture de l'index trouv�.
			}
		}
		return;
	}
	
	function imtf(&$tab,$source,&$destination)
	{
		$len = count($source);
		for ( $i = 0; $i < $len ; ++$i)
		{ // parcourt du buffer a inverser.
			if ($source[$i]['name'] == $tab[0]['name'] || $source[$i]['name'] == $tab[0]['type']['name']) { // si s'agit de la meme valeur originale      
				$destination[$i] = $tab[0]; // ecrire cette valeur	        
			} else
			{ // sinon
				$pos = $tab[$source[$i]]; // recherche de la nouvelle valeur	           
				for($index = $source[$i]; $index > 0; --$index)
				{
					$tab[$index] = $tab[$index-1]; // rotation des index           
				}          
				$destination[$i] = $tab[0] = $pos; // ecrire cette valeur
			}
		}	    
		return;
	}

	/**
	 * No operation
         *
         */
	function nop()
	{
	}
	
	/**
	 * Format string with general_stdWrap from configuration
	 * 
	 * @param	string		$string to wrap
	 * @return	string		wrapped string
	 */
	function formatStr ( $str )
	{
		if ( is_array ( $this->conf [ 'general_stdWrap.' ] ) )
		{
			$str = $this->cObj->stdWrap ($str, $this->conf [ 'general_stdWrap.' ] );
		}
		return $str;
	}
	
	/**
	 * Format string with rte_stdWrap from configuration
	 * 
	 * @param	string		$string to wrap
	 * @return	string		wrapped string
	 */
	function formatStrRTE ( $str )
	{
		if ( is_array ( $this->conf [ 'rte_stdWrap.' ] ) )
		{
			$str = $this->cObj->stdWrap ( $str, $this->conf [ 'rte_stdWrap.' ] );
		}
		return $str;
	}
	
	function trim_value ( &$value )
	{
		$value = trim ( $value );
	}
}
?>