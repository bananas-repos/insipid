<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2019 Johannes Keßler
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
			<?php if(!empty($subHeadline)) { ?>
			<h2 class="is-size-2"><?php echo $subHeadline; ?></h2>
			<?php } ?>
			<h3><a href="index.php?p=overview&m=tag">
					<i class="icon ion-md-return-left"></i></a></h3>
		</div>
	</div>

<?php require('_displaySubmitStatus.inc.php'); ?>

</section>

<section>
<?php if(!empty($categoryCollection)) { ?>
<div class="columns">
	<div class="column">
		<form method="post">
			<table class="table">
				<tr>
					<th>Name</th>
					<th>New name</th>
					<th>Deletion</th>
				</tr>
			<?php foreach ($categoryCollection as $k=>$v) { ?>
				<tr>
					<td><a href="index.php?p=overview&m=category&id=<?php echo urlencode($k); ?>" target="_blank"><?php echo $v['name']; ?></a></td>
					<td>
						<input class="input" type="text" name="category[<?php echo urlencode($k); ?>]">
					</td>
					<td>
						<input type="checkbox" value="delete" name="deleteCategory[<?php echo urlencode($k); ?>]">
					</td>
				</tr>
			<?php } ?>
				<tr>
					<td>New category</td>
					<td>
						<input class="input" type="text" name="newCategory">
					</td>
					<td>
						&nbsp;
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<input type="submit" class="button is-success" name="updateCategories" value="Update categories">
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
<?php } ?>
</section>
