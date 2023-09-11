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
 * a static helper class
 */
class Summoner {

    private const BROWSER_AGENT_STRING = 'Mozilla/5.0 (X11; Linux x86_64; rv:68.0) Gecko/20100101 Firefox/68.0';

    /**
     * validate the given string with the given type. Optional check the string
     * length
     *
     * @param string $input The string to check
     * @param string $mode How the string should be checked
     * @param int $limit If int given the string is checked for length
     *
     * @see http://de.php.net/manual/en/regexp.reference.unicode.php
     * http://www.sql-und-xml.de/unicode-database/#pc
     *
     * the pattern replaces all that is allowed. the correct result after
     * the replace should be empty, otherwise are there chars which are not
     * allowed
     *
     * @return bool
     */
    static function validate(string $input, string $mode='text', int $limit=0): bool {
        // check if we have input
        $input = trim($input);

        if($input == "") return false;

        $ret = false;

        switch ($mode) {
            case 'mail':
                if(filter_var($input,FILTER_VALIDATE_EMAIL) === $input) {
                    return true;
                }
                else {
                    return false;
                }
            break;

            case 'url':
                if(filter_var($input,FILTER_VALIDATE_URL) === $input) {
                    return true;
                }
                else {
                    return false;
                }
            break;

            case 'nospace':
                // text without any whitespace and special chars
                $pattern = '/[\p{L}\p{N}]/u';
            break;

            case 'nospaceP':
                // text without any whitespace and special chars
                // but with Punctuation other
                # http://www.sql-und-xml.de/unicode-database/po.html
                $pattern = '/[\p{L}\p{N}\p{Po}\-_]/u';
            break;

            case 'digit':
                // only numbers and digit
                // warning with negative numbers...
                $pattern = '/[\p{N}\-]/';
            break;

            case 'pageTitle':
                // text with whitespace and without special chars
                // but with Punctuation
                $pattern = '/[\p{L}\p{N}\p{Po}\p{Z}\s\-_]/u';
            break;

            # strange. the \p{M} is needed.. don't know why..
            case 'filename':
                $pattern = '/[\p{L}\p{N}\p{M}\-_\.\p{Zs}]/u';
            break;

            case 'text':
            default:
                $pattern = '/[\p{L}\p{N}\p{P}\p{S}\p{Z}\p{M}\s]/u';
        }

        $value = preg_replace($pattern, '', $input);

        if($value === "") {
            $ret = true;
        }

        if(!empty($limit)) {
            # isset starts with 0
            if(isset($input[$limit])) {
                # too long
                $ret = false;
            }
        }

        return $ret;
    }


    /**
     * execute a curl call to the given $url
     *
     * @param string $url The request url
     * @param int $port
     * @return string
     */
    static function curlCall(string $url, int $port=0): string {
        $ret = '';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        curl_setopt($ch, CURLOPT_USERAGENT,self::BROWSER_AGENT_STRING);

        // curl_setopt($ch, CURLOPT_VERBOSE, true);
        // curl_setopt($ch, CURLOPT_HEADER, true);

        if(!empty($port)) {
          curl_setopt($ch, CURLOPT_PORT, $port);
        }

        $do = curl_exec($ch);

        if(is_string($do) === true) {
            $ret = $do;
        }
        else {
            self::sysLog('ERROR '.var_export(curl_error($ch),true));
        }

        curl_close($ch);

        return $ret;
    }

    /**
     * Download given url to given file
     *
     * @param string $url
     * @param string $whereToStore
     * @param int $port
     * @return bool
     */
    static function downloadFile(string $url, string $whereToStore, int $port=0): bool {
        $fh = fopen($whereToStore, 'w+');

        $ret = false;

        if($fh !== false) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FILE, $fh);

            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
            curl_setopt($ch, CURLOPT_USERAGENT, self::BROWSER_AGENT_STRING);

            if(!empty($port)) {
                curl_setopt($ch, CURLOPT_PORT, $port);
            }
            curl_exec($ch);
            curl_close($ch);

            $ret = true;
        }

        fclose($fh);

        return $ret;
    }

    /**
     * simulate the Null coalescing operator in php5
     * this only works with arrays and checking if the key is there and echo/return it.
     * http://php.net/manual/en/migration70.new-features.php#migration70.new-features.null-coalesce-op
     *
     * @param array $array
     * @param string $key
     * @return mixed
     */
    static function ifset(array $array, string $key): mixed {
        return $array[$key] ?? false;
    }

    /**
     * try to gather meta information from given URL
     *
     * @param string $url
     * @return array
     */
    static function gatherInfoFromURL(string $url): array {
        $ret = array();

        if(self::validate($url,'url')) {
            $data = self::curlCall($url);
            if(!empty($data)) {
                $ret = self::socialMetaInfos($data);
            }
        }

        return $ret;
    }

    /**
     * get as much as possible social meta infos from given string
     * the string is usually a HTML source
     *
     * @param string $string
     * @return array
     */
    static function socialMetaInfos(string $string): array {
        #http://www.w3bees.com/2013/11/fetch-facebook-og-meta-tags-with-php.html
        #http://www.9lessons.info/2014/01/social-meta-tags-for-google-twitter-and.html
        #http://ogp.me/
        #https://moz.com/blog/meta-data-templates-123

        $dom = new DomDocument;
        # surpress invalid html warnings
        @$dom->loadHTML($string);

        $xpath = new DOMXPath($dom);
        $metas = $xpath->query('//*/meta');

        $mediaInfos = array();

        # meta tags
        foreach($metas as $meta) {
            if($meta->getAttribute('property')) {
                $prop = $meta->getAttribute('property');
                $prop = mb_strtolower($prop);

                # minimum required information
                # http://ogp.me/#metadata
                if($prop == "og:title") {

                    $mediaInfos['title'] = $meta->getAttribute('content');
                }
                elseif($prop == "og:image") {
                    $mediaInfos['image'] = $meta->getAttribute('content');
                }
                elseif($prop == "og:url") {
                    $mediaInfos['link'] = $meta->getAttribute('content');
                }
                elseif($prop == "og:description") {
                    $mediaInfos['description'] = $meta->getAttribute('content');
                }
            }
            elseif($meta->getAttribute('name')) {
                $name = $meta->getAttribute('name');
                $name = mb_strtolower($name);

                # twitter
                # https://dev.twitter.com/cards/overview

                if($name == "twitter:title") {
                    $mediaInfos['title'] = $meta->getAttribute('content');
                }
                elseif($name == "twitter:description") {
                    $mediaInfos['description'] = $meta->getAttribute('content');
                }
                elseif($name == "twitter:image") {
                    $mediaInfos['image'] = $meta->getAttribute('content');
                }
                elseif($name == "description") {
                    $mediaInfos['description'] = $meta->getAttribute('content');
                }

            }
            elseif($meta->getAttribute('itemprop')) {
                $itemprop = $meta->getAttribute('itemprop');
                $itemprop = mb_strtolower($itemprop);

                # google plus
                if($itemprop == "name") {
                    $mediaInfos['title'] = $meta->getAttribute('content');
                }
                elseif($itemprop == "description") {
                    $mediaInfos['description'] = $meta->getAttribute('content');
                }
                elseif($itemprop == "image") {
                    $mediaInfos['image'] = $meta->getAttribute('content');
                }

            }
        }


        if(!isset($mediaInfos['title'])) {
            $titleDom = $xpath->query('//title');
            $mediaInfos['title'] = $titleDom->item(0)->nodeValue;
        }

        return $mediaInfos;
    }

    /**
     * at creation a category or tag can be a string with multiple values.
     * separated with space or ,
     * category and tag is a single string without any separators
     *
     * @param string $string
     * @return array
     */
    static function prepareTagOrCategoryStr(string $string): array {
        $ret = array();
        $_ret = array();

        $string = trim($string, ", ");
        if(strstr($string, ",")) {
            $_t = explode(",", $string);
            foreach($_t as $n) {
                $_ret[$n] = $n;
            }
            unset($_t);
            unset($n);

            foreach($_ret as $e) {
                if(strstr($e, " ")) {
                    unset($ret[$e]);
                    $_t = explode(" ", $e);
                    foreach($_t as $new) {
                        $new = trim($new);
                        $_c = self::validate($new,'nospace');
                        if(!empty($new) && $_c === true) {
                            $ret[$new] = $new;
                        }
                    }
                }
                else {
                    $new = trim($e);
                    $_c = self::validate($new,'nospace');
                    if(!empty($new) && $_c === true) {
                        $ret[$new] = $new;
                    }
                }
            }
        }
        else {
            $_t = explode(" ", $string);
            foreach($_t as $new) {
                $new = trim($new);
                $_c = self::validate($new,'nospace');
                if(!empty($new) && $_c === true) {
                   $ret[$new] = $new;
                }
            }
        }


        return $ret;
    }

    /**
     * a very simple HTTP_AUTH authentication.
     */
    static function simpleAuth(): void {
        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])
            || $_SERVER['PHP_AUTH_USER'] !== FRONTEND_USERNAME || $_SERVER['PHP_AUTH_PW'] !== FRONTEND_PASSWORD
            ) {
            header('WWW-Authenticate: Basic realm="Insipid edit area"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'No Access.';
            exit;
        }
    }

    /**
     * check if we have a valid auth. Nothing more.
     *
     * @see Summoner::simpleAuth to trigger the auth
     * @return bool
     */
    static function simpleAuthCheck(): bool {
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])
            && $_SERVER['PHP_AUTH_USER'] === FRONTEND_USERNAME && $_SERVER['PHP_AUTH_PW'] === FRONTEND_PASSWORD
        ) {
            return true;
        }

        return false;
    }

    /**
     * Checks if in the given urlstring a scheme is existent. If not add http:// to it
     *
     * @param string $urlString
     * @return string
     */
    static function addSchemeToURL(string $urlString): string {
        $ret = $urlString;

        if(empty(parse_url($ret, PHP_URL_SCHEME))) {
            $ret = "http://".$ret;
        }

        return $ret;
    }

    /**
     * retrieve the folder size with its children of given folder path
     *
     * @param string $folder
     * @return int
     */
    static function folderSize(string $folder): int {
        $ret = 0;

        if(file_exists($folder) && is_readable($folder)) {
            foreach (glob(rtrim($folder, '/') . '/*', GLOB_NOSORT) as $each) {
                $ret += is_file($each) ? filesize($each) : self::folderSize($each);
            }
        }

        return $ret;
    }

    /**
     * Calculate the given byte size in more human readable format.
     *
     * @param integer $size
     * @param string $unit
     * @return string
     */
    static function humanFileSize(int $size, string $unit=""): string {
        $ret =  number_format($size)." bytes";

        if((!$unit && $size >= 1<<30) || $unit == "GB") {
            $ret = number_format($size / (1 << 30), 2)."GB";
        }
        elseif((!$unit && $size >= 1<<20) || $unit == "MB") {
            $ret = number_format($size / (1 << 20), 2) . "MB";
        }
        elseif( (!$unit && $size >= 1<<10) || $unit == "KB") {
            $ret = number_format($size / (1 << 10), 2) . "KB";
        }

        return $ret;
    }

    /**
     * delete and/or empty a directory
     *
     * $empty = true => empty the directory but do not delete it
     *
     * @param string $directory
     * @param boolean $empty
     * @param int $fTime If not false remove files older then this value in sec.
     * @return boolean
     */
    static function recursive_remove_directory(string $directory, bool $empty=false, int $fTime=0): bool {
        if(substr($directory,-1) == '/') {
            $directory = substr($directory,0,-1);
        }

        if(!file_exists($directory) || !is_dir($directory)) {
            return false;
        }
        elseif(!is_readable($directory)) {
            return false;
        }
        else {
            $handle = opendir($directory);

            // and scan through the items inside
            while (false !== ($item = readdir($handle))) {
                if($item[0] != '.') {
                    $path = $directory.'/'.$item;

                    if(is_dir($path)) {
                        recursive_remove_directory($path);
                    }
                    else {
                        if(!empty($fTime) && is_int($fTime)) {
                            $ft = filemtime($path);
                            $offset = time()-$fTime;
                            if($ft <= $offset) {
                                unlink($path);
                            }
                        }
                        else {
                            unlink($path);
                        }
                    }
                }
            }
            closedir($handle);

            if($empty === false) {
                if(!rmdir($directory)) {
                    return false;
                }
            }
            return true;
        }
    }

    /**
     * http_build_query with modify array
     * modify will add: key AND value not empty
     * modify will remove: only key with no value
     *
     * @param array $array
     * @param array $modify
     * @return string
     */
    static function createFromParameterLinkQuery(array $array, array $modify=array()): string {
        $ret = '';

        if(!empty($modify)) {
            foreach($modify as $k=>$v) {
                if(empty($v)) {
                    unset($array[$k]);
                }
                else {
                    $array[$k] = $v;
                }
            }
        }

        if(!empty($array)) {
            $ret = http_build_query($array);
        }

        return $ret;
    }

    /**
     * Make the input more safe for logging
     *
     * @param string $input The string to be made more safe
     * @return string
     */
    static function cleanForLog(string $input): string {
        $input = var_export($input, true);
        $input = preg_replace( "/[\t\n\r]/", " ", $input);
        return addcslashes($input, "\000..\037\177..\377\\");
    }

    /**
     * error_log with a dedicated destination
     * Uses LOGFILE const
     *
     * @param string $msg The string to be written to the log
     */
    static function sysLog(string $msg): void {
        error_log(date("c")." ".$msg."\n", 3, LOGFILE);
    }
}
