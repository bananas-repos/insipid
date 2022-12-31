<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2021 Johannes Keßler
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
$currentGetParameters['p'] = 'overview';

$_requestMode = '';
if(isset($_GET['m']) && !empty($_GET['m'])) {
	$_requestMode = trim($_GET['m']);
	$_requestMode = Summoner::validate($_requestMode,'nospace') ? $_requestMode : '';
}

$_id = '';
if(isset($_GET['id']) && !empty($_GET['id'])) {
	$_id = trim($_GET['id']);
	$_id = Summoner::validate($_id,'digit') ? $_id : '';
}

$_curPage = 1;
if(isset($_GET['page']) && !empty($_GET['page'])) {
	$_curPage = trim($_GET['page']);
	$_curPage = Summoner::validate($_curPage,'digit') ? $_curPage : 1;
}

$_sort = '';
if(isset($_GET['s']) && !empty($_GET['s'])) {
    $_sort = trim($_GET['s']);
    $_sort = Summoner::validate($_sort,'nospace') ? $_sort : '';
}

$_sortDirection = '';
if(isset($_GET['sd']) && !empty($_GET['sd'])) {
    $_sortDirection = trim($_GET['sd']);
    $_sortDirection = Summoner::validate($_sortDirection,'nospace') ? $_sortDirection : '';
}

$linkCollection = array();
$subHeadline = false;
$tagCollection = array();
$categoryCollection = array();
$pagination = array('pages' => 0);
$displayEditButton = false;
$isAwm = false;
$sortLink = array();

if(Summoner::simpleAuthCheck() === true) {
	$displayEditButton = true;
}

$sortLink['active'] = 'default';
$sortLink['activeDirection'] = false;

$_LinkColllectionQueryOptions = array(
    'limit' => RESULTS_PER_PAGE,
    'offset' =>(RESULTS_PER_PAGE * ($_curPage-1))
);

if(!empty($_sort) && $_sort === 'title') {
    $currentGetParameters['s'] = 'title';
    $sortLink['active'] = 'title';
    $_LinkColllectionQueryOptions['sort'] = 'title';
}
if(!empty($_sortDirection) && $_sortDirection === 'asc') {
    $currentGetParameters['sd'] = 'asc';
    $sortLink['activeDirection'] = true;
    $_LinkColllectionQueryOptions['sortDirection'] = 'asc';
}

switch($_requestMode) {
	case 'tag':
        $currentGetParameters['m'] = 'tag';
		if(!empty($_id)) {
            $tagObj = new Tag($DB);
            $tagObj->initbyid($_id);
            $tagname = $tagObj->getData('name');
            $subHeadline = $tagname.' <i class="ion-md-pricetag"></i>';

			$linkCollection = $Management->linksByTag($_id, $_LinkColllectionQueryOptions);

            $currentGetParameters['id'] = $_id;
		}
		else {
			# show all the tags we have
			$tagCollection = $Management->tags(0, true);
			$subHeadline = $T->t('view.tags').' <i class="ion-md-pricetags"></i>';
		}
	break;
	case 'category':
        $currentGetParameters['m'] = 'category';
		if(!empty($_id)) {
            $catObj = new Category($DB);
            $catObj->initbyid($_id);
            $catname = $catObj->getData('name');
            $subHeadline = $catname.' <i class="ion-md-filing"></i>';

			$linkCollection = $Management->linksByCategory($_id, $_LinkColllectionQueryOptions);

            $currentGetParameters['id'] = $_id;
		}
		else {
			# show all the categories we have
			$categoryCollection = $Management->categories(0, true);
			$subHeadline = $T->t('view.categories').' <i class="ion-md-filing"></i>';
		}
	break;
	case 'awm':
        $currentGetParameters['m'] = 'awm';
		Summoner::simpleAuth();
		$isAwm = true;
		$subHeadline = 'Awaiting moderation';
		$Management->setShowAwm(true);

		$linkCollection = $Management->links($_LinkColllectionQueryOptions);
	break;
	case 'all':
	default:
		# show all
		$linkCollection = $Management->links($_LinkColllectionQueryOptions);
}

if(!empty($linkCollection['amount'])) {
	$pagination['pages'] = ceil($linkCollection['amount'] / RESULTS_PER_PAGE);
	$pagination['curPage'] = $_curPage;

    $currentGetParameters['page'] = $_curPage;
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

$sortLink['default'] = Summoner::createFromParameterLinkQuery($currentGetParameters,array('s'=>false,'sd'=>false));
$sortLink['name'] = Summoner::createFromParameterLinkQuery($currentGetParameters,array('s'=>'title','sd'=>false));
$sortLink['direction'] = Summoner::createFromParameterLinkQuery($currentGetParameters,array('sd'=>'asc'));
