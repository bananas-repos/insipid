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
	 * @param mixed $limit If int given the string is checked for length
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
	static function validate($input,$mode='text',$limit=false) {
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
				$pattern = '/[\p{L}\p{N}\p{Po}\-]/u';
			break;

			case 'digit':
				// only numbers and digit
				// warning with negative numbers...
				$pattern = '/[\p{N}\-]/';
			break;

			case 'pageTitle':
				// text with whitespace and without special chars
				// but with Punctuation
				$pattern = '/[\p{L}\p{N}\p{Po}\p{Z}\s-]/u';
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
	 * return if the given string is utf8
	 * http://php.net/manual/en/function.mb-detect-encoding.php
	 *
	 * @param string $string
	 * @return number
	 */
	static function is_utf8($string) {
	   // From http://w3.org/International/questions/qa-forms-utf-8.html
	   return preg_match('%^(?:
			 [\x09\x0A\x0D\x20-\x7E]            # ASCII
		   | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
		   |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
		   | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
		   |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
		   |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
		   | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
		   |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
	   )*$%xs', $string);
	}

	/**
	 * execute a curl call to the given $url
	 * @param string $url The request url
	 * @param bool $port
	 * @return bool|mixed
	 */
	static function curlCall($url,$port=false) {
		$ret = false;

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
		curl_setopt($ch, CURLOPT_USERAGENT,self::BROWSER_AGENT_STRING);

		// curl_setopt($ch, CURLOPT_VERBOSE, true);
		//curl_setopt($ch, CURLOPT_HEADER, true);

		if(!empty($port)) {
		  curl_setopt($ch, CURLOPT_PORT, $port);
		}

		$do = curl_exec($ch);

		if(is_string($do) === true) {
			$ret = $do;
		}
		else {
			error_log('ERROR '.var_export(curl_error($ch),true));
		}

		curl_close($ch);

		return $ret;
	}

	/**
	 * Download given url to given file
	 * @param $url
	 * @param $whereToStore
	 * @param bool $port
	 * @return bool
	 */
	static function downloadFile($url, $whereToStore, $port=false) {
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
	 * check if a string starts with a given string
	 *
	 * @param string $haystack
	 * @param string $needle
	 * @return boolean
	 */
	static function startsWith($haystack, $needle) {
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}

	/**
	 * check if a string ends with a given string
	 *
	 * @param string $haystack
	 * @param string $needle
	 * @return boolean
	 */
	static function endsWith($haystack, $needle) {
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}
		return (substr($haystack, -$length) === $needle);
	}


	/**
	 * simulate the Null coalescing operator in php5
	 * this only works with arrays and checking if the key is there and echo/return it.
	 * http://php.net/manual/en/migration70.new-features.php#migration70.new-features.null-coalesce-op
	 *
	 * @param $array
	 * @param $key
	 * @return bool
	 */
	static function ifset($array,$key) {
		return isset($array[$key]) ? $array[$key] : false;
	}

	/**
	 * try to gather meta information from given URL
	 * @param string $url
	 * @return array|bool
	 */
	static function gatherInfoFromURL($url) {
		$ret = false;

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
	 * @param string $string
	 * @return array
	 */
	static function socialMetaInfos($string) {
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
	static function prepareTagOrCategoryStr($string) {
		$ret = array();

		$string = trim($string, ", ");
		if(strstr($string, ",")) {
			$_t = explode(",", $string);
			foreach($_t as $new) {
				$ret[$new] = $new;
			}
			unset($_t);
			unset($new);

			foreach($ret as $e) {
				if(strstr($e, " ")) {
					unset($ret[$e]);
					$_t = explode(" ", $e);
					foreach($_t as $new) {
						$new = trim($new);
						if(!empty($new)) {
							$ret[$new] = $new;
						}
					}
				}
			}
		}
		else {
			$_t = explode(" ", $string);
			foreach($_t as $new) {
				$new = trim($new);
				if(!empty($new)) {
				   $ret[$new] = $new;
				}
			}
		}


		return $ret;
	}

	/**
	 * a very simple HTTP_AUTH authentication.
	 */
	static function simpleAuth() {
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
	 * @see Summoner::simpleAuth to trigger the auth
	 * @return bool
	 */
	static function simpleAuthCheck() {
		if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])
			&& $_SERVER['PHP_AUTH_USER'] === FRONTEND_USERNAME && $_SERVER['PHP_AUTH_PW'] === FRONTEND_PASSWORD
		) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if in the given urlstring a scheme is existent. If not add http:// to it
	 * @param $urlString
	 * @return string
	 */
	static function addSchemeToURL($urlString) {
		$ret = $urlString;

		if(empty(parse_url($ret, PHP_URL_SCHEME))) {
			$ret = "http://".$ret;
		}

		return $ret;
	}

    /**
     * retrieve the folder size with its children of given folder path
     * @param $folder
     * @return false|int
     */
	static function folderSize($folder) {
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
     * @param $size
     * @param string $unit
     * @return string
     */
	static function  humanFileSize($size,$unit="") {
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
    static function recursive_remove_directory($directory,$empty=false,$fTime=0) {
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
                        if($fTime !== false && is_int($fTime)) {
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
     * @param $array
     * @param bool $modify
     * @return string
     */
    static function createFromParameterLinkQuery($array,$modify=false) {
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
	 * Simple helper to detect the $_FILES upload status
	 * Expects the error value from $_FILES['error']
	 * @param $error
	 * @return array
	 */
	static function checkFileUploadStatus($error) {
		$message = "Unknown upload error";
		$status = false;

		switch ($error) {
			case UPLOAD_ERR_OK:
				$message = "There is no error, the file uploaded with success.";
				$status = true;
				break;
			case UPLOAD_ERR_INI_SIZE:
				$message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
				break;
			case UPLOAD_ERR_PARTIAL:
				$message = "The uploaded file was only partially uploaded";
				break;
			case UPLOAD_ERR_NO_FILE:
				$message = "No file was uploaded";
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$message = "Missing a temporary folder";
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$message = "Failed to write file to disk";
				break;
			case UPLOAD_ERR_EXTENSION:
				$message = "File upload stopped by extension";
				break;
		}

		return array(
			'message' => $message,
			'status' => $status
		);
	}
}
