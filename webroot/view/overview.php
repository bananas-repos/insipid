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
    		<h2 class="is-size-2"><i class="icon ion-md-list"></i> <?php echo $subHeadline; ?></h2>
    		<?php } ?>
		</div>
	</div>
</section>

<section class="section">
	<div class="columns is-multiline">
		<div class="column is-one-quarter">
            <div class="box">
              <article class="media">
                <div class="media-left">
                  <figure class="image is-64x64">
                    <img src="https://bulma.io/images/placeholders/128x128.png" alt="Image">
                  </figure>
                </div>
                <div class="media-content">
                  <div class="content">
                    <p>
                      <strong>John Smith</strong> <small>@johnsmith</small> <small>31m</small>
                      <br>
                      Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean efficitur sit amet massa fringilla egestas. Nullam condimentum luctus turpis.
                    </p>
                  </div>
                  <nav class="level is-mobile">
                    <div class="level-left">
                      <a class="level-item" aria-label="reply">
                        <span class="icon is-small">
                          <i class="fas fa-reply" aria-hidden="true"></i>
                        </span>
                      </a>
                      <a class="level-item" aria-label="retweet">
                        <span class="icon is-small">
                          <i class="fas fa-retweet" aria-hidden="true"></i>
                        </span>
                      </a>
                      <a class="level-item" aria-label="like">
                        <span class="icon is-small">
                          <i class="fas fa-heart" aria-hidden="true"></i>
                        </span>
                      </a>
                    </div>
                  </nav>
                </div>
              </article>
            </div>
		</div>
</section>

<section class="section">
<?php if(!empty($linkCollection)) { ?>
<div class="columns is-multiline">
<?php foreach ($linkCollection as $link) { ?>
	<div class="column is-one-quarter">
		<div class="columns">
			<div class="column">
    		<?php if(!empty($link['image'])) { ?>
          		<a href="<?php echo $link['link']; ?>" target="_blank">
            	<img class="linkthumbnail" src= "<?php echo $link['image']; ?>">
            	</a>
    		<?php } ?>
    		</div>
          	<div class="column">
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
</section>
