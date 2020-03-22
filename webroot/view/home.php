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
							<input class="input" type="text" name="data[searchfield]" placeholder="Search your bookmarks or add a new one">
							<span class="icon is-small is-left">
								<i class="ion-link"></i>
							</span>
						</div>
					</div>
					<div class="control">
						<input type="submit" class="button is-info" value="Search" name="submitsearch">
					</div>
				</div>
			</form>
		</div>

		<div class="column">
			<p class="has-text-right">
				<a href="index.php?p=overview&m=tag" title="all tags" class="button">
					<span class="icon"><i class="ion-md-pricetags"></i></span>
				</a>
				<a href="index.php?p=overview&m=category" title="all categories" class="button">
					<span class="icon"><i class="ion-md-filing"></i></span>
				</a>
				<a href="index.php" title="... back to home" class="button">
					<span class="icon"><i class="ion-md-home"></i></span>
				</a>
			</p>
		</div>
	</div>
	<?php require('_displaySubmitStatus.inc.php'); ?>
</section>

<?php if(!empty($searchResult)) { ?>
<section class="section">
	<div class="columns">
		<div class="column">
			<div class="content">
				<h3>Something has been found...</h3>
				<div class="field is-grouped is-grouped-multiline">
<?php foreach ($searchResult as $sr) { ?>
					<div class="control">
						<div class="tags has-addons">
							<a class="tag is-dark" href="<?php echo $sr['link']; ?>" target="_blank" ><?php echo $sr['title']; ?></a>
							<a class="tag is-info" title="more details..." href="index.php?p=linkinfo&id=<?php echo $sr['hash']; ?>" ><i class="ion-md-information-circle-outline"></i></a>
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
					<h3>This URL was not found. Want to add it?</h3>
				</div>
				<div class="field has-addons">
					<div class="control is-expanded">
						<div class="control has-icons-left">
							<input type="url" name="data[url]" class="input" value="<?php echo Summoner::ifset($formData, 'url'); ?>" />
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
					<label class="label">Title</label>
					<div class="control">
						<input class="input" type="text" name="data[title]" value="<?php echo Summoner::ifset($formData, 'title'); ?>" />
					</div>
				</div>
			</div>
			<div class="column is-half">
				<div class="field">
					<label class="label">Description</label>
					<div class="control">
						<input class="input" type="text" name="data[description]" value="<?php echo Summoner::ifset($formData, 'description'); ?>" />
					</div>
				</div>
			</div>
		</div>

		<div class="columns">
			<div class="column is-half">
				<img class="linkthumbnail" src="<?php echo Summoner::ifset($formData, 'imageToShow'); ?>" alt="Image from provided link" />
			</div>
			<div class="column is-half">
				<div class="field">
					<label class="label">Image Link</label>
					<div class="control">
						<input class="input" type="url" name="data[image]" value="<?php echo Summoner::ifset($formData, 'image'); ?>" />
					</div>
				</div>
			</div>
		</div>

		<div class="columns">
			<div class="column is-half">
				<label class="label">Category</label>

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
                    <p class="help">Enter a new one or select an existing from the suggested and press enter.</p>
                </div>
                <datalist id="category-datalist">
                    <?php foreach($existingCategories as $c) { ?>
                        <option value="<?php echo $c['name']; ?>"><?php echo $c['name']; ?></option>
                    <?php } ?>
                </datalist>
                <input type="hidden" name="data[category]" id="category-save" value="<?php echo implode(',',$formData['categories']); ?>" />

			</div>
			<div class="column is-half">
				<label class="label">Tag</label>

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
                    <p class="help">Enter a new one or select an existing from the suggested and press enter.</p>
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
					<input type="checkbox" name="data[private]" value="1" <?php if(Summoner::ifset($formData, 'private')) echo "checked"; ?> />
					Private
				</label>
			</div>
			<div class="column is-half">
				<div class="control">
					<input type="submit" class="button is-primary" name="addnewone" value="Add new Link">
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
				<h4><a href="index.php?p=overview&m=all">Last added</a></h4>
<?php if(!empty($latestLinks)) { ?>
				<div class="tags">
<?php foreach ($latestLinks as $ll) { ?>
					<a class="tag is-link" href="<?php echo $ll['link']; ?>" target="_blank"><?php echo $ll['title']; ?></a>
<?php } ?>
				</div>
<?php } ?>
			</div>
		</div>
	</div>
</section>


<section class="section">
	<div class="columns is-multiline">
<?php
	if(!empty($orderedCategories)) {
		foreach ($orderedCategories as $k=>$v) {
			$links = $Management->linksByCategory($v['id'],'');
?>
		<div class="column is-one-quarter">
			<div class="content">
				<h4><a href="?p=overview&m=category&id=<?php echo urlencode($v['id']); ?>"><?php echo $v['name']; ?></a></h4>
				<ul>
<?php foreach ($links['results'] as $link) { ?>
					<li><a class="" href="<?php echo $link['link']; ?>" target="_blank"><?php echo $link['title']; ?></a></li>
<?php } ?>
				</ul>
			</div>
		</div>
<?php
		}
	}
?>
	</div>
</section>
