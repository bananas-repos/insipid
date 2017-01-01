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
		<h1 class="text-center">All of your links</h1>
		<?php if(!empty($subHeadline)) { ?>
		<h4 class="text-center"><?php echo $subHeadline; ?></h4>
		<?php } ?>
	</div>
</div>
<div class="row expanded">
	<div class="large-12 columns">
		<p class="text-right">
			<a href="index.php?p=overview&m=tag" title="all tags"><i class="fi-price-tag"></i></a>
			<a href="index.php?p=overview&m=category" title="all categories"><i class="fi-ticket"></i></a>
			<a href="index.php" title="... back to home"><i class="fi-home"></i></a>
		</p>
	</div>
</div>

<?php if(!empty($linkCollection)) { ?>
<div class="row expanded small-up-1 medium-up-2 large-up-3" data-equalizer data-equalize-by-row="true">
<?php foreach ($linkCollection as $link) { ?>
	<div class="column">
		<div class="media-object linkbox" data-equalizer-watch>
		<?php if(!empty($link['image'])) { ?>
          	<div class="media-object-section">
          		<a href="<?php echo $link['link']; ?>" target="_blank">
            	<img class="linkthumbnail" src= "<?php echo $link['image']; ?>">
            	</a>
          	</div>
		<?php } ?>
          	<div class="media-object-section">
	            <h4><a href="<?php echo $link['link']; ?>" target="_blank"><?php echo $link['title']; ?></a></h4>
    	        <p><?php echo $link['description']; ?></p>
    	        <p>
    	        	<a href="<?php echo $link['link']; ?>" target="_blank" class="small button">Visit link</a>
    	        	<a href="index.php?p=linkinfo&id=<?php echo $link['hash']; ?>" class="small button">More details</a>
    	        </p>
        	  </div>
        </div>
	</div>
<?php } ?>
</div>
<?php } if(!empty($tagCollection)) { ?>
<div class="row">
	<div class="large-12 columns">
		<ul>
		<?php foreach ($tagCollection as $t) { ?>
			<li><a href="index.php?p=overview&m=tag&id=<?php echo urlencode($t['name']); ?>"><?php echo $t['name']; ?></a></li>
		<?php } ?>
		</ul>
	</div>
</div>
<?php } if(!empty($categoryCollection)) { ?>
<div class="row">
	<div class="large-12 columns">
		<ul>
		<?php foreach ($categoryCollection as $c) { ?>
			<li><a href="index.php?p=overview&m=category&id=<?php echo urlencode($c['name']); ?>"><?php echo $c['name']; ?></a></li>
		<?php } ?>
		</ul>
	</div>
</div>
<?php } ?>

