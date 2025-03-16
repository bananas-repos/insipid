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
		<div class="column">
			<form method="post" action="index.php">
				<input type="hidden" name="password" />
				<input type="hidden" name="username" />
				<div class="field has-addons">
					<div class="control is-expanded">
						<div class="control has-icons-left">
							<input class="input" type="text" name="data[searchfield]" placeholder="<?php echo $T->t('home.input.placeholder'); ?>">
							<span class="icon is-small is-left">
								<i class="ion-link"></i>
							</span>
						</div>
					</div>
					<div class="control">
						<input type="submit" class="button is-info" value="<?php echo $T->t('home.input.search'); ?>" name="submitsearch">
					</div>
				</div>
			</form>
		</div>

		<?php require('_headNavIcons.inc.php'); ?>
	</div>
	<?php require('_displaySubmitStatus.inc.php'); ?>
</section>

<?php if(!empty($searchResult)) { ?>
<section class="section">
	<div class="columns">
		<div class="column">
			<div class="content">
				<h3><?php echo $T->t('home.input.search.found'); ?></h3>
				<div class="field is-grouped is-grouped-multiline">
<?php foreach ($searchResult as $sr) { ?>
					<div class="control">
						<div class="tags has-addons">
							<a class="tag is-dark" href="<?php echo $sr['link']; ?>" target="_blank" ><?php echo $sr['title']; ?></a>
							<a class="tag is-info" title="<?php echo $T->t('view.more.details'); ?>" href="index.php?p=linkinfo&id=<?php echo $sr['hash']; ?>" ><i class="ion-md-information-circle-outline"></i></a>
						</div>
					</div>
<?php } ?>
				</div>
			</div>
		</div>
	</div>
</section>
<?php } ?>

<?php if($showAddForm) { ?>
<section class="section">
	<form method="post" autocomplete=off>
		<input type="hidden" name="password" />
		<input type="hidden" name="username" />
		<div class="columns">
			<div class="column">
				<div class="content">
					<h3><?php echo $T->t('home.input.url.not.found.add'); ?></h3>
				</div>
				<div class="field has-addons">
					<div class="control is-expanded">
						<div class="control has-icons-left">
							<input type="url" name="data[url]" class="input" value="<?php echo $formData['url'] ?? ''; ?>" />
							<span class="icon is-small is-left">
								<i class="ion-link"></i>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="columns">
			<div class="column is-half">
				<div class="field">
					<label class="label"><?php echo $T->t('view.title'); ?></label>
					<div class="control">
						<input class="input" type="text" name="data[title]" value="<?php echo $formData['title'] ?? ''; ?>" />
					</div>
				</div>
			</div>
			<div class="column is-half">
				<div class="field">
					<label class="label"><?php echo $T->t('view.description'); ?></label>
					<div class="control">
						<input class="input" type="text" name="data[description]" value="<?php echo $formData['description'] ?? ''; ?>" />
					</div>
				</div>
			</div>
		</div>

		<div class="columns">
			<div class="column is-half">
				<img class="linkthumbnail" src="<?php echo $formData['imageToShow'] ?? ''; ?>" alt="<?php echo $T->t('view.image.of.link'); ?>" />
			</div>
			<div class="column is-half">
				<div class="field">
					<label class="label"><?php echo $T->t('view.image.link'); ?></label>
					<div class="control">
						<input class="input" type="url" name="data[image]" value="<?php echo $formData['image'] ?? ''; ?>" />
					</div>
				</div>
			</div>
		</div>

		<div class="columns">
			<div class="column is-half">
				<label class="label"><?php echo $T->t('view.categories'); ?></label>

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
                    <p class="help"><?php echo $T->t('view.tag.help'); ?></p>
                </div>
                <datalist id="category-datalist">
                    <?php foreach($existingCategories as $c) { ?>
                        <option value="<?php echo $c['name']; ?>"><?php echo $c['name']; ?></option>
                    <?php } ?>
                </datalist>
                <input type="hidden" name="data[category]" id="category-save" value="<?php echo implode(',',$formData['categories']); ?>" />

			</div>
			<div class="column is-half">
				<label class="label"><?php echo $T->t('view.tag'); ?></label>

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
                    <p class="help"><?php echo $T->t('view.tag.help'); ?></p>
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
			<div class="column is-half">
				<label class="checkbox is-pulled-right">
					<input type="checkbox" name="data[private]" value="1" <?php if(isset($formData['private'])) echo "checked"; ?> />
					<?php echo $T->t('view.private'); ?>
				</label>
			</div>
			<div class="column is-half">
				<div class="control">
					<input type="submit" class="button is-primary" name="addnewone" value="<?php echo $T->t('home.input.new.link'); ?>">
				</div>
			</div>
		</div>

	</form>
</section>

<script type="text/javascript" src="asset/js/editlink.js"></script>

<?php } ?>

<section class="section">
	<div class="columns">
		<div class="column">
			<div class="content">
				<h4><a href="index.php?p=overview&m=all"><?php echo $T->t('home.last.added'); ?></a></h4>
<?php if(!empty($latestLinks)) { ?>
				<dl>
<?php foreach ($latestLinks as $ll) { ?>
					<dt>
                        <a href="<?php echo $ll['link']; ?>" target="_blank"><?php echo $ll['title']; ?></a>
                        <a href="index.php?p=linkinfo&id=<?php echo $ll['hash']; ?>"><i class="ion-md-information-circle-outline"></i></a>
                    </dt>
                    <dd class="tags ddTags">
                    <?php foreach ($ll['tags'] as $tid=>$tname) {
                        echo '<a href="index.php?p=overview&m=tag&id='.$tid.'" class="tag is-white" title="'.$T->t('view.tag').'">'.$tname.'</a>';
                    } ?>
                    <?php foreach ($ll['categories'] as $cid=>$cname) {
                        echo '<a href="index.php?p=overview&m=category&id='.$cid.'" class="tag is-white" title="'.$T->t('view.category').'">'.$cname.'</a>';
                    } ?>
                    </dd>
<?php } ?>
				</dl>
<?php } ?>
			</div>
		</div>
	</div>
</section>
