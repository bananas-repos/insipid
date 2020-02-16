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
 * Simple Class ImportExport used to create and process a xml file
 * Different from the complete mysql dump
 */
class ImportExport {

	/**
	 * @var String The current memory xmlwriter
	 */
	private $_currentXW;

	private $_xmlImportXSD = 'lib/xmlimport.xsd';


	/**
	 * @var
	 */
	private $_uploadedData;

	public function __construct() {
	}

	/**
	 * create a xml file for a given single link
	 * expects the array from Link->load
	 * @param array $data
	 * @return string XML string from xmlwriter
	 */
	public function createSingleLinkExportXML($data) {

		$this->_currentXW = xmlwriter_open_memory();
		xmlwriter_set_indent($this->_currentXW, 1);
		xmlwriter_set_indent_string($this->_currentXW, ' ');
		xmlwriter_start_document($this->_currentXW, '1.0', 'UTF-8');
		xmlwriter_start_element($this->_currentXW, 'root');

		xmlwriter_start_element($this->_currentXW, 'insipidlink');

		xmlwriter_start_attribute($this->_currentXW, 'id');
		xmlwriter_text($this->_currentXW, $data['id']);
		xmlwriter_end_attribute($this->_currentXW);

		xmlwriter_start_element($this->_currentXW, 'link');
		xmlwriter_start_cdata($this->_currentXW);
		xmlwriter_text($this->_currentXW, $data['link']);
		xmlwriter_end_cdata($this->_currentXW);
		xmlwriter_end_element($this->_currentXW);

		xmlwriter_start_element($this->_currentXW, 'description');
		xmlwriter_start_cdata($this->_currentXW);
		xmlwriter_text($this->_currentXW, $data['description']);
		xmlwriter_end_cdata($this->_currentXW);
		xmlwriter_end_element($this->_currentXW);

		xmlwriter_start_element($this->_currentXW, 'title');
		xmlwriter_start_cdata($this->_currentXW);
		xmlwriter_text($this->_currentXW, $data['title']);
		xmlwriter_end_cdata($this->_currentXW);
		xmlwriter_end_element($this->_currentXW);

		xmlwriter_start_element($this->_currentXW, 'hash');
		xmlwriter_start_cdata($this->_currentXW);
		xmlwriter_text($this->_currentXW, $data['hash']);
		xmlwriter_end_cdata($this->_currentXW);
		xmlwriter_end_element($this->_currentXW);

		xmlwriter_start_element($this->_currentXW, 'image');
		xmlwriter_start_cdata($this->_currentXW);
		xmlwriter_text($this->_currentXW, $data['image']);
		xmlwriter_end_cdata($this->_currentXW);
		xmlwriter_end_element($this->_currentXW);

		if(!empty($data['tags'])) {
			xmlwriter_start_element($this->_currentXW, 'tags');
			foreach($data['tags'] as $k=>$v) {
				$this->_elementFromKeyValue('tag',$k,$v);
			}
			xmlwriter_end_element($this->_currentXW);
		}

		if(!empty($data['categories'])) {
			xmlwriter_start_element($this->_currentXW, 'categories');
			foreach($data['categories'] as $k=>$v) {
				$this->_elementFromKeyValue('category',$k,$v);
			}
			xmlwriter_end_element($this->_currentXW);
		}

		xmlwriter_start_element($this->_currentXW, 'created');
		xmlwriter_start_cdata($this->_currentXW);
		xmlwriter_text($this->_currentXW, $data['created']);
		xmlwriter_end_cdata($this->_currentXW);
		xmlwriter_end_element($this->_currentXW);

		xmlwriter_start_element($this->_currentXW, 'updated');
		xmlwriter_start_cdata($this->_currentXW);
		xmlwriter_text($this->_currentXW, $data['updated']);
		xmlwriter_end_cdata($this->_currentXW);
		xmlwriter_end_element($this->_currentXW);

		xmlwriter_start_element($this->_currentXW, 'exportcreated');
		xmlwriter_start_cdata($this->_currentXW);
		xmlwriter_text($this->_currentXW, date('Y-m-d H:i:s'));
		xmlwriter_end_cdata($this->_currentXW);
		xmlwriter_end_element($this->_currentXW);

		xmlwriter_start_element($this->_currentXW, 'status');
		xmlwriter_start_cdata($this->_currentXW);
		xmlwriter_text($this->_currentXW, $data['status']);
		xmlwriter_end_cdata($this->_currentXW);
		xmlwriter_end_element($this->_currentXW);


		xmlwriter_end_element($this->_currentXW); // insipidlink

		xmlwriter_end_element($this->_currentXW); // root
		xmlwriter_end_document($this->_currentXW); // document

		return xmlwriter_output_memory($this->_currentXW);
	}

	/**
	 * @param $file array $_FILES array. Just check if everything is there
	 * and put it into _uploadedData
	 * @throws Exception
	 */
	public function loadImportFile($file) {

		if(!isset($file['name'])
			|| !isset($file['type'])
			|| !isset($file['size'])
			|| !isset($file['tmp_name'])
			|| !isset($file['error'])
		) {
			throw new Exception('Invalid Upload');
		}

		$workWith = $file['tmp_name'];
		if(!empty($workWith)) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $workWith);
			finfo_close($finfo);
			if($mime != 'text/xml') {
				throw new Exception('Invalid mime type');
			}
		} else {
			throw new Exception('Invalid file upload information');
		}

		// now validate the xml file
		$this->_uploadedData = file_get_contents($file['tmp_name']);

		if(!empty($this->_uploadedData)) {
			$_valid = $this->_validateXMLImport();
			if($_valid !== true) {
				$this->_uploadedData = '';
				throw new Exception('Invalid xml format: '.$_valid);
			}
		}
		else {
			$this->_uploadedData = '';
			throw new Exception('Empty upload file?');
		}
	}

	/**
	 * parse the data from _uploadedData and create an array we can use
	 * @return array
	 * @throws Exception
	 */
	public function parseImportFile() {
		$ret = array();

		if(!empty($this->_uploadedData)) {
			$xml = simplexml_load_string($this->_uploadedData, "SimpleXMLElement", LIBXML_NOCDATA);
			if(!empty($xml->insipidlink)) {
				foreach($xml->insipidlink as $linkEntry) {
					$_id = (string)$linkEntry->attributes()->id;
					$ret[$_id]['id'] = $_id;
					$ret[$_id]['link'] = (string)$linkEntry->link;
					$ret[$_id]['description'] = (string)$linkEntry->description;
					$ret[$_id]['title'] = (string)$linkEntry->title;
					$ret[$_id]['hash'] = (string)$linkEntry->hash;
					$ret[$_id]['created'] = (string)$linkEntry->created;
					$ret[$_id]['updated'] = (string)$linkEntry->updated;
					$ret[$_id]['private'] = (string)$linkEntry->status;
					$ret[$_id]['image'] = (string)$linkEntry->image;

					if($linkEntry->categories->count() > 0) {
						$ret[$_id]['category'] = '';
						foreach ($linkEntry->categories->category as $cat) {
							$_cname = (string)$cat;
							$ret[$_id]['category'] .= $_cname.",";
						}
					}

					if($linkEntry->tags->count() > 0) {
						$ret[$_id]['tag'] = '';
						foreach ($linkEntry->tags->tag as $tag) {
							$_tname = (string)$tag;
							$ret[$_id]['tag'] .= $_tname.",";
						}
					}
				}
			}
		}
		else {
			throw new Exception('Empty xml data. LoadImportFile needs to be called first.');
		}

		return $ret;
	}

	/**
	 * Create a single xml element for the current loaded xmlwriter
	 * @param String $name
	 * @param String $key
	 * @param String $value
	 */
	private function _elementFromKeyValue($name, $key, $value) {

		if(!empty($key) && !empty($value) && !empty($name)) {
			xmlwriter_start_element($this->_currentXW, $name);

			xmlwriter_start_attribute($this->_currentXW, 'id');
			xmlwriter_text($this->_currentXW, $key);
			xmlwriter_end_attribute($this->_currentXW);

			xmlwriter_start_cdata($this->_currentXW);
			xmlwriter_text($this->_currentXW, $value);
			xmlwriter_end_cdata($this->_currentXW);

			xmlwriter_end_element($this->_currentXW);
		}
	}

	/**
	 * validate an import of a export xml with the
	 * saved xsd file _xmlImportXSD
	 * @return bool|string
	 */
	private function _validateXMLImport() {
		$ret = false;
		$xmlReader = new XMLReader();
		$xmlReader->XML($this->_uploadedData);
		if(!empty($xmlReader)) {
			$xmlReader->setSchema($this->_xmlImportXSD);
			libxml_use_internal_errors(true);
			while($xmlReader->read()) {
				if (!$xmlReader->isValid()) {
					$ret = $this->_xmlErrors();
					break;
				} else {
					$ret = true;
					break;
				}
			}
		}

		return $ret;
	}

	/**
	 * Reads libxml_get_errors and creates a simple string with all
	 * the info we need.
	 * @return string
	 */
	private function _xmlErrors() {
		$errors = libxml_get_errors();
		$result = array();
		foreach ($errors as $error) {
			$errorString = "Error $error->code in $error->file (Line:{$error->line}):";
			$errorString .= trim($error->message);
			$result[] = $errorString;
		}
		libxml_clear_errors();
		return implode("\n",$result);
	}
}
