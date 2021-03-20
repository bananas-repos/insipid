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
<?php require('_displaySubmitStatus.inc.php'); ?>

	<div class="columns">
		<?php require('_headNavIcons.inc.php'); ?>
	</div>

	<div class="columns">
		<div class="column">
			<h1 class="is-size-2"><?php echo $linkData['title']; ?></h1>
			<h3><a href="index.php?p=linkinfo&id=<?php echo Summoner::ifset($formData, 'hash'); ?>">
				<i class="icon ion-md-return-left"></i></a></h3>
		</div>
	</div>
</section>

<section class="section">

	<form method="post" autocomplete="off">
		<div class="columns">
			<div class="column is-one-quarter">
				<p><?php echo $T->t('view.date.added'); ?></p>
			</div>
			<div class="column">
				<p>
					<?php echo $linkData['created']; ?>
					(<?php echo $T->t('edit.link.last.update'); ?> <?php echo $linkData['updated']; ?>)
				</p>
			</div>
		</div>
		<div class="columns">
			<div class="column is-one-quarter">
				<p><?php echo $T->t('view.title'); ?></p>
			</div>
			<div class="column">
				<input class="input" type="text" name="data[title]" value="<?php echo Summoner::ifset($formData, 'title'); ?>" />
			</div>
		</div>
		<div class="columns">
			<div class="column is-one-quarter">
				<p><?php echo $T->t('view.description'); ?></p>
			</div>
			<div class="column">
				<input class="input" type="text" name="data[description]" value="<?php echo Summoner::ifset($formData, 'description'); ?>" />
			</div>
		</div>
		<div class="columns">
			<div class="column is-one-quarter">
				<p><?php echo $T->t('view.url'); ?></p>
			</div>
			<div class="column">
				<p><a href="<?php echo $linkData['link']; ?>" target="_blank"><?php echo $linkData['link']; ?></a></p>
			</div>
		</div>
		<div class="columns">
			<div class="column is-one-quarter">
				<p>
					<?php echo $T->t('view.image'); ?>: (<small><?php echo $T->t('view.image.provided'); ?></small>)
				</p>
			</div>
			<div class="column">
				<p>
					<img class="linkthumbnail" src="<?php echo $linkData['imageToShow']; ?>" alt="<?php echo $T->t('view.image.provided'); ?>">
				</p>
				<input class="input" type="text" name="data[image]" value="<?php echo Summoner::ifset($formData, 'image'); ?>" /><br />
				<br />
				<label class="checkbox">
					<input type="checkbox" name="data[localImage]" value="1" <?php if(Summoner::ifset($formData, 'localImage')) echo "checked"; ?> />
					<?php echo $T->t('edit.link.image.save'); ?>
				</label>
			</div>
		</div>
		<div class="columns">
			<div class="column is-one-quarter">
				<p>
					<?php echo $T->t('edit.link.thumbnail.webpage'); ?>
				</p>
			</div>
			<div class="column">
				<?php if(isset($linkData['snapshotLink'])) { ?>
				<p><a href="<?php echo $linkData['snapshotLink']; ?>" target="_blank"><?php echo $T->t('edit.link.thumbnail.view'); ?></a></p>
				<?php } ?>
				<label class="checkbox">
					<input type="checkbox" name="data[snapshot]" value="1" <?php if(Summoner::ifset($formData, 'snapshot')) echo "checked"; ?>  />
					<?php echo $T->t('edit.link.thumbnail.save'); ?>
				</label>
			</div>
		</div>
		<?php if(defined('WKHTMLTOPDF_USE') && WKHTMLTOPDF_USE === true) { ?>
		<div class="columns">
			<div class="column is-one-quarter">
				<p>
					<?php echo $T->t('edit.link.full.screenshot'); ?>
				</p>
			</div>
			<div class="column">
				<?php if(isset($linkData['pagescreenshotLink'])) { ?>
				<p><a href="<?php echo $linkData['pagescreenshotLink']; ?>" target="_blank"><?php echo $T->t('edit.link.full.screenshot.view'); ?></a></p>
				<?php } ?>
				<label class="checkbox">
					<input type="checkbox" name="data[pagescreenshot]" value="1" <?php if(Summoner::ifset($formData, 'pagescreenshot')) echo "checked"; ?>  />
					<?php echo $T->t('edit.link.full.screenshot.save'); ?>
				</label>
			</div>
		</div>
		<?php } ?>
        <div class="columns">
            <div class="column is-one-quarter">
                <p><?php echo $T->t('view.tags'); ?></p>
            </div>
            <div class="column">
                <div class="field is-grouped is-grouped-multiline" id="tag-listbox">
                    <div class="control" id="tag-template" style="display: none;">
                        <div class="tags has-addons">
                            <span class="tag"></span>
                            <a class="tag is-delete" onclick="removeTag('','tag')"></a>
                        </div>
                    </div>

					<?php foreach($formData['tags'] as $t) { ?>
                    <div class="control" id="tag-<?php echo $t; ?>">
                        <div class="tags has-addons">
                            <span class="tag"><?php echo $t; ?></span>
                            <a class="tag is-delete" onclick="removeTag('<?php echo $t; ?>','tag')"></a>
                        </div>
                    </div>
					<?php } ?>
                </div>

				<div class="field">
					<div class="control">
                	<input type="text" placeholder="tagname"
						   name="taglistinput" list="tag-datalist" value="" onkeypress="addTag(event,'tag')" />
					</div>
					<p class="help"><?php echo $T->t('edit.link.tags.description'); ?></p>
				</div>
                <datalist id="tag-datalist">
                    <?php foreach($existingTags as $t) { ?>
                        <option value="<?php echo $t['name']; ?>"><?php echo $t['name']; ?></option>
                    <?php } ?>
                </datalist>
                <input type="hidden" name="data[tag]" id="tag-save" value="<?php echo implode(',',$formData['tags']); ?>" />
            </div>
        </div>
		<div class="columns">
			<div class="column is-one-quarter">
				<p><?php echo $T->t('view.category'); ?></p>
			</div>
			<div class="column">
				<div class="field is-grouped is-grouped-multiline" id="category-listbox">
					<div class="control" id="category-template" style="display: none;">
						<div class="tags has-addons">
							<span class="tag"></span>
							<a class="tag is-delete" onclick="removeTag('','category')"></a>
						</div>
					</div>

					<?php foreach($formData['categories'] as $t) { ?>
						<div class="control" id="category-<?php echo $t; ?>">
							<div class="tags has-addons">
								<span class="tag"><?php echo $t; ?></span>
								<a class="tag is-delete" onclick="removeTag('<?php echo $t; ?>','category')"></a>
							</div>
						</div>
					<?php } ?>
				</div>
				<div class="field">
					<div class="control">
						<input type="text" placeholder="categoryname"
							   name="categorylistinput" list="category-datalist" value="" onkeypress="addTag(event,'category')" />
					</div>
					<p class="help"><?php echo $T->t('edit.link.tags.description'); ?></p>
				</div>
				<datalist id="category-datalist">
					<?php foreach($existingCategories as $c) { ?>
						<option value="<?php echo $c['name']; ?>"><?php echo $c['name']; ?></option>
					<?php } ?>
				</datalist>
				<input type="hidden" name="data[category]" id="category-save" value="<?php echo implode(',',$formData['categories']); ?>" />
			</div>
		</div>
		<div class="columns">
			<div class="column is-one-quarter">
				<label><?php echo $T->t('edit.link.options'); ?></label>
			</div>
			<div class="column">
				<label class="checkbox">
					<input type="checkbox" name="data[private]" value="1" <?php if(Summoner::ifset($formData, 'private')) echo "checked"; ?> />
					<?php echo $T->t('view.private'); ?>
				</label>
			</div>
		</div>
		<div class="columns">
			<div class="column is-one-quarter">
				&nbsp;
			</div>
			<div class="column">
				<input type="submit" class="button is-info" name="refreshlink" value="<?php echo $T->t('edit.link.refresh'); ?>">
				<input type="submit" class="button is-success" name="editlink" value="<?php echo $T->t('edit.link.save'); ?>">
			</div>
		</div>
		<div class="columns">
			<div class="column is-one-quarter">
				<label><?php echo $T->t('edit.link.delete'); ?></label>
				<input class="checkbox" type="checkbox" name="data[delete]" value="1" />
			</div>
			<div class="column">
				<input type="submit" class="button is-danger" name="deleteLink" value="<?php echo $T->t('edit.link.delete'); ?>">
			</div>
		</div>
	</form>
</section>

<script type="text/javascript" src="asset/js/editlink.js"></script>
