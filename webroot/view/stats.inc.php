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
$_displayEditButton = false;
if(Summoner::simpleAuthCheck() === true) {
	$_displayEditButton = true;
	$moderationAmount = $Management->moderationAmount();
}

if(isset($_POST['statsDeleteLocalStorage'])) {
    if($Management->clearLocalStorage() === true) {
        $TemplateData['refresh'] = 'index.php?p=stats';
    }
    else {
        $submitFeedback['message'] = $T->t('stats.storage.clean.fail');
        $submitFeedback['status'] = 'error';
    }
}

if(isset($_POST['statsCreateDBBackup'])) {

	require_once 'lib/Mysqldump/Mysqldump.php';

	$dumpSettings = array(
		'include-tables' => array(
			DB_PREFIX.'_category',
			DB_PREFIX.'_categoryrelation',
			DB_PREFIX.'_link',
			DB_PREFIX.'_tag',
			DB_PREFIX.'_tagrelation'
		),
		'include-views' => array(
			DB_PREFIX.'_combined'
		),
		'default-character-set' => 'utf8mb4'
	);


	$backupTmpFile = tempnam(sys_get_temp_dir(),'inspid');
	try {
		$dump = new Mysqldump('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USERNAME, DB_PASSWORD, $dumpSettings);
		$dump->start($backupTmpFile);
	} catch (Exception $e) {
		echo 'mysqldump-php error: ' . $e->getMessage();
	}

	/*
    require_once 'lib/Mysqldump.php';
    $backupTmpFile = tempnam(sys_get_temp_dir(),'inspid');

    // mysqldump was modifed to make this work
    // include-views was not working while using include-tables
    $dumpSettings = array(
        'include-tables' => array(
            DB_PREFIX.'_category',
            DB_PREFIX.'_categoryrelation',
            DB_PREFIX.'_link',
            DB_PREFIX.'_tag',
            DB_PREFIX.'_tagrelation'
        ),
        'include-views' => array(
            DB_PREFIX.'_combined'
        ),
        'default-character-set' => \Ifsnop\Mysqldump\Mysqldump::UTF8MB4
    );
    $dump = new Ifsnop\Mysqldump\Mysqldump(
        'mysql:host='.DB_HOST.';dbname='.DB_NAME,
        DB_USERNAME,
        DB_PASSWORD,
        $dumpSettings
    );

    $dump->start($backupTmpFile);
	*/

    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=inspid-db-backup-full.sql");
    readfile($backupTmpFile);
    exit();
}

if(isset($_POST['statsImportXML'])) {
	$_options = array();

	if(isset($_FILES['importxmlfile']) && !empty($_FILES['importxmlfile'])) {

		$_options['overwrite'] = false;
		if(isset($_POST['importOverwrite'])) {
			$_options['overwrite'] = true;
		}

		$do = $Management->processImportFile($_FILES['importxmlfile'], $_options);
		if(isset($do['status']) && $do['status'] === 'success') {
			$submitFeedback['status'] = 'success';
			$submitFeedback['message'] = $do['message'];
		}
		else {
			$submitFeedback['message'] = $do['message'];
			$submitFeedback['status'] = 'error';
		}
	}
	else {
		$submitFeedback['message'] = $T->t('stats.import.missing.file');
		$submitFeedback['status'] = 'error';
	}
}

if(isset($_POST['statsUpdateSearchIndex'])) {

    if($Management->updateSearchIndex() === true) {
        $TemplateData['refresh'] = 'index.php?p=stats';
    }
    else {
        $submitFeedback['message'] = $T->t('stats.search.index.fail');
        $submitFeedback['status'] = 'error';
    }
}



$linkAmount = $Management->linkAmount();
$tagAmount = $Management->tagAmount();
$categoryAmount = $Management->categoryAmount();
$localStorageAmount = $Management->storageAmount();
$localStorageAmount = Summoner::humanFileSize($localStorageAmount);
