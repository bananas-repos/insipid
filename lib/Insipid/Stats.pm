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

package Insipid::Stats;

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

use Data::Dumper;

@ISA = qw(Exporter);

@EXPORT = qw(
show_stats
);

sub show_stats {
	&findSimilarities;
}

sub findSimilarities {
	my $url = shift;

	my ($sql, $sth, @row);
	my %domains = ();

	if($url) {

	}

	$sql = "SELECT `url` FROM `$tbl_bookmarks` ORDER BY `url`";
	$sth = $dbh->prepare($sql);
	$sth->execute;

	if($sth->rows ne 0) {
		while(@row = $sth->fetchrow_array()) {
			#print $row['url']."<br />";
			my $uri = URI->new($row['url']);
			#print $uri->host."<br />";
			if($domains{$uri->host}) {
				$domains{$uri->host}++;
			}
			else {
				$domains{$uri->host} = 1;
			}
		}

		if(%domains) {



			print "<table cellpadding='2' cellspacing='0'>";
			print "<tr><th>Domain</th><th>Count</th></tr>";
			#for(sort keys %domains) {
			foreach (reverse sort { $domains{$a} <=> $domains{$b} } keys %domains ) {
				print "<tr><td>$_</td><td>$domains{$_}</td></tr>";
			}
			print "</table>";
		}
	}
}

1;
__END__
