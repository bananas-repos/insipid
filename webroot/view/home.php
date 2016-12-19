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

<?php if($showAddForm) { ?>
<form method="post">
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
    	<div class="large-6 columns">
    		<label>
    			Category
    			<select name="data[category]"></select>
    		</label>
    	</div>
    	<div class="large-6 columns">
    		<label>
    			Tag
    			<select name="data[tag]"></select>
    		</label>
    	</div>
    </div>

    <div class="row">
    	<div class="large-12 columns">
    		<input type="submit" class="button" value="Add new Link">
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
	  		<img src="assets/img/generic/rectangle-1.jpg">
			<div class="card-section">
				<p>It has an easy to override visual style, and is appropriately subdued.</p>
				<a class="button" href="#">I'm a button</a>
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
