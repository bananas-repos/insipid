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

<section class="section">
    <div class="columns">
    	<div class="column">
    		<form method="post">
    			<input type="hidden" name="password" />
    			<input type="hidden" name="username" />
				<div class="field has-addons">
    				<div class="control is-expanded">
        				<div class="control has-icons-left">
        					<input class="input" type="text" name="data[searchfield]" placeholder="Search your bookmarks">
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
    </div>
</section>

<?php if(!empty($submitFeedback)) { ?>
<section>
<div class="columns">
	<div class="column">
<?php if($submitFeedback['status'] == "error") { ?>
		<div class="notification is-danger">
			<?php echo $submitFeedback['message']; ?>
		</div>
<?php } else { ?>
		<div class="notification is-success">
			<p><?php echo $submitFeedback['message']; ?></p>
		</div>
<?php } ?>
	</div>
</div>
</section>
<?php } ?>

<?php if(!empty($searchResult)) { ?>
<section>
    <div class="columns">
    	<div class="column">
    		<div class="content">
        		<h3>Something has been found...</h3>
        		<div class="field is-grouped is-grouped-multiline">
<?php foreach ($searchResult as $sr) { ?>
					<div class="control">
						<div class="tags has-addons">
        					<a class="tag is-dark" href="<?php echo $sr['link']; ?>" target="_blank" ><?php echo $sr['title']; ?></a>
        					<a class="tag is-info" title="more details..." href="index.php?p=linkinfo&id=<?php echo $sr['hash']; ?>" ><i class="ion-gear-a"></i></a>
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

<section class="section">
	<div class="columns">
    	<div class="column">
    		<div class="content">
	    		<h4><a href="index.php?p=overview&m=all">Last added</a></h4>
<?php if(!empty($latestLinks)) { ?>
				<div class="tags">
<?php foreach ($latestLinks as $ll) { ?>
					<a class="tag is-medium" href="<?php echo $ll['link']; ?>" target="_blank"><?php echo $ll['title']; ?></a>
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
        foreach ($orderedCategories as $cat=>$date) {
            $links = $Management->linksByCategoryString($cat);
?>
    	<div class="column is-one-quarter">
    		<div class="content">
    			<h4><a href="?p=overview&m=category&id=<?php echo urlencode($cat); ?>"><?php echo $cat; ?></a></h4>
				<div class="tags">
<?php foreach ($links as $link) { ?>
					<a class="tag" href="<?php echo $link['link']; ?>" target="_blank"><?php echo $link['title']; ?></a>
<?php } ?>
				</div>
			</div>
		</div>
<?php
        }
    }
?>
	</div>
</section>
