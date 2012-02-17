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
use URI::Escape;

use Data::Dumper;

@ISA = qw(Exporter);

@EXPORT = qw(
show_stats
);

sub show_stats {
	&groupByDomain;
}

sub groupByDomain {
	my $url = shift;

	my ($sql, $sth, @row);
	my %domainGroup = ();
	my @invalidDomains;

	if($url) {

	}

	# keep order otherwise the while will not work
	$sql = "SELECT `id`, `url`,
					`linkcheck_status`,
					`linkcheck_date`
			FROM `$tbl_bookmarks` ORDER BY `url`";
	$sth = $dbh->prepare($sql);
	$sth->execute;

	if($sth->rows ne 0) {
		print "<h3> Invalid URLs after last linkcheck</h3>";
		print "<ul>";
		while(@row = $sth->fetchrow_array()) {
			my $uri = URI->new($row[1]);

			if($row[2] eq 0) {
				print "<li><a href='$site_url/insipid.cgi?op=edit_bookmark&id=$row[0]'>".$row[1]."</a> (<a href='$site_url/insipid.cgi?op=delete_bookmark&id=$row[0]'>delete</a>)</li>";
			}

			if($domainGroup{$uri->host}) {
				$domainGroup{$uri->host}++;
			} else {
				$domainGroup{$uri->host} = 1;
			}
		}
		print "</ul>";

		if(%domainGroup) {

			print "<h3>Bookmarks grouped by domain</h3>";
			print "<table cellpadding='2' cellspacing='0'>";
			print "<tr><th>Domain</th><th>Count</th></tr>";
			#for(sort keys %domains) {
			foreach (reverse sort { $domainGroup{$a} <=> $domainGroup{$b} } keys %domainGroup ) {

				print "<tr><td><a href='$site_url/insipid.cgi?bydomain=".uri_escape($_)."'>$_</a></td><td>$domainGroup{$_}</td></tr>";
			}
			print "</table>";
		}
	}
}

1;
__END__
