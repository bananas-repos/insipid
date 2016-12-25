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
<div class="row">
	<div class="large-12 columns">
		<h1 class="text-center">Welcome to your Inspid installation</h1>
	</div>
</div>

<div class="row">
	<div class="large-12 columns">
		<form method="post">
			<input type="hidden" name="password" />
			<input type="hidden" name="username" />
    		<div class="input-group">
    			<span class="input-group-label"><i class="fi-link"></i></span>
    			<input class="input-group-field" type="url" name="data[searchfield]">
    			<div class="input-group-button">
    				<input type="submit" class="button" value="Search" name="submitsearch">
    			</div>
    		</div>
    	</form>
    </div>
</div>

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

<?php if(!empty($searchResult)) { ?>
<div class="row">
	<div class="large-12 columns">
		<h3>Something has been found...</h3>
		<ul>
<?php foreach ($searchResult as $sr) { ?>
		<li>
			<a href="<?php echo $sr['link']; ?>" target="_blank" ><?php echo $sr['title']; ?></a>
			<a href="<?php echo $sr['link']; ?>" ><i class="fi-info"></i></a>
		</li>
<?php } ?>
		</ul>
	</div>
</div>
<?php } ?>

<?php if($showAddForm) { ?>
<form method="post">
	<input type="hidden" name="password" />
	<input type="hidden" name="username" />
	<div class="row">
    	<div class="large-12 columns">
    		<h3>This URL was not found. Want to add it?</h3>
    	</div>
    </div>
    <div class="row">
    	<div class="large-12 columns">
    		<label>
    			New URL
    			<input type="url" name="data[url]" value="<?php echo Summoner::ifset($formData, 'url'); ?>" />
    		</label>
        </div>
    </div>
    <div class="row">
    	<div class="large-6 columns">
    		<label>
    			Description
    			<input type="text" name="data[description]" value="<?php echo Summoner::ifset($formData, 'description'); ?>" />
    		</label>
    	</div>
    	<div class="large-6 columns">
			<label>
				Title
				<input type="text" name="data[title]" value="<?php echo Summoner::ifset($formData, 'title'); ?>" />
			</label>
    	</div>
    </div>
    <div class="row">
    	<div class="large-6 columns">
    		<label>
    			Image Link
    			<input type="url" name="data[image]" value="<?php echo Summoner::ifset($formData, 'image'); ?>" />
    		</label>
    	</div>
    	<div class="large-6 columns">
			<img class="linkthumbnail" src="<?php echo Summoner::ifset($formData, 'image'); ?>" alt="Image from provided link" />
    	</div>
    </div>
    <div class="row">
    	<div class="large-6 columns">
    		<label>
    			Category
    			<input type="text" name="data[category]" list="categorylist"
    				class="flexdatalist" data-min-length='1' multiple='multiple'
    				value="<?php echo Summoner::ifset($formData, 'category'); ?>" />
    			<datalist id="categorylist">
				<?php foreach($existingCategories as $c) { ?>
					<option value="<?php echo $c['name']; ?>">
				<?php } ?>
                </datalist>
    		</label>
    	</div>
    	<div class="large-6 columns">
    		<label>
    			Tag
    			<input type="text" name="data[tag]" list="taglist"
    				class="flexdatalist" data-min-length='1' multiple='multiple'
    				value="<?php echo Summoner::ifset($formData, 'tag'); ?>" />
    			<datalist id="taglist">
    			<?php foreach($existingTags as $t) { ?>
					<option value="<?php echo $t['name']; ?>">
				<?php } ?>
                </datalist>
    		</label>
    	</div>
    </div>

    <div class="row">
    	<div class="large-6 columns">
    		<label>
    			Username
    			<input type="text" name="data[username]" />
    		</label>
    	</div>
    	<div class="large-6 columns">
    		<label>
    			Password
    			<input type="password" name="data[password]" />
    		</label>
    	</div>
    </div>

    <div class="row">
    	<div class="large-8 columns">
    		<input type="checkbox" name="data[private]" value="1" <?php if(Summoner::ifset($formData, 'private')) echo "checked"; ?> /><label>Private</label>
    	</div>
    	<div class="large-4 columns text-right" >
    		<input type="submit" class="button" name="addnewone" value="Add new Link">
    	</div>
    </div>
</form>
<?php } ?>

<div class="row expanded small-up-3 medium-up-6">
	<div class="column">
		<div class="card">
			<div class="card-divider">
	    		<h4>Last added</h4>
	  		</div>
	  		<img src="asset/img/insipid.png">
			<div class="card-section">
<?php if(!empty($latestLinks)) { ?>
				<ul>
<?php foreach ($latestLinks as $ll) { ?>
					<li>
						<a href="<?php echo $ll['link']; ?>" target="_blank"><?php echo $ll['title']; ?></a>
					</li>
<?php } ?>
				</ul>
				<a class="button" href="#">more</a>
<?php } ?>
			</div>
		</div>
	</div>
  <div class="column">
    <div class="card">
      <img src="assets/img/generic/rectangle-1.jpg">
      <div class="card-section">
        <h4>This is a card.</h4>
        <p>It has an easy to override visual style, and is appropriately subdued.</p>
      </div>
    </div>
  </div>
  <div class="column">
    <div class="card">
      <img src="assets/img/generic/rectangle-1.jpg">
      <div class="card-section">
        <h4>This is a card.</h4>
        <p>It has an easy to override visual style, and is appropriately subdued.</p>
      </div>
    </div>
  </div>
  <div class="column">
    <div class="card">
      <img src="assets/img/generic/rectangle-1.jpg">
      <div class="card-section">
        <h4>This is a card.</h4>
        <p>It has an easy to override visual style, and is appropriately subdued.</p>
      </div>
    </div>
  </div>
  <div class="column">
    <div class="card">
      <img src="assets/img/generic/rectangle-1.jpg">
      <div class="card-section">
        <h4>This is a card.</h4>
        <p>It has an easy to override visual style, and is appropriately subdued.</p>
      </div>
    </div>
  </div>
  <div class="column">
    <div class="card">
      <img src="assets/img/generic/rectangle-1.jpg">
      <div class="card-section">
        <h4>This is a card.</h4>
        <p>It has an easy to override visual style, and is appropriately subdued.</p>
      </div>
    </div>
  </div>
</div>
