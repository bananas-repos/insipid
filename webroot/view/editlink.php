<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2017 Johannes Keßler
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

<?php if(empty($link)) { ?>
<div class="callout alert">
	<h5>Error</h5>
	<p>Something went wrong...</p>
</div>
<?php } ?>

<?php if(!empty($submitFeedback)) { ?>
<div class="row">
	<div class="large-12 columns">
<?php if($submitFeedback['status'] == "error") { ?>
		<div class="callout alert">
			<h5>Error</h5>
			<p><?php echo $submitFeedback['message']; ?></p>
		</div>
<?php } else { ?>
		<div class="callout success">
			<h5>Success</h5>
			<p><?php echo $submitFeedback['message']; ?></p>
		</div>
<?php } ?>
	</div>
</div>
<?php } ?>

<div class="row">
	<div class="large-12 columns">
		<h1 class="text-center"><?php echo $link['title']; ?></h1>
	</div>
</div>
<div class="row expanded">
	<div class="large-12 columns">
		<p class="text-right"><a href="index.php" title="... back to home" class="tiny button"><i class="fi-home"></i></a></p>
	</div>
</div>

<form method="post">
	<div class="row">
    	<div class="small-12 medium-2 columns">
    		<p>Date added:</p>
    	</div>
    	<div class="small-12 medium-10 columns">
    		<p><?php echo $link['created']; ?></p>
    	</div>
    </div>
    <div class="row">
    	<div class="small-12 medium-2 columns">
    		<p>Title:</p>
    	</div>
    	<div class="small-12 medium-10 columns">
    		<input type="text" name="data[title]" value="<?php echo Summoner::ifset($formData, 'title'); ?>" />
    	</div>
    </div>
    <div class="row">
    	<div class="small-12 medium-2 columns">
    		<p>Description:</p>
    	</div>
    	<div class="small-12 medium-10 columns">
    		<input type="text" name="data[description]" value="<?php echo Summoner::ifset($formData, 'description'); ?>" />
    	</div>
    </div>
    <div class="row">
    	<div class="small-12 medium-2 columns">
    		<p>URL:</p>
    	</div>
    	<div class="small-12 medium-10 columns">
    		<p><?php echo $link['link']; ?></p>
    	</div>
    </div>
    <div class="row">
    	<div class="small-12 medium-2 columns">
    		<p>
    			Image:<br />
    			<small>If provided</small>
    		</p>
    	</div>
    	<div class="small-12 medium-10 columns">
    		<p>
    			<img class="linkthumbnail" src="<?php echo $link['image']; ?>" alt="Image if provided">
    		</p>
    		<input type="text" name="data[image]" value="<?php echo Summoner::ifset($formData, 'image'); ?>" />
    	</div>
    </div>

    <div class="row">
    	<div class="small-12 medium-2 columns">
    		<p>Tags:</p>
    	</div>
    	<div class="small-12 medium-10 columns">
    	    <input type="text" name="data[tag]" list="taglist"
				class="flexdatalist" data-min-length='1' multiple='multiple'
				value="<?php echo Summoner::ifset($formData, 'tag'); ?>" />
			<datalist id="taglist">
			<?php foreach($existingTags as $t) { ?>
				<option value="<?php echo $t['name']; ?>">
			<?php } ?>
            </datalist>
            <br />
    	</div>
    </div>
    <div class="row">
    	<div class="small-12 medium-2 columns">
    		<p>Category:</p>
    	</div>
    	<div class="small-12 medium-10 columns">
    	    <input type="text" name="data[category]" list="categorylist"
				class="flexdatalist" data-min-length='1' multiple='multiple'
				value="<?php echo Summoner::ifset($formData, 'category'); ?>" />
			<datalist id="categorylist">
			<?php foreach($existingCategories as $c) { ?>
				<option value="<?php echo $c['name']; ?>">
			<?php } ?>
            </datalist>
            <br />
    	</div>
    </div>
    <div class="row">
    	<div class="large-8 columns">
    		<input type="checkbox" name="data[private]" value="1" <?php if(Summoner::ifset($formData, 'private')) echo "checked"; ?> /><label>Private</label>
    	</div>
    	<div class="large-4 columns text-right" >
    		<input type="submit" class="button" name="editlink" value="Update">
    	</div>
    </div>
</form>
