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
		</div>
	</div>

    <div class="columns">
        <div class="column is-half">
        <?php if($pagination['pages'] > 1) { ?>
            <nav class="pagination is-small" role="navigation" aria-label="pagination">
                <?php if($pagination['curPage'] > 1) {
                    echo '<a href="index.php?'.Summoner::createFromParameterLinkQuery($currentGetParameters,array('page'=>($pagination['curPage']-1))).'" 
                        class="pagination-previous">Previous</a>';
                }
                if($pagination['curPage'] < $pagination['pages']) {
                    echo '<a href="index.php?'.Summoner::createFromParameterLinkQuery($currentGetParameters,array('page'=>($pagination['curPage']+1))).'" 
                        class="pagination-next">Next</a>';
                }
                ?>
                <ul class="pagination-list">
                    <?php
                    $ellipsisShown = 0;
                    for($i=1;$i<=$pagination['pages'];$i++) {
                        $active = '';
                        if($i == $pagination['curPage']) $active = 'is-current';

                        if(in_array($i,$pagination['visibleRange'])) {
                            echo '<li><a href="index.php?'.Summoner::createFromParameterLinkQuery($currentGetParameters,array('page'=>$i)).'"
                            class="pagination-link ' . $active . '"
                            aria-label="Goto page ' . $i . '">' . $i . '</a></li>';
                        }
                        else {
                            if($i < $pagination['currentRangeStart'] && $ellipsisShown == 0) {
                                echo '<li><span class="pagination-ellipsis">&hellip;</span></li>';
                                $ellipsisShown = 1;
                            }
                            if($i > $pagination['currentRangeEnd'] && ($ellipsisShown == 0 || $ellipsisShown == 1)) {
                                echo '<li><span class="pagination-ellipsis">&hellip;</span></li>';
                                $ellipsisShown = 2;
                            }
                        }
                    }
                    ?>
                </ul>
            </nav>
        <?php } ?>
        </div>
        <div class="column is-half">
            <div class="is-pulled-right">
                <a href="index.php?<?php echo $sortLink['default']; ?>"
                   class="button is-small <?php if($sortLink['active'] === 'default') { ?>is-link<?php } ?>">default</a>
                <a href="index.php?<?php echo $sortLink['name']; ?>"
                   class="button is-small <?php if($sortLink['active'] === 'name') { ?>is-link<?php } ?>">name</a>
                <a href="index.php?<?php echo $sortLink['direction']; ?>"
                   class="button is-small <?php if($sortLink['activeDirection'] === true) { ?>is-link<?php } ?>"><span class="icon"><i class="ion-md-arrow-dropup"></i></span></a>
            </div>
        </div>
    </div>
</section>

<section class="section">
<?php if(!empty($linkCollection['results'])) { ?>
<div class="columns is-multiline">
<?php foreach ($linkCollection['results'] as $link) { ?>
	<div class="column is-one-quarter">
		<div class="card">
			<div class="card-image">
				<figure class="image is-4by3">
				<a href="<?php echo $link['link']; ?>" target="_blank">
			<?php if(!empty($link['image'])) { ?>
				<img class="linkthumbnail" src= "<?php echo $link['imageToShow']; ?>">
			<?php } else { ?>
				<img class="" src= "asset/img/no-link-picture.png">
			<?php } ?>
				</a>
				</figure>
			</div>
			<div class="card-content">
				<div class="content">
					<h4><a href="<?php echo $link['link']; ?>" target="_blank"><?php echo $link['title']; ?></a></h4>
					<p><?php echo $link['description']; ?></p>
				</div>
			</div>
			<footer class="card-footer">
				<a href="<?php echo $link['link']; ?>" target="_blank" class="card-footer-item">Visit link</a>
				<?php if($isAwm === true) { ?>
				<a href="index.php?p=editlink&id=<?php echo $link['hash']; ?>&awm=1" class="card-footer-item">Edit</a>
				<?php } else { ?>
				<a href="index.php?p=linkinfo&id=<?php echo $link['hash']; ?>" class="card-footer-item">More details</a>
				<?php } ?>
			</footer>
		</div>
	</div>
<?php } ?>
</div>
<?php } if(!empty($tagCollection)) { ?>
<div class="columns">
	<div class="column">
		<table class="table">
			<tr>
				<th>Name</th>
				<th># of links</th>
			</tr>
		<?php foreach ($tagCollection as $k=>$v) { ?>
			<tr>
				<td><a href="index.php?p=overview&m=tag&id=<?php echo urlencode($k); ?>"><?php echo $v['name']; ?></a></td>
				<td><?php echo $v['amount']; ?></td>
			</tr>
		<?php } ?>
		</table>
	</div>
	<?php if($displayEditButton === true) { ?>
	<div class="column">
		<div class="content">
			<a href="index.php?p=edittags" class="button is-small is-danger">
				<span class="icon"><i class="ion-md-create"></i></span>
				<span>Edit tags</span>
			</a>
		</div>
	</div>
	<?php } ?>
</div>
<?php } if(!empty($categoryCollection)) { ?>
<div class="columns">
	<div class="column">
		<table class="table">
			<tr>
				<th>Name</th>
				<th># of links</th>
			</tr>
		<?php foreach ($categoryCollection as $k=>$v) { ?>
			<tr>
				<td><a href="index.php?p=overview&m=category&id=<?php echo urlencode($k); ?>"><?php echo $v['name']; ?></a></td>
				<td><?php echo $v['amount']; ?></td>
			</tr>
		<?php } ?>
		</table>
	</div>
	<?php if($displayEditButton === true) { ?>
	<div class="column">
		<div class="content">
			<a href="index.php?p=editcategories" class="button is-small is-danger">
				<span class="icon"><i class="ion-md-create"></i></span>
				<span>Edit categories</span>
			</a>
		</div>
	</div>
	<?php } ?>
</div>
<?php } ?>
</section>
