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
$submitFeedback = false;
$formData = false;

# very simple security check.
# can/should be extended in the future.
Summoner::simpleAuth();


if(isset($_POST['category']) && !empty($_POST['category']) && isset($_POST['updateCategories'])) {
	$categoryData = $_POST['category'];
    $newCategory = $_POST['newCategory'];

    # first update then deletion and then add
    # adding a new one which matches an existing one will update it.

    $submitFeedback['message'] = array();
    $submitFeedback['status'] = 'success';

    $catToUpdate = array();
    foreach ($categoryData as $cid=>$cNewValue) {
        $_c = Summoner::validate($cNewValue,'nospace');
        if($_c === true) {
            $catToUpdate[$cid] = $cNewValue;
        }
    }

	$deleteCategoryData = array();
	if(isset($_POST['deleteCategory'])) {
		$deleteCategoryData = $_POST['deleteCategory'];
	}

    $catDoNotDeleteFromUpdate = array();
    if(!empty($catToUpdate)) {
        $submitFeedback['message'][] = $T->t('edit.category.renamed');
        foreach ($catToUpdate as $k=>$v) {
            $catObjAlternative = new Category($DB);
            $do = $catObjAlternative->initbystring($v,true);
            if($do === 1) { # existing
                // the target cat should not be removed!
                $catDoNotDeleteFromUpdate[$catObjAlternative->getData('id')] = $catObjAlternative->getData('id');
                $catObjOld = new Category($DB);
                if(!empty($catObjOld->initbyid($k))) {
                    $linksToUpdate = $catObjOld->getReleations();
                    if(!empty($linksToUpdate)) {
                        foreach($linksToUpdate as $linkId) {
                            $catObjAlternative->setRelation($linkId);
                        }
                        $catObjOld->delete();
                    }
                }
                else {
                    $submitFeedback['message'][] = $T->t('edit.category.rename.fail');
                    $submitFeedback['status'] = 'error';
                }
            }
            elseif ($do === 3) { # not existing one. Can be renamed
                $catObjRename = new Category($DB);
                if(!empty($catObjRename->initbyid($k))) {
                    $catObjRename->rename($v);
                }
            }
            else {
                $submitFeedback['message'][] = $T->t('edit.category.rename.fail');
                $submitFeedback['status'] = 'error';
            }
        }
    }

	if(!empty($deleteCategoryData)) {
		$submitFeedback['message'][] = $T->t('edit.category.deleted');

		foreach($deleteCategoryData as $k=>$v) {
            if($v == "delete" && !isset($catDoNotDeleteFromUpdate[$k])) {
				$catObj = new Category($DB);
				$load = $catObj->initbyid($k);
				if($load !== false) {
					$catObj->delete();
				}
				else {
					$submitFeedback['message'][] = $T->t('edit.category.delete.fail');
					$submitFeedback['status'] = 'error';
				}
			}
		}
	}

	if(!empty($newCategory)) {
		$submitFeedback['message'][] = $T->t('edit.category.added');
		$catArr = Summoner::prepareTagOrCategoryStr($newCategory);

		foreach($catArr as $c) {
			$catObj = new Category($DB);
			$do = $catObj->initbystring($c);
			if($do === false) {
				$submitFeedback['message'][] = $T->t('edit.category.add.fail');
				$submitFeedback['status'] = 'error';
			}
		}
	}
}

# show all the categories we have
$categoryCollection = $Management->categories(false, true);
$subHeadline = $T->t('view.categories').' <i class="ion-md-filing"></i>';
