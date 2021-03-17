<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2021 Johannes Keßler
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
<div class="column">
	<p class="has-text-right">
		<a href="index.php?p=overview&m=tag" title="<?php echo $T->t('view.nav.all.tags'); ?>" class="button">
			<span class="icon"><i class="ion-md-pricetags"></i></span>
		</a>
		<a href="index.php?p=overview&m=category" title="<?php echo $T->t('view.nav.all.categories'); ?>" class="button">
			<span class="icon"><i class="ion-md-filing"></i></span>
		</a>
		<a href="index.php" title="<?php echo $T->t('view.nav.back.home'); ?>" class="button">
			<span class="icon"><i class="ion-md-home"></i></span>
		</a>
	</p>
</div>
