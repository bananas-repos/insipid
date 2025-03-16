<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2025 Johannes Keßler
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
 * Class Snapshot
 * Create from given ULR a Screenshot for storage.
 * Currently only browserless.io is supported.
 *
 */
class Snapshot {

    /**
     * Snapshot constructor
     */
    public function __constructor(): void {}

    /**
     * use configured COMPLETE_PAGE_SCREENSHOT_COMMAND to create a whole page screenshot
     * of the given link and store it locally
     *
     * Uses browserless.io and needs settings in config.php
     *
     * @param String $url URL to take the screenshot from
     * @param string $filename
     * @return boolean
     */
    public function wholePageSnapshot(string $url, string $filename): bool {
        $ret = false;

        if(!empty($url) && is_writable(dirname($filename))) {

            $postdata = json_encode(array(
                'url' => $url,
                'waitFor' => COMPLETE_PAGE_SCREEENSHOT_BROWSERLESS_TIMEOUT,
                'options' => array(
                    'fullPage' => true,
                    'type' => "jpeg",
                    'quality' => COMPLETE_PAGE_SCREEENSHOT_BROWSERLESS_IMAGE_QUALITY
                )
            ));

            if(DEBUG) Summoner::sysLog("DEBUG browserless json data ".Summoner::cleanForLog($postdata));

            $fh = fopen($filename, 'w+');

            $api_url = COMPLETE_PAGE_SCREENSHOT_BROWSERLESS_API.COMPLETE_PAGE_SCREENSHOT_BROWSERLESS_API_KEY;
            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_FILE, $fh);
            //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Cache-Control: no-cache'));

            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 2);

            // manual DEBUG ONLY
            //$curl_log = fopen(ABSOLUTE_PATH."/curl.log", 'w');
            //curl_setopt($ch, CURLOPT_VERBOSE, true);
            //curl_setopt($ch, CURLOPT_STDERR, $curl_log);

            if(!empty($port)) {
                curl_setopt($ch, CURLOPT_PORT, $port);
            }
            $do = curl_exec($ch);
            curl_close($ch);
            fclose($fh);

            if(DEBUG) Summoner::sysLog("DEBUG return ".Summoner::cleanForLog($do));

            // manual DEBUG ONLY
            //fclose($curl_log);

            $ret = true;
        } else {
            Summoner::sysLog("ERROR URL $url is empty or target $filename is not writeable.");
        }

        return $ret;
    }
}
