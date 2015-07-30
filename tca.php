<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_charbeitsbeispiele_maxmedia'] = array (
	'ctrl' => $TCA['tx_charbeitsbeispiele_maxmedia']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,title,text,subheadline,link,parent_id'
	),
	'feInterface' => $TCA['tx_charbeitsbeispiele_maxmedia']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'title' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ch_arbeitsbeispiele/locallang_db.xml:tx_charbeitsbeispiele_maxmedia.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',
			)
		),
                'subheadline' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ch_arbeitsbeispiele/locallang_db.xml:tx_charbeitsbeispiele_maxmedia.subheadline',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',
			)
		),
		'text' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ch_arbeitsbeispiele/locallang_db.xml:tx_charbeitsbeispiele_maxmedia.text',		
			'config' => array (
				'type' => 'text',
				'cols' => '30',	
				'rows' => '5',
			)
		),
                "image" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ch_arbeitsbeispiele/locallang_db.xml:tx_charbeitsbeispiele_maxmedia.image",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "gif,png,jpeg,jpg",	
				"max_size" => 100000,	
				"uploadfolder" => "uploads/tx_charbeitsbeispiele",
                                "show_thumbs" => 1,
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		'link' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ch_arbeitsbeispiele/locallang_db.xml:tx_charbeitsbeispiele_maxmedia.link',		
			'config' => array (
				'type'     => 'input',
				'size'     => '15',
				'max'      => '255',
				'checkbox' => '',
				'eval'     => 'trim',
				'wizards'  => array(
					'_PADDING' => 2,
					'link'     => array(
						'type'         => 'popup',
						'title'        => 'Link',
						'icon'         => 'link_popup.gif',
						'script'       => 'browse_links.php?mode=wizard',
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
					)
				)
			)
		),
		'parent_id' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ch_arbeitsbeispiele/locallang_db.xml:tx_charbeitsbeispiele_maxmedia.parent_id',		
			"config" => Array (			
				'type' => 'select',
				'form_type' => 'user',
				'userFunc' => 'tx_ch_treeview->displayCategoryTree',
				'treeView' => 1,
				'treeName' => 'txcharbeitsbeispiele',
				'treeMaxDepth' => 5,
				'size' => 10,
				'autoSizeMax' => 25,
				'selectedListStyle' => 'width:250px',
				'minitems' => 0,
				'maxitems' => 100,  
				'foreign_table' => 'tx_charbeitsbeispiele_maxmedia',
                                'orderBy' => 'sorting ASC',
                                "expandFirst" => 1,
				'wizards' => Array(
					'_PADDING' => 2,
					'_VERTICAL' => 1,
					'add' => Array(
						'type' => 'script',
						'title' => 'LLL:EXT:ch_treeview/locallang_db.xml:tx_chtreeview_example.createNewParentCategory',
						'icon' => 'add.gif',
						'params' => Array(
							'table'=>'tx_chtreeview_example',
							'pid' => '###CURRENT_PID###',
							'setValue' => 'set'
						),
						'script' => 'wizard_add.php',
					),
					'list' => Array(
						'type' => 'script',
						'title' => 'LLL:EXT:ch_treeview/locallang_db.xml:tx_treeview_example.listCategories',
						'icon' => 'list.gif',
						'params' => Array(
							'table'=>'tx_chtreeview_example',
							'pid' => '###CURRENT_PID###',
						),
						'script' => 'wizard_list.php',
					),
				),
			)
		),
                "isroot" => Array (		
			"exclude" => 0,
			'label'   => 'LLL:EXT:ch_arbeitsbeispiele/locallang_db.xml:tx_charbeitsbeispiele_maxmedia.isroot',
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
                'target' => array (        
                    'exclude' => 0,        
                    'label' => 'LLL:EXT:ch_arbeitsbeispiele/locallang_db.xml:tx_charbeitsbeispiele_maxmedia.target',        
                    'config' => array (
                        'type' => 'select',
                        'items' => array (
                            array('LLL:EXT:ch_arbeitsbeispiele/locallang_db.xml:tx_charbeitsbeispiele_maxmedia.target.I.0', '0'),
                            array('LLL:EXT:ch_arbeitsbeispiele/locallang_db.xml:tx_charbeitsbeispiele_maxmedia.target.I.1', '1'),
                        ),
                        'size' => 1,    
                        'maxitems' => 1,
                    )
                ),
                "animate" => Array (		
			"exclude" => 0,
			'label'   => 'LLL:EXT:ch_arbeitsbeispiele/locallang_db.xml:tx_charbeitsbeispiele_maxmedia.animate',
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'title;;;;2-2-2, subheadline, text, image, link, target, parent_id, isroot, animate')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);
?>