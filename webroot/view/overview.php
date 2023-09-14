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
<section class="section">
	<div class="columns">
		<?php require('_headNavIcons.inc.php'); ?>
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
                        class="pagination-previous">'.$T->t('view.previous').'</a>';
                }
                if($pagination['curPage'] < $pagination['pages']) {
                    echo '<a href="index.php?'.Summoner::createFromParameterLinkQuery($currentGetParameters,array('page'=>($pagination['curPage']+1))).'" 
                        class="pagination-next">'.$T->t('view.next').'</a>';
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
                            class="pagination-link '.$active.'"
                            aria-label="Goto page '.$i.'">'. $i.'</a></li>';
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
            <?php if(!empty($linkCollection['results'])) { ?>
            <div class="is-pulled-right">
                <a href="index.php?<?php echo $sortLink['default']; ?>"
                   class="button is-small <?php if($sortLink['active'] === 'default') { ?>is-link<?php } ?>"><?php echo $T->t('view.sort.default'); ?></a>
                <a href="index.php?<?php echo $sortLink['name']; ?>"
                   class="button is-small <?php if($sortLink['active'] === 'title') { ?>is-link<?php } ?>"><?php echo $T->t('view.sort.title'); ?></a>
                <a href="index.php?<?php echo $sortLink['direction']; ?>"
                   class="button is-small <?php if($sortLink['activeDirection'] === true) { ?>is-link<?php } ?>"><span class="icon"><i class="ion-md-arrow-dropup"></i></span></a>
            </div>
            <?php } ?>
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
				<a href="<?php echo $link['link']; ?>" target="_blank" class="card-footer-item"><?php echo $T->t('view.visit.link'); ?></a>
				<?php if($isAwm === true) { ?>
				<a href="index.php?p=editlink&id=<?php echo $link['hash']; ?>&awm=1" class="card-footer-item"><?php echo $T->t('view.edit'); ?></a>
				<?php } else { ?>
				<a href="index.php?p=linkinfo&id=<?php echo $link['hash']; ?>" class="card-footer-item"><?php echo $T->t('view.more.details'); ?></a>
				<?php } ?>
			</footer>
		</div>
	</div>
<?php } ?>
</div>
<?php } if(!empty($tagCollection)) { ?>
<div class="columns">
	<div class="column is-half">
		<?php if($displayEditButton === true) { ?>
			<div class="column">
				<div class="content">
					<a href="index.php?p=edittags" class="button is-small is-danger">
						<span class="icon"><i class="ion-md-create"></i></span>
						<span><?php echo $T->t('view.edit.tags'); ?></span>
					</a>
				</div>
			</div>
		<?php } ?>
		<div class="column">
			<table class="table">
				<tr>
					<th><?php echo $T->t('view.name'); ?></th>
					<th><?php echo $T->t('view.num.links'); ?></th>
				</tr>
			<?php foreach ($tagCollection as $k=>$v) { ?>
				<tr>
					<td><a href="index.php?p=overview&m=tag&id=<?php echo urlencode($k); ?>"><?php echo $v['name']; ?></a></td>
					<td><?php echo $v['amount']; ?></td>
				</tr>
			<?php } ?>
			</table>
		</div>
	</div>
	<div class="column is-half">
		<?php if(!empty($colInfo)) { ?>
		<?php echo $T->t('view.tag.topcombination'); ?>
		<table class="table">
			<tr>
				<th>#</th>
				<th><?php echo $T->t('view.tags'); ?></th>
			</tr>
			<?php foreach ($colInfo as $k=>$v) { ?>
				<tr>
					<td><?php echo $v['amount']; ?></td>
					<td>
						<?php foreach ($v['rel'] as $tid=>$tname) { ?>
							<a href="index.php?p=overview&m=tag&id=<?php echo urlencode($tid); ?>"><?php echo $tname; ?></a>
						<?php } ?>
					</td>
				</tr>
			<?php } ?>
		</table>
		<?php } ?>
	</div>
</div>
<?php } if(!empty($categoryCollection)) { ?>
<div class="columns">
	<div class="column is-half">
		<?php if($displayEditButton === true) { ?>
			<div class="column">
				<div class="content">
					<a href="index.php?p=editcategories" class="button is-small is-danger">
						<span class="icon"><i class="ion-md-create"></i></span>
						<span><?php echo $T->t('view.edit.categories'); ?></span>
					</a>
				</div>
			</div>
		<?php } ?>
		<div class="column">
			<table class="table">
				<tr>
					<th><?php echo $T->t('view.name'); ?></th>
					<th><?php echo $T->t('view.num.links'); ?></th>
				</tr>
			<?php foreach ($categoryCollection as $k=>$v) { ?>
				<tr>
					<td><a href="index.php?p=overview&m=category&id=<?php echo urlencode($k); ?>"><?php echo $v['name']; ?></a></td>
					<td><?php echo $v['amount']; ?></td>
				</tr>
			<?php } ?>
			</table>
		</div>
	</div>
	<div class="column is-half">
		<?php if(!empty($colInfo)) { ?>
			<?php echo $T->t('view.category.topcombination'); ?>
			<table class="table">
				<tr>
					<th>#</th>
					<th><?php echo $T->t('view.categories'); ?></th>
				</tr>
				<?php foreach ($colInfo as $k=>$v) { ?>
					<tr>
						<td><?php echo $v['amount']; ?></td>
						<td>
							<?php foreach ($v['rel'] as $cid=>$cname) { ?>
								<a href="index.php?p=overview&m=category&id=<?php echo urlencode($cid); ?>"><?php echo $cname; ?></a>
							<?php } ?>
						</td>
					</tr>
				<?php } ?>
			</table>
		<?php } ?>
	</div>
</div>
<?php } ?>
</section>
