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
	<div class="columns">
		<div class="column">
    		<p class="has-text-right">
    			<a href="index.php?p=overview&m=tag" title="all tags" class="is-small button">
    				<span class="icon"><i class="ion-md-pricetag"></i></span>
    			</a>
    			<a href="index.php?p=overview&m=category" title="all categories" class="is-small button">
    				<span class="icon"><i class="ion-md-list"></i></span>
    			</a>
    			<a href="index.php" title="... back to home" class="is-small button">
    				<span class="icon"><i class="ion-md-home"></i></span>
    			</a>
    		</p>
    	</div>
	</div>

	<div class="columns">
		<div class="column">
    		<h1 class="is-size-1">All of your links</h1>
    		<?php if(!empty($subHeadline)) { ?>
    		<h2 class="is-size-2"><?php echo $subHeadline; ?></h2>
    		<?php } ?>
		</div>
	</div>
</section>

<section class="section">
<?php if(!empty($linkCollection)) { ?>
<div class="columns is-multiline">
<?php foreach ($linkCollection as $link) { ?>
	<div class="column is-one-quarter">
		<div class="card">
			<div class="card-image">
    		<?php if(!empty($link['image'])) { ?>
    			<figure class="image is-4by3">
          		<a href="<?php echo $link['link']; ?>" target="_blank">
            	<img class="" src= "<?php echo $link['image']; ?>">
            	</a>
            	</figure>
    		<?php } ?>
			</div>
			<div class="card-content">
				<div class="content">
    	            <h4><a href="<?php echo $link['link']; ?>" target="_blank"><?php echo $link['title']; ?></a></h4>
        	        <p><?php echo $link['description']; ?></p>
				</div>
  			</div>
  			<footer class="card-footer">
				<a href="<?php echo $link['link']; ?>" target="_blank" class="card-footer-item">Visit link</a>
				<a href="index.php?p=linkinfo&id=<?php echo $link['hash']; ?>" class="card-footer-item">More details</a>
			</footer>
        </div>
	</div>
<?php } ?>
</div>
<?php } if(!empty($tagCollection)) { ?>
<div class="columns">
	<div class="column">
		<ul>
		<?php foreach ($tagCollection as $t) { ?>
			<li><a href="index.php?p=overview&m=tag&id=<?php echo urlencode($t['name']); ?>"><?php echo $t['name']; ?></a></li>
		<?php } ?>
		</ul>
	</div>
</div>
<?php } if(!empty($categoryCollection)) { ?>
<div class="columns">
	<div class="column">
		<ul>
		<?php foreach ($categoryCollection as $c) { ?>
			<li><a href="index.php?p=overview&m=category&id=<?php echo urlencode($c['name']); ?>"><?php echo $c['name']; ?></a></li>
		<?php } ?>
		</ul>
	</div>
</div>
<?php } ?>
</section>
