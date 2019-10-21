<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2019 Johannes Keßler
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
$_requestMode = false;
if(isset($_GET['m']) && !empty($_GET['m'])) {
	$_requestMode = trim($_GET['m']);
	$_requestMode = Summoner::validate($_requestMode,'nospace') ? $_requestMode : "all";
}

$_id = false;
if(isset($_GET['id']) && !empty($_GET['id'])) {
	$_id = trim($_GET['id']);
	$_id = Summoner::validate($_id,'digit') ? $_id : false;
}
$_curPage = 1;
if(isset($_GET['page']) && !empty($_GET['page'])) {
	$_curPage = trim($_GET['page']);
	$_curPage = Summoner::validate($_curPage,'digit') ? $_curPage : 1;
}

$linkCollection = array();
$subHeadline = false;
$tagCollection = array();
$categoryCollection = array();
$pagination = array('pages' => 0);

$_displayEditButton = false;
if(Summoner::simpleAuthCheck() === true) {
	$_displayEditButton = true;
}

switch($_requestMode) {
	case 'tag':
		if(!empty($_id)) {
			$linkCollection = $Management->linksByTag($_id,false,RESULTS_PER_PAGE, (RESULTS_PER_PAGE * ($_curPage-1)));
			if(!empty($linkCollection['results'])) {
				$tagObj = new Tag($DB);
				$tagObj->initbyid($_id);
				$tagname = $tagObj->getData('name');
				$subHeadline = $tagname.' <i class="ion-md-pricetag"></i>';
			}
		}
		else {
			# show all the tags we have
			$tagCollection = $Management->tags(false, true);
			$subHeadline = 'All the tags <i class="ion-md-pricetags"></i>';
		}
	break;
	case 'category':
		if(!empty($_id)) {
			$linkCollection = $Management->linksByCategory($_id,false,RESULTS_PER_PAGE, (RESULTS_PER_PAGE * ($_curPage-1)));
			if(!empty($linkCollection['results'])) {
				$catObj = new Category($DB);
				$catObj->initbyid($_id);
				$catname = $catObj->getData('name');
				$subHeadline = $catname.' <i class="ion-md-filing"></i>';
			}
		}
		else {
			# show all the categories we have
			$categoryCollection = $Management->categories(false, true);
			$subHeadline = 'All the categories <i class="ion-md-filing"></i>';
		}
	break;
	case 'all':
	default:
		# show all
		$linkCollection = $Management->links(RESULTS_PER_PAGE, (RESULTS_PER_PAGE * ($_curPage-1)));
}
if(!empty($linkCollection['amount'])) {
	$pagination['pages'] = ceil($linkCollection['amount'] / RESULTS_PER_PAGE);
	$pagination['curPage'] = $_curPage;
	$pagination['linkadd'] = '&m='.$_requestMode;
	if(!empty($_id)) {
		$pagination['linkadd'] .= '&id='.$_id;
	}
}

if($pagination['pages'] > 11) {
	# first pages
	$pagination['visibleRange'] = range(1,3);
	# last pages
	foreach(range($pagination['pages']-2, $pagination['pages']) as $e) {
		array_push($pagination['visibleRange'], $e);
	}
	# pages before and after current page
	$cRange = range($pagination['curPage']-1, $pagination['curPage']+1);
	foreach($cRange as $e) {
		array_push($pagination['visibleRange'], $e);
	}
	$pagination['currentRangeStart'] = array_shift($cRange);
	$pagination['currentRangeEnd'] = array_pop($cRange);
}
else {
	$pagination['visibleRange'] = range(1,$pagination['pages']);
}
