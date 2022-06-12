<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2022 Johannes Keßler
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
			<h2 class="is-size-2"><?php echo $T->t('view.random.headline'); ?></h2>
		</div>
	</div>
</section>
<section class="section">
	<div class="columns">
		<div class="column">

			<h3 class="is-size-3"><?php echo $T->t('view.random.link'); ?></h3>
			<div class="field is-grouped is-grouped-multiline">
				<?php foreach ($randomLink as $sr) { ?>
					<div class="control">
						<div class="tags has-addons">
							<a class="tag is-dark" href="<?php echo $sr['link']; ?>" target="_blank" ><?php echo $sr['title']; ?></a>
							<a class="tag is-info" title="<?php echo $T->t('view.more.details'); ?>" href="index.php?p=linkinfo&id=<?php echo $sr['hash']; ?>" ><i class="ion-md-information-circle-outline"></i></a>
						</div>
					</div>
				<?php } ?>
			</div>

			<h3 class="is-size-3"><?php echo $T->t('view.tag'); ?></h3>
			<div>
				<?php foreach ($randomTag as $sr) { ?>
					<a href="index.php?p=overview&m=tag&id=<?php echo $sr['id']; ?>"" class="button is-small">
						<span class="icon"><i class="ion-md-pricetag"></i></span>
						<span><?php echo $sr['name']; ?></span>
					</a>
				<?php } ?>
			</div>

			<h3 class="is-size-3"><?php echo $T->t('view.category'); ?></h3>
			<div>
				<?php foreach ($randomCategory as $sr) { ?>
					<a href="index.php?p=overview&m=category&id=<?php echo $sr['id']; ?>"" class="button is-small">
						<span class="icon"><i class="ion-md-filing"></i></span>
						<span><?php echo $sr['name']; ?></span>
					</a>
				<?php } ?>
			</div>

		</div>
	</div>
</section>
