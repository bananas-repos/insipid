<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2020 Johannes Keßler
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
 * class Snapshot
 * create from given ULR a Screenshot for storage
 * right now it uses google pagespeedonline.
 */
class Snapshot {
	private $_googlePageSpeed = 'https://www.googleapis.com/pagespeedonline/v2/runPagespeed?url=';

	public function __constructor() {}

	/**
	 * call given url with google PageSpeed API
	 * to recieve image data
	 *
	 * @param String $url URL to take a thumbnail from
	 * @return
	 */
	public function doSnapshot($url,$filename) {
		$ret = false;

		if(!empty($url) && is_writable(dirname($filename))) {
			$theCall = Summoner::curlCall($this->_googlePageSpeed.urlencode($url).'&screenshot=true');
			if(!empty($theCall)) {
				$jsonData = json_decode($theCall,true);
				if(!empty($jsonData) && isset($jsonData['screenshot']['data'])) {
					$imageData = $jsonData['screenshot']['data'];
					$imageData = str_replace(['_', '-'], ['/', '+'], $imageData);
					$imageData = base64_decode($imageData);
					$ret = file_put_contents($filename, $imageData);
				}
			}
		}

		return $ret;
	}
}