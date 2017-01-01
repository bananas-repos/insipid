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
<div class="row">
	<div class="large-12 columns">
		<h1 class="text-center"><?php echo $link['title']; ?></h1>
	</div>
</div>
<div class="row expanded">
	<div class="large-12 columns">
		<p class="text-right"><a href="index.php" title="... back to home"><i class="fi-home"></i></a></p>
	</div>
</div>
<div class="row">
	<div class="small-12 medium-2 columns">
		<p>Title:</p>
	</div>
	<div class="small-12 medium-10 columns">
		<p><?php echo $link['title']; ?></p>
	</div>
</div>
<div class="row">
	<div class="small-12 medium-2 columns">
		<p>Description:</p>
	</div>
	<div class="small-12 medium-10 columns">
		<p><?php echo $link['description']; ?></p>
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
	</div>
</div>
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
		<p>Tags:</p>
	</div>
	<div class="small-12 medium-10 columns">
		<?php
            if(!empty($link['tags'])) {
		      foreach($link['tags'] as $v) {
        ?>
        	<a href="index.php?p=overview&m=tag&id=<?php echo urlencode($v['tag']); ?>" class="button tiny"><i class="fi-price-tag"></i> <?php echo $v['tag']; ?></a>
        <?php
		      }
            }
	    ?>
	</div>
</div>
<div class="row">
	<div class="small-12 medium-2 columns">
		<p>Category:</p>
	</div>
	<div class="small-12 medium-10 columns">
		<?php
            if(!empty($link['categories'])) {
		      foreach($link['categories'] as $v) {
        ?>
        	<a href="index.php?p=overview&m=category&id=<?php echo urlencode($v['category']); ?>" class="button tiny"><i class="fi-ticket"></i> <?php echo $v['category']; ?></a>
        <?php
		      }
            }
	    ?>
	</div>
</div>

