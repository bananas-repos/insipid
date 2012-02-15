#!/usr/bin/perl
#
# Copyright (C) 2008 Luke Reeves
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307
# USA
#

package Insipid::Tags;

use strict;
use warnings;

use vars qw(@ISA @EXPORT @EXPORT_OK);
use CGI qw/:standard/;
use CGI::Carp qw(fatalsToBrowser);
use Insipid::Config;
use Insipid::Database;
use Insipid::Sessions;
use Insipid::Util;
require Exporter;

use Data::Dumper;

@ISA = qw(Exporter);

@EXPORT = qw(
show_tags
get_tags
get_tags_list
set_tags
tag_operations
);

sub tag_operations {

	check_access();

	my ($sql,$sth,$sql1, $sth1, $sql2,$sth2);

	if(param('save') && param('newName')) {
		print '<p>Reanming...</p>';

		my $newTag = param('newName');
		my $oldTagName = param('rename');
		my $oldTagId = get_tag_id_by_name($oldTagName);

		# is the new name already a tag ?
		# if check then check is the new tagId
		my $check = get_tag_id_by_name($newTag);
		if($check && ($check != $oldTagId)) {
			$sql = "SELECT bookmark_id, tag_id
						FROM `$tbl_bookmark_tags`
						WHERE tag_id = ?";
			$sth = $dbh->prepare($sql);
			$sth->execute($oldTagId);
			while(my ($bid, $tid) = $sth->fetchrow_array) {
				$sql1 = "DELETE FROM `$tbl_bookmark_tags`
							WHERE bookmark_id = ?
							AND tag_id = ?";
				$sth1 = $dbh->prepare($sql1);
				$sth1->execute($bid,$tid);

				$sql2 = "INSERT IGNORE INTO `$tbl_bookmark_tags`
							SET bookmark_id = ?,
							tag_id = ?";
				$sth2 = $dbh->prepare($sql2);
				$sth2->execute($bid,$check);
			}

			# remove the old one
			$sql = "DELETE FROM `$tbl_tags` WHERE id = ?";
			$sth = $dbh->prepare($sql);
			$sth->execute($oldTagId);
		}
		else {
			# just rename the tag
			$sql = "UPDATE $tbl_tags SET name = ? WHERE id = ?";
			$sth = $dbh->prepare($sql);
			$sth->execute($newTag,$oldTagId);
		}

		print '<span style="color: green;">Done !</span>';
	}
	elsif(param("doDelete") && param("deleteTag") && param("moveTo")) {
		print '<p>Delete...</p>';

		# this moves the selected tag and its bookmarks to the new one
		# the tag is still availbale in the DB

		my($delTagName,$moveToTagName) = (param('deleteTag'), param('moveTo'));

		if($delTagName && $moveToTagName && ($delTagName ne $moveToTagName)) {
			# get the ones with the old tag
			my $oldTagId = get_tag_id_by_name($delTagName);
			my $moveToTagId = get_tag_id_by_name($moveToTagName);
			if($oldTagId && $moveToTagId) {
				$sql = "UPDATE IGNORE `$tbl_bookmark_tags`
							SET `tag_id` = ?
							WHERE `tag_id` = ?";
				$sth = $dbh->prepare($sql);
				$sth->execute($moveToTagId,$oldTagId);
			}
		}

		print '<span style="color: green;">Done !</span>';
	}

	print '<h2>Rename Tag</h2>';
	print '<form method="post" action="">';
	print '<select name="rename">';
	show_tags(1);
	print '</select>';
	print '<input type="text" name="newName" value="" />';
	print '<input type="submit" value="Rename" />';
	print '<input type=hidden name="op" value="tags">';
	print '<input type=hidden name="save" value="yes">';
	print '</form>';

	print '<h2>Delete Tag</h2>';
	print '<form method="post" action="">';
	print "Delete Tag";
	print '<select name="deleteTag">';
	show_tags(1);
	print '</select><br />';
	print "and move to:";
	print '<select name="moveTo">';
	show_tags(1);
	print '</select>';
	print '<input type=hidden name="op" value="tags">';
	print '<input type=hidden name="doDelete" value="yes">';
	print '<input type="submit" value="Delete and move" />';
	print '</form>';

	print '<h2>Show bookmarks without a tag</h2>';
	print '<p><a href="'.$site_url.'/insipid.cgi?tag=empty">Show me the bookmarks</a></p>';
}

# Display the tag list.  Takes one parameter for the mode - 0 is for the
# sidebar, 1 is for a SELECT box. TODO: Cache the actual result set so
# that when there's more than one tag list on a page we only hit the database
# once.
sub show_tags {
	my ($mode) = shift;
	if(!defined($mode)) { $mode = 0; }

	my ($sql, $sth);
	if($mode eq 0) { print "<div id=\"leftside\">"; }

	my $tag = url_param('tag');

	#if()) {
		# find the tags which have been used with this tags too
		my $tagstring = $tag;
		chomp($tagstring);
		$tagstring =~ s/ /','/g;

		# get the bookmarks first which have those tags
		$sql = "SELECT bm.id FROM $tbl_bookmarks as bm
				INNER JOIN $tbl_bookmark_tags AS bt ON bm.id = bt.bookmark_id
				INNER JOIN $tbl_tags AS t ON t.id = bt.tag_id
			WHERE 1";
		$sql .= " AND t.name IN ('$tagstring')" if $tag;

		$sql .= " AND (bm.access_level = 1) " if(logged_in() eq 0);

		$sth = $dbh->prepare($sql);
		$sth->execute;

		if($sth->rows ne 0) {
			my @bids;
			while(my($id) = $sth->fetchrow_array()) {
				push(@bids,$id);
			}

			if(@bids) {
				# there are results
				# get the tags for the tag list
				my $bids = join(',',@bids);

				$sql = "SELECT t.name, count(*) FROM $tbl_bookmarks AS bm
					INNER JOIN $tbl_bookmark_tags AS bt
						ON (bm.id = bt.bookmark_id)
					INNER JOIN $tbl_tags AS t
						ON (t.id = bt.tag_id)
					WHERE 1";

				$sql .= " AND bm.id IN ($bids) AND t.name NOT IN ('$tagstring')" if $tag;
				$sql .= " AND (bm.access_level = 1) " if(logged_in() eq 0);

				$sql .= " GROUP BY t.name
					ORDER BY t.name";
				#print $sql;
				$sth = $dbh->prepare($sql);
				$sth->execute;
				if($sth->rows ne 0) {

					if($mode eq 0) {
						print '<div id="taglist" style="">';
						print '<table cellpadding="0" cellspacing="0" ';
						print 'class="tagsummarytable"><tbody>';
						print '<tr><th colspan="2">Tags</th></tr>';
					}


					while(my @rs = $sth->fetchrow_array()) {
						my $link = $tag_url.$rs[0];

						if($mode eq 0) {
							print "<tr><td class=\"tagtabletext\">($rs[1])</td>";
							print "<td class=\"tagtabletext\"><a href=\"$link\">$rs[0]</a></td></tr>\n";
						} else {
							print "<option name=\"$rs[0]\">$rs[0]</option>";
						}
					}

					if($mode eq 0) {
						print "</tbody></table></div>";
						print "</div>";
					}

					return;
				}
			}
		}
	#}



}

# Get a string representing a URLs tags
sub get_tags {
	my ($url) = (@_);
	my @tags = get_tags_list($url);

	my $rv = "";
	foreach (@tags) {
		$rv = "$rv $_";
	}

	# Trim leading whitespace
	$rv =~ s/^\s+//;
	return $rv;
}

# Get a list of the tags for a given URL id
sub get_tags_list {
	my ($url) = (@_);
	my $sql = "select $tbl_tags.name from $tbl_tags
			inner join $tbl_bookmark_tags on
				($tbl_tags.id = $tbl_bookmark_tags.tag_id)
			inner join $tbl_bookmarks on
				($tbl_bookmark_tags.bookmark_id = $tbl_bookmarks.id)
			where ($tbl_bookmarks.url = ?)";

	my $sth = $dbh->prepare($sql);
	$sth->execute($url);

	my @tags;
	while(my @r = $sth->fetchrow_array) {
		push(@tags, $r[0]);
	}

	return @tags;
}

# Sets tags for a bookmark.  Takes a bookmark ID and a string
# representing the tags as parameters.
sub set_tags {
	my ($bookmark_id, $tag_string) = (@_);

	check_access();

	my @tags = split(" ", $tag_string);

	# Clear old tags first.
	my $sql = "delete from $tbl_bookmark_tags where (bookmark_id = ?)";
	my $sth = $dbh->prepare($sql);
	$sth->execute($bookmark_id);

	foreach my $cur (@tags) {
	    # check if this tag exists in tags table
	    my $sql = "select count(id) from $tbl_tags where (name = ?)";
	    my $sth = $dbh->prepare($sql);
	    $sth->execute($cur);
	    my @rv = $sth->fetchrow_array;
	    my $tagcount = $rv[0];

	    # or create a new tag
	    if ($tagcount < 1) {
			my $sql = "insert into $tbl_tags (name) values(?)";
			my $sth = $dbh->prepare($sql);
			$sth->execute($cur);
	    }

	    # and fetch the tag ID
	    $sql = "select id from $tbl_tags where (name = ?)";
	    $sth = $dbh->prepare($sql);
	    $sth->execute($cur);
	    my $tid = $sth->fetchrow_array;

	    $sql = "insert into $tbl_bookmark_tags(bookmark_id, tag_id)
		  values( ? , ? )";
	    $sth = $dbh->prepare($sql);
	    $sth->execute($bookmark_id, $tid);
	}
}

# check if given word is an existing tag
# if so return the tag id
sub get_tag_id_by_name {
	my ($tag) = (@_);

	my($tagId, $sql, $sth, @result);

	if($tag) {
		$sql = "select id from `$tbl_tags` where (name = ?)";
		$sth = $dbh->prepare($sql);
		$sth->execute($tag);
		@result = $sth->fetchrow_array();
		$tagId = $result[0];
	}

	return $tagId;
}

1;
__END__
