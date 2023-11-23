<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2023 Johannes Keßler
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
 * right now it uses google pagespeedonline for a simple snapshot
 *
 *
 */
class Snapshot {
    /**
     * @var string
     */
    private string $_googlePageSpeed = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=';

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

        if(!empty($url) && is_writable(dirname($filename))) {
            if(DEBUG) {
                Summoner::sysLog("DEBUG try to save to $filename with $this->_googlePageSpeed for $url");
            }
            $theCall = Summoner::curlCall($this->_googlePageSpeed.urlencode($url).'&screenshot=true');
            if(!empty($theCall['status'])) {
                $jsonData = json_decode($theCall['message'],true);
                if(DEBUG) {
                    Summoner::sysLog("DEBUG Call result data: ".Summoner::cleanForLog($jsonData));
                }
                if(!empty($jsonData) && isset($jsonData['lighthouseResult']['fullPageScreenshot']['screenshot']['data'])) {
                    $imageData = $jsonData['lighthouseResult']['fullPageScreenshot']['screenshot']['data'];

                    $source = fopen($imageData, 'r');
                    $destination = fopen($filename, 'w');
                    if(stream_copy_to_stream($source, $destination)) {
                        $ret = $filename;
                    }
                    fclose($source);
                    fclose($destination);
                } elseif(DEBUG) {
                    Summoner::sysLog("DEBUG invalid json data. Path ['lighthouseResult']['fullPageScreenshot']['screenshot']['data'] not found in : ".Summoner::cleanForLog($jsonData));
                }
            } elseif(DEBUG) {
                Summoner::sysLog("DEBUG curl call failed ".Summoner::cleanForLog($theCall));
            }
        } else {
            Summoner::sysLog("ERROR URL $url is empty or target $filename is not writeable.");
        }

        return $ret;
    }

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

            // DEBUG ONLY
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

            // DEBUG ONLY
            //fclose($curl_log);

            $ret = true;
        } else {
            Summoner::sysLog("ERROR URL $url is empty or target $filename is not writeable.");
        }

        return $ret;
    }
}

