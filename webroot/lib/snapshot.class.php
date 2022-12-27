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
 * class Snapshot
 * create from given ULR a Screenshot for storage
 * right now it uses google pagespeedonline.
 */
class Snapshot {
	/**
	 * @var string
	 */
	private string $_googlePageSpeed = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=';

	/**
	 * @var string
	 */
	private string $_wkhtmltoimageOptions = '--load-error-handling ignore --quality 80 --quiet --width 1900';

	/**
	 * Snapshot constructor
	 */
	public function __constructor(): void {}

	/**
	 * call given url with google PageSpeed API
	 * to receive image data
	 *
	 * @param String $url URL to take a thumbnail from
	 * @param string $filename
	 * @return boolean
	 */
	public function doSnapshot(string $url, string $filename): bool {
		$ret = false;

		// new path in jason
		// lighthouseResult audits full-page-screenshot details screenshot data (base64 encoded)

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

	/**
	 * use configured WKHTMLTOPDF_COMMAND to create a whole page screenshot
	 * of the given link and store it locally
	 *
	 * @param String $url URL to take the screenshot from
	 * @param string $filename
	 * @return boolean
	 */
	public function wholePageSnapshot(string $url, string $filename): bool {
		$ret = false;

		require_once 'lib/shellcommand.class.php';

		if(!empty($url) && is_writable(dirname($filename))) {
			$cmd = WKHTMLTOPDF_COMMAND;
			$params = $this->_wkhtmltoimageOptions." ".$url." ".$filename;
			$command = new ShellCommand($cmd." ".$params);
			if ($command->execute()) {
			    $ret = $command->getOutput();
			} else {
				error_log($command->getError());
				$ret = $command->getExitCode();
			}
		}

		return $ret;
	}
}
