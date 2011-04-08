<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2011 Wolfgang Rotschek <scotty@dev-null.at>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is 
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

/** 
 * @author	Benjamin Mack (www.xnos.org) 
 * @subpackage	tx_seobasics
 * 
 * This package includes all functions for generating XML sitemaps
 */

require_once(PATH_t3lib.'class.t3lib_page.php');
require_once(PATH_t3lib.'class.t3lib_div.php');

// Typo3 debugging utility
require_once(PATH_t3lib.'utility/class.t3lib_utility_debug.php');

// base class
require_once(t3lib_extMgm::extPath('dev_null_seo', 'renderer/class.tx_devnullseo_render_abstract.php'));

class tx_devnullseo_render_flvplayer extends tx_devnullseo_render_abstract
{

	public function getXmlWrapName() {
		return 'pageItems';
	}
	
	public function getIncludeRenderer($name, $pageConfig) {
		return NULL;
	}
	
	public function renderItems($page, $config, $section = NULL) {
		$selectClause = array(
			'pid = ' . $page,							// page holding record
			'CType = "list"',							// content types
			'list_type = "flvplayer_pi1"',              // plugin
			'deleted = 0',								// no deleted records
			'(starttime = 0 || starttime > NOW())',		// starttime
			'(endtime = 0 || endtime < NOW())',			// endtime
			'fe_group = ""',							// only unrestricted content
		);

		// build query to access page content
		$dbRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery('deleted, starttime, endtime, header, pi_flexform', 'tt_content', implode(' AND ', $selectClause));
		while($rowArray = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbRes)) {

			// t3lib_utility_Debug::printArray($rowArray, 'media');

			$_flexform 	= t3lib_div::xml2array($rowArray['pi_flexform']);
			$_media     = $_flexform['data']['sDEF']['lDEF'];
			
			// t3lib_utility_Debug::printArray($_media, 'media');

			$_copyright = $this->getPageLink($page);
			
			$_video		= $this->getImageLink($_media['file']['vDEF']);
			$_splash	= $this->getImageLink($_media['image']['vDEF']);
							
			$nodeItems = array();
			$nodeItems[] = $this->wrapXmlItem('videoLoc', $_video);
			$nodeItems[] = $this->wrapXmlItem('videoThumb', $_splash);
			$nodeItems[] = $this->wrapXmlItem('videoTitel', $rowArray['header']);
			$nodeItems[] = $this->wrapXmlItem('videoDescr', $rowArray['header']);

			// t3lib_utility_Debug::printArray($nodeItems, 'image');
			
			$xml = $this->wrapXmlItem('video', implode("\n", $nodeItems));
			
			$this->items[$_video] = $xml;
		}
		// t3lib_utility_Debug::printArray($this->items, 'image');
		
		$GLOBALS['TYPO3_DB']->sql_free_result($dbRes);

	}
	
	/**
	 * Creates a link to a single page
	 *
	 * @param	array	$pageId	Page ID
	 * @return	string	Full URL of the page including host name (escaped)
	 */
	protected function getPageLink($pageId) {
		$conf = array(
			'parameter' => $pageId,
			'returnLast' => 'url',
		);
		$link = htmlspecialchars($this->cObj->typoLink('', $conf));
		return t3lib_div::locationHeaderUrl($link);
	}

	/**
	 * Creates a link to a single image
	 *
	 * @param	array	$pageId	Page ID
	 * @return	string	Full URL of the page including host name (escaped)
	 */
	protected function getImageLink($image) {

		$link = htmlspecialchars($image);
		return t3lib_div::locationHeaderUrl($link);
	}

}
?>
