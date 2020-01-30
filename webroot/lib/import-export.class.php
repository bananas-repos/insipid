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

class ImportExport {

	private $_currentXW;

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


		xmlwriter_end_element($this->_currentXW); // insipidlink
		xmlwriter_end_document($this->_currentXW); // document

		return xmlwriter_output_memory($this->_currentXW);
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
}
