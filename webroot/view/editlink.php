<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2018 Johannes Keßler
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
<?php if(empty($linkData)) { ?>
	<div class="columns">
		<div class="column">
			<div class="notification is-danger">
				<h5>Error</h5>
				<p>Something went wrong...</p>
			</div>
		</div>
	</div>
<?php } ?>

<?php require('_displaySubmitStatus.inc.php'); ?>

	<div class="columns">
		<div class="column">
			<p class="has-text-right">
				<a href="index.php" title="... back to home" class="button">
					<i class="icon ion-md-home"></i>
				</a>
			</p>
		</div>
	</div>

	<div class="columns">
		<div class="column">
			<h1 class="is-size-2"><?php echo $linkData['title']; ?></h1>
		</div>
	</div>
</section>

<section class="section">

	<form method="post">
		<div class="columns">
			<div class="column is-one-quarter">
				<p>Date added:</p>
			</div>
			<div class="column">
				<p>
					<?php echo $linkData['created']; ?>
					(Last update: <?php echo $linkData['updated']; ?>)
				</p>
			</div>
		</div>
		<div class="columns">
			<div class="column is-one-quarter">
				<p>Title:</p>
			</div>
			<div class="column">
				<input class="input" type="text" name="data[title]" value="<?php echo Summoner::ifset($formData, 'title'); ?>" />
			</div>
		</div>
		<div class="columns">
			<div class="column is-one-quarter">
				<p>Description:</p>
			</div>
			<div class="column">
				<input class="input" type="text" name="data[description]" value="<?php echo Summoner::ifset($formData, 'description'); ?>" />
			</div>
		</div>
		<div class="columns">
			<div class="column is-one-quarter">
				<p>URL:</p>
			</div>
			<div class="column">
				<p><?php echo $linkData['link']; ?></p>
			</div>
		</div>
		<div class="columns">
			<div class="column is-one-quarter">
				<p>
					Image: (<small>If provided</small>)
				</p>
			</div>
			<div class="column">
				<p>
					<img class="linkthumbnail" src="<?php echo $linkData['imageToShow']; ?>" alt="Image if provided...">
				</p>
				<input class="input" type="text" name="data[image]" value="<?php echo Summoner::ifset($formData, 'image'); ?>" /><br />
				<br />
				<input class="checkbox" type="checkbox" name="data[localImage]" value="1" <?php if(Summoner::ifset($formData, 'localImage')) echo "checked"; ?> />
				Store image locally
			</div>
		</div>

		<div class="columns">
			<div class="column is-one-quarter">
				<p>Tags:</p>
			</div>
			<div class="column">
				<input type="text" name="data[tag]" list="taglist"
					class="flexdatalist input" multiple='multiple'
					data-min-length="0" data-cache="0"
					data-toggle-selected="true"
					value="<?php echo Summoner::ifset($formData, 'tag'); ?>" />
				<datalist id="taglist">
				<?php foreach($existingTags as $t) { ?>
					<option value="<?php echo $t['name']; ?>"><?php echo $t['name']; ?></option>
				<?php } ?>
				</datalist>
			</div>
		</div>
		<div class="columns">
			<div class="column is-one-quarter">
				<p>Category:</p>
			</div>
			<div class="column">
				<input type="text" name="data[category]" list="categorylist"
					class="flexdatalist input" multiple='multiple'
					data-min-length="0" data-cache="0"
					data-toggle-selected="true"
					value="<?php echo Summoner::ifset($formData, 'category'); ?>" />
				<datalist id="categorylist">
				<?php foreach($existingCategories as $c) { ?>
					<option value="<?php echo $c['name']; ?>"><?php echo $c['name']; ?></option>
				<?php } ?>
				</datalist>
			</div>
		</div>
		<div class="columns">
			<div class="column is-half">
				<label>Private</label>
				<input class="checkbox" type="checkbox" name="data[private]" value="1" <?php if(Summoner::ifset($formData, 'private')) echo "checked"; ?> />
			</div>
			<div class="column is-half">
				<input type="submit" class="button is-info" name="refreshlink" value="Refresh from source">
				<input type="submit" class="button is-primary" name="editlink" value="Save">
			</div>
		</div>
	</form>
</section>

<link rel="stylesheet" href="asset/css/jquery.flexdatalist.min.css">
<script type="text/javascript" src="asset/js/jquery.min.js"></script>
<script type="text/javascript" src="asset/js/jquery.flexdatalist.min.js"></script>
