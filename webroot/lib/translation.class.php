<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2022 Johannes Keßler
 *
 * Development starting from 2011: Johannes Keßler
 * https://www.bananas-playground.net/projekt/insipid/
 *
 * creator:
 * Luke Reeves <luke@neuro-tech.net>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/gpl-3.0.
 *
 */

/**
 * Class Translation
 *
 * A very simple way to load and provide the translation
 */
class Translation {

	/**
	 * @var string The lang code
	 */
	private string $_defaultLangToUse = 'eng';

	/**
	 * @var array The loaded lang information from the file
	 */
	private array $_langData = array();

	/**
	 * Translation constructor.
	 */
	public function __construct() {
		$_langFile = ABSOLUTE_PATH.'/lib/lang/'.$this->_defaultLangToUse.'.lang.ini';
		if(defined('FRONTEND_LANGUAGE')) {
			$_langFile = ABSOLUTE_PATH.'/lib/lang/'.FRONTEND_LANGUAGE.'.lang.ini';
			if(file_exists($_langFile)) {
				$_langData = parse_ini_file($_langFile);
				if($_langData !== false) {
					$this->_langData = $_langData;
				}
			}
		}
		else {
			$_langData = parse_ini_file($_langFile);
			if($_langData !== false) {
				$this->_langData = $_langData;
			}
		}
	}

	/**
	 * Return text for given key for currently loaded lang
	 *
	 * @param string $key
	 * @return string
	 */
	public function t(string $key): string {
		$ret = $key;
		if(isset($this->_langData[$key])) {
			$ret = $this->_langData[$key];
		}
		return $ret;
	}
}
