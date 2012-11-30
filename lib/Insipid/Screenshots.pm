#!/usr/bin/perl
#
# Copyright (C) 2012 jumpin.banana
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

package Insipid::Screenshots;

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

use URI;
use URI::Escape;
use POSIX qw/strftime/;
use File::Basename;

@ISA = qw(Exporter);

@EXPORT = qw(
show_screenshots
);

sub show_screenshots {

	if(defined(param('delete')) && defined(param('hash'))) {
		my $_md5 = param('hash');
		if($_md5 ne '') {
			unlink("./screenshots/$_md5.png");
			print "<p style='color: green'>Screenshot deleted.</p>";
		}
	}

	my @files = <./screenshots/*.png>;
	if(@files) {
		print '<table>';
		print '<tr><th>Screenshot</th><th>Date</th><th>Bookmark</th><th>Options</th></tr>';
		foreach my $file (@files) {
			# some file informations
			my ($dev, $ino, $mode, $nlink, $uid, $gid, $rdev, $size, $atime,
    			$mtime, $ctime, $blksize, $blocks) = stat($file);

			my $md5Hash = basename($file,  ".png");

			# get the bookmark
			my $sql = "select $tbl_bookmarks.url, $tbl_bookmarks.id from $tbl_bookmarks
						where ($tbl_bookmarks.md5 = ?)";
			my $sth = $dbh->prepare($sql);
			$sth->execute($md5Hash);
			my @r = $sth->fetchrow_array;

			my $bookmark = $r[0];
			my $bookmarkID = $r[1];

			print '<tr>';
			print "<td><a href='".$file ."'>Screenshotfile</a></td>";
			print "<td>".scalar localtime($mtime)."</td>";
			print "<td><a href='$bookmark'>$bookmark</a></td>\n";
   			print "<td><a href='$site_url/insipid.cgi?op=screenshots&delete=1&hash=$md5Hash'>delete</a>, <a href='$site_url/insipid.cgi?op=screenshot&id=$bookmarkID'>refresh</a></td>\n";
   			print '</tr>';
   		}
   		print '</table>';
   	}
   	else {
   		print "<p>No screenshots available yet.</p>";
   	}
}
