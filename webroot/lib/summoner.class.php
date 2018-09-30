<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2018 Johannes Keßler
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

			case 'rights':
				return self::isRightsString($input);
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

		#if($input === $value) {
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
	static function is_utf8 ( $string ) {
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
	 * execute a curl call to the fiven $url
	 * @param string $curl The request url
	 */
	static function curlCall($url,$port=false) {
		$ret = false;

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		if(!empty($port)) {
		  curl_setopt($ch, CURLOPT_PORT, $port);
		}

		$do = curl_exec($ch);

		if(is_string($do) === true) {
			$ret = $do;
		}
		else {
			$ret = false;
			error_log(var_export(curl_error($ch),true));
		}

		curl_close($ch);

		return $ret;
	}

	/**
	 * check if a string strts with a given string
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
	 *
	 * this only works with arrays and checking if the key is there and echo/return it.
	 *
	 * http://php.net/manual/en/migration70.new-features.php#migration70.new-features.null-coalesce-op
	 */

	static function ifset($array,$key) {
	    return isset($array[$key]) ? $array[$key] : false;
	}

	/**
	 * try to gather meta information from given URL
	 * @param string $url
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
	 * get as much as possible solcial meta infos from given string
	 * the string is usually a HTML source
	 * @param string $string
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
	        $titleDom = $xpath->query('//html/head/title');
	        $mediaInfos['title'] = $titleDom->item(0)->nodeValue;
	    }

	    return $mediaInfos;
	}

	/**
	 * at creation a category or tag can be a string with multiple values.
	 * seperated with space or ,
	 * category and tag is a single string without any seperators
	 *
	 * @param string $string
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
	 * extract from given string (was email body) any links we want to add
	 * should be in the right format
	 * return an array with links and the infos about them
	 *
	 * new-absolute-link|multiple,category,strings|multiple,tag,strings\n
	 *
	 * @param string $string
	 * @return array $ret
	 */
	static function extractEmailLinks($string) {
	    $ret = array();

	    #this matches a valid URL. An URL with | is still valid...
	    $urlpattern  = '#(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))#';

	    preg_match_all($urlpattern, $string, $matches);
	    if(isset($matches[0]) && !empty($matches[0])) {
	        foreach($matches[0] as $match) {
	            $ret[md5($match)] = $match;
	        }
	    }


	    return $ret;
	}
}

?>
