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
$submitFeedback = array();
$formData = array();

# very simple security check.
# can/should be extended in the future.
Summoner::simpleAuth();


if(isset($_POST['tag']) && !empty($_POST['tag']) && isset($_POST['updateTags'])) {
	$tagData = $_POST['tag'];
    $newTag = $_POST['newTag'];

    # first update then deletion and then add
    # adding a new one which matches an existing one will update it.

    $submitFeedback['message'] = array();
    $submitFeedback['status'] = 'success';

	$tagToUpdate = array();
	foreach ($tagData as $tid=>$tNewValue) {
	    $_c = Summoner::validate($tNewValue,'nospace');
	    if($_c === true) {
	        $tagToUpdate[$tid] = $tNewValue;
        }
    }

    $deleteTagData = array();
    if(isset($_POST['deleteTag'])) {
        $deleteTagData = $_POST['deleteTag'];
    }

    $tagDoNotDeleteFromUpdate = array();
	if(!empty($tagToUpdate)) {
        $submitFeedback['message'][] = $T->t('edit.tags.renamed');
	    foreach ($tagToUpdate as $k=>$v) {
            $tagObjAlternative = new Tag($DB);
            $do = $tagObjAlternative->initbystring($v,true);
            if($do === 1) { # existing
				if($k == $tagObjAlternative->getData('id')) {
					// Rename to the same. Do nothing
					continue;
				}
                // the target tag should not be removed!
                $tagDoNotDeleteFromUpdate[$tagObjAlternative->getData('id')] = $tagObjAlternative->getData('id');
                $tagObjOld = new Tag($DB);
                if(!empty($tagObjOld->initbyid($k))) {
                    $linksToUpdate = $tagObjOld->getReleations();
                    if(!empty($linksToUpdate)) {
                        foreach($linksToUpdate as $linkId) {
                            $tagObjAlternative->setRelation($linkId);
                        }
                        $tagObjOld->delete();
                    }
                }
                else {
                    $submitFeedback['message'][] = $T->t('edit.tags.rename.fail');
                    $submitFeedback['status'] = 'error';
                }
            }
            elseif ($do === 3) { # not existing one. Can be renamed
                $tagObjRename = new Tag($DB);
                if(!empty($tagObjRename->initbyid($k))) {
                    $tagObjRename->rename($v);
                }
            }
            else {
                $submitFeedback['message'][] = $T->t('edit.tags.rename.fail');
                $submitFeedback['status'] = 'error';
            }
        }
    }

	if(!empty($deleteTagData)) {
		$submitFeedback['message'][] = $T->t('edit.tags.delete');

		foreach($deleteTagData as $k=>$v) {
			if($v == "delete" && !isset($tagDoNotDeleteFromUpdate[$k])) {
				$tagObj = new Tag($DB);
				$load = $tagObj->initbyid($k);
				if($load !== false) {
					$tagObj->delete();
				}
				else {
					$submitFeedback['message'][] = $T->t('edit.tags.delete.fail');
					$submitFeedback['status'] = 'error';
				}
			}
		}
	}

	if(!empty($newTag)) {
		$submitFeedback['message'][] = $T->t('edit.tags.added');
		$tagArr = Summoner::prepareTagOrCategoryStr($newTag);

		foreach($tagArr as $c) {
			$tagObj = new Tag($DB);
			$do = $tagObj->initbystring($c);
			if($do === false) {
				$submitFeedback['message'][] = $T->t('edit.tags.add.fail');
				$submitFeedback['status'] = 'error';
			}
		}
	}
}

# show all the tags we have
$tagCollection = $Management->tags(false, true);
$subHeadline = $T->t('view.tags').' <i class="ion-md-pricetags"></i>';
