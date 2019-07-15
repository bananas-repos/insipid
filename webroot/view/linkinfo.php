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

<?php if(empty($linkData)) { ?>
<section class="section">
    <div class="columns">
    	<div class="column">
    		<div class="notification is-danger">
    			<h5>Error</h5>
    			<p>Something went wrong...</p>
    		</div>
    	</div>
    </div>
</section>
<?php } ?>

<section class="section">
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
	<div class="columns">
		<div class="column is-one-third">
			<p>Title:</p>
		</div>
		<div class="column is-two-third">
			<p><?php echo $linkData['title']; ?></p>
		</div>
	</div>
	<div class="columns">
    	<div class="column is-one-third">
    		<p>Description:</p>
    	</div>
    	<div class="column is-two-third">
    		<p><?php echo $linkData['description']; ?></p>
    	</div>
    </div>
    <div class="columns">
    	<div class="column is-one-third">
    		<p>URL:</p>
    	</div>
    	<div class="column is-two-third">
    		<p><a href="<?php echo $linkData['link']; ?>" target="_blank"><?php echo $linkData['link']; ?></a></p>
    	</div>
    </div>
    <div class="columns">
    	<div class="column is-one-third">
    		<p>
    			Image: (<small>If provided</small>)
    		</p>
    	</div>
    	<div class="column is-two-third">
    		<p>
    			<img class="linkthumbnail" src="<?php echo $linkData['image']; ?>" alt="Image if provided...">
    		</p>
    	</div>
    </div>
    <div class="columns">
    	<div class="column is-one-third">
    		<p>Date added:</p>
    	</div>
    	<div class="column is-two-third">
    		<p><?php echo $linkData['created']; ?></p>
    	</div>
    </div>
    <div class="columns">
    	<div class="column is-one-third">
    		<p>Tags:</p>
    	</div>
    	<div class="column is-two-third">
    		<?php
                if(!empty($linkData['tags'])) {
    		      foreach($linkData['tags'] as $k=>$v) {
            ?>
            	<a href="index.php?p=overview&m=tag&id=<?php echo urlencode($k); ?>" class="button is-small">
            		<span class="icon"><i class="ion-md-pricetag"></i></span>
            		<span><?php echo $v; ?></span>
            	</a>
            <?php
    		      }
                }
    	    ?>
    	</div>
    </div>
    <div class="columns">
    	<div class="column is-one-third">
    		<p>Category:</p>
    	</div>
    	<div class="column is-two-third">
    		<?php
                if(!empty($linkData['categories'])) {
    		      foreach($linkData['categories'] as $k=>$v) {
            ?>
            	<a href="index.php?p=overview&m=category&id=<?php echo urlencode($k); ?>" class="button is-small">
            		<span class="icon"><i class="ion-md-list"></i></span>
            		<span><?php echo $v; ?></span>
            	</a>
            <?php
    		      }
                }
    	    ?>
    	</div>
    </div>
    <div class="columns">
    	<div class="column">
            <a href="index.php?p=editlink&id=<?php echo $linkData['hash']; ?>" class="button is-small is-danger">
            	<span class="icon">
            		<i class="ion-md-create"></i>
            	</span>
            	<span>edit</span>
            </a>
    	</div>
    </div>
</section>








