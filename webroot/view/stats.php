<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2020 Johannes Keßler
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
				<a href="index.php?p=overview&m=tag" title="all tags" class="button">
					<span class="icon"><i class="ion-md-pricetags"></i></span>
				</a>
				<a href="index.php?p=overview&m=category" title="all categories" class="button">
					<span class="icon"><i class="ion-md-filing"></i></span>
				</a>
				<a href="index.php" title="... back to home" class="button">
					<span class="icon"><i class="ion-md-home"></i></span>
				</a>
			</p>
		</div>
	</div>

	<div class="columns">
		<div class="column">
			<h2 class="is-size-2">Stats</h2>
		</div>
	</div>
</section>

<section class="section">
	<div class="columns is-multiline">
		<div class="column is-one-quarter">
			<h4 class="is-size-4">Links</h4>
			<p># of Links: <?php echo $linkAmount; ?></p>
			<p><a href="index.php?p=overview&m=all">View all</a></p>
		</div>
		<div class="column is-one-quarter">
			<h4 class="is-size-4">Tags</h4>
			<p># of Tags: <?php echo $tagAmount; ?></p>
			<p><a href="index.php?p=overview&m=tag">View all</a></p>
		</div>
		<div class="column is-one-quarter">
			<h4 class="is-size-4">Categories</h4>
			<p># of Categories: <?php echo $categoryAmount; ?></p>
			<p><a href="index.php?p=overview&m=category">View all</a></p>
		</div>
		<?php if($_displayEditButton === true) { ?>
		<div class="column is-one-quarter">
			<h4 class="is-size-4">Moderation</h4>
			<p># Moderation needed: <?php echo $moderationAmount; ?></p>
			<p><a href="index.php?p=overview&m=awm">View all</a></p>
		</div>
		<div class="column is-one-quarter">
			<h4 class="is-size-4">Local image storage</h4>
			<p>Diskspace used: <?php echo $localStorageAmount; ?></p>
            <form method="post">
                <input type="submit" class="button is-info is-small" value="Delete all" name="statsDeleteLocalStorage">
            </form>
		</div>
        <div class="column is-one-quarter">
            <h4 class="is-size-4">Full DB backup</h4>
            <p>Create a complete DB export from your data.</p>
            <form method="post">
                <input type="submit" class="button is-info is-small" value="Create backup" name="statsCreateDBBackup">
            </form>
        </div>
        <div class="column is-one-quarter">
            <h4 class="is-size-4">Search index</h4>
            <p>Update search index</p>
            <form method="post">
                <input type="submit" class="button is-info is-small" value="Update index" name="statsUpdateSearchIndex">
            </form>
        </div>
		<?php } ?>
	</div>
</section>
