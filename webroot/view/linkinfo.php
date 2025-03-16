<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2025 Johannes Keßler
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
		<?php require('_headNavIcons.inc.php'); ?>
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
			<p><?php echo $T->t('view.title'); ?></p>
		</div>
		<div class="column is-two-third">
			<p><?php echo $linkData['title']; ?></p>
		</div>
	</div>
	<div class="columns">
		<div class="column is-one-third">
			<p><?php echo $T->t('view.description'); ?></p>
		</div>
		<div class="column is-two-third">
			<p><?php echo $linkData['description']; ?></p>
		</div>
	</div>
	<div class="columns">
		<div class="column is-one-third">
			<p><?php echo $T->t('view.url'); ?></p>
		</div>
		<div class="column is-two-third">
			<p><a href="<?php echo $linkData['link']; ?>" target="_blank"><?php echo $linkData['link']; ?></a></p>
		</div>
	</div>
	<div class="columns">
		<div class="column is-one-third">
			<p>
				<?php echo $T->t('view.website.thumbnail'); ?> (<small><?php echo $T->t('view.website.thumbnail.provided'); ?></small>)
			</p>
		</div>
		<div class="column is-two-third">
			<p>
				<img class="linkthumbnail" src="<?php echo $linkData['imageToShow']; ?>" alt="<?php echo $T->t('view.website.thumbnail.noimage'); ?>">
			</p>
		</div>
	</div>
    <?php if(defined('COMPLETE_PAGE_SCREENSHOT') && COMPLETE_PAGE_SCREENSHOT === true) { ?>
    <div class="columns">
	    <div class="column is-one-third">
		    <p>
                <?php echo $T->t('view.pagescreenshot'); ?>
		    </p>
	    </div>
	    <div class="column is-two-third">
            <?php if(isset($linkData['pagescreenshotLink'])) { ?>
			    <p><a href="<?php echo $linkData['pagescreenshotLink']; ?>" target="_blank"><?php echo $T->t('view.pagescreenshot.link'); ?></a></p>
            <?php } ?>
	    </div>
    </div>
    <?php } ?>
	<div class="columns">
		<div class="column is-one-third">
			<p><?php echo $T->t('view.date.added'); ?></p>
		</div>
		<div class="column is-two-third">
			<p><?php echo $linkData['created']; ?></p>
		</div>
	</div>
	<div class="columns">
		<div class="column is-one-third">
			<p><?php echo $T->t('view.tags'); ?></p>
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
			<p><?php echo $T->t('view.categories'); ?></p>
		</div>
		<div class="column is-two-third">
			<?php
				if(!empty($linkData['categories'])) {
				  foreach($linkData['categories'] as $k=>$v) {
			?>
				<a href="index.php?p=overview&m=category&id=<?php echo urlencode($k); ?>" class="button is-small">
					<span class="icon"><i class="ion-md-filing"></i></span>
					<span><?php echo $v; ?></span>
				</a>
			<?php
				  }
				}
			?>
		</div>
	</div>
	<?php if($_displayEditButton === true) { ?>
	<div class="columns">
		<div class="column">
			<a href="index.php?p=editlink&id=<?php echo $linkData['hash']; ?>" class="button is-small is-danger">
				<span class="icon">
					<i class="ion-md-create"></i>
				</span>
				<span><?php echo $T->t('view.edit'); ?></span>
			</a>
            <a href="index.php?p=editlink&id=<?php echo $linkData['hash']; ?>&m=export" class="button is-small is-success">
				<span class="icon">
					<i class="ion-md-download"></i>
				</span>
                <span><?php echo $T->t('view.export'); ?></span>
            </a>
		</div>
	</div>
	<?php } ?>
</section>
