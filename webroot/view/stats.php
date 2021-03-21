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
?>
<section class="section">

	<div class="columns">
		<?php require('_headNavIcons.inc.php'); ?>
	</div>

	<div class="columns">
		<div class="column">
			<h2 class="is-size-2">Stats</h2>
		</div>
	</div>

	<?php require('_displaySubmitStatus.inc.php'); ?>
</section>

<section class="section">
	<div class="columns is-multiline">
		<div class="column is-one-quarter">
			<h4 class="is-size-4"><?php echo $T->t('view.links'); ?></h4>
			<p># <?php echo $linkAmount; ?></p>
			<p><a href="index.php?p=overview&m=all"><?php echo $T->t('stats.view.all'); ?></a></p>
		</div>
		<div class="column is-one-quarter">
			<h4 class="is-size-4"><?php echo $T->t('view.tags'); ?></h4>
			<p># <?php echo $tagAmount; ?></p>
			<p><a href="index.php?p=overview&m=tag"><?php echo $T->t('stats.view.all'); ?></a></p>
		</div>
		<div class="column is-one-quarter">
			<h4 class="is-size-4"><?php echo $T->t('view.categories'); ?></h4>
			<p># <?php echo $categoryAmount; ?></p>
			<p><a href="index.php?p=overview&m=category"><?php echo $T->t('stats.view.all'); ?></a></p>
		</div>
		<?php if($_displayEditButton === true) { ?>
		<div class="column is-one-quarter">
			<h4 class="is-size-4"><?php echo $T->t('stats.moderation'); ?></h4>
			<p># <?php echo $moderationAmount; ?></p>
			<p><a href="index.php?p=overview&m=awm"><?php echo $T->t('stats.view.all'); ?></a></p>
		</div>
		<div class="column is-one-quarter">
			<h4 class="is-size-4"><?php echo $T->t('stats.image.storage'); ?></h4>
			<p><?php echo $T->t('stats.image.storage.diskspace'); ?>: <?php echo $localStorageAmount; ?></p>
            <form method="post">
                <input type="submit" class="button is-info is-small" value="<?php echo $T->t('stats.image.delete.all'); ?>" name="statsDeleteLocalStorage">
            </form>
		</div>
        <div class="column is-one-quarter">
            <h4 class="is-size-4"><?php echo $T->t('stats.full.backup'); ?></h4>
            <p><?php echo $T->t('stats.full.backup.help'); ?></p>
            <form method="post">
                <input type="submit" class="button is-info is-small" value="<?php echo $T->t('stats.full.backup.create'); ?>" name="statsCreateDBBackup">
            </form>
        </div>
        <div class="column is-one-quarter">
            <h4 class="is-size-4"><?php echo $T->t('stats.search.index'); ?></h4>
            <p><?php echo $T->t('stats.search.index.help'); ?></p>
            <form method="post">
                <input type="submit" class="button is-info is-small" value="<?php echo $T->t('stats.search.index.update'); ?>" name="statsUpdateSearchIndex">
            </form>
        </div>
        <div class="column is-one-quarter">
            <h4 class="is-size-4"><?php echo $T->t('stats.import.xml'); ?></h4>
            <p><?php echo $T->t('stats.import.xml.help'); ?></p>
            <form method="post" enctype="multipart/form-data">
                <div class="file">
                    <label class="file-label">
                        <input class="file-input" type="file" name="importxmlfile">
                        <span class="file-cta">
                            <span class="file-icon">
                                <i class="ion-md-cloud-upload"></i>
                            </span>
                            <span class="file-label">
                                <?php echo $T->t('stats.import.xml.file'); ?>
                            </span>
                        </span>
                    </label>
                </div>
                <div class="field">
                    <label class="checkbox">
                        <input type="checkbox" value="overwrite" name="importOverwrite">
						<?php echo $T->t('stats.import.xml.overwrite'); ?>
                    </label>
                </div>
                <div class="field">
                    <input type="submit" class="button is-info is-small" value="<?php echo $T->t('stats.import.xml.import'); ?>" name="statsImportXML">
                </div>
            </form>
        </div>
		<?php } ?>
	</div>
</section>
