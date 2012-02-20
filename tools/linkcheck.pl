#!/usr/bin/perl -w
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


#
# check exiting bookmark entries if they are available
# ssl entries will not be checked...
# if they return a non 200 HTTP Status code they will be marked and visible
# in the stats overview
#

use warnings;
use strict;
use Getopt::Long;
use DBI;
use LWP::UserAgent;

BEGIN {
    binmode STDOUT, ':encoding(UTF-8)';
    binmode STDERR, ':encoding(UTF-8)';
}

use lib "../lib";
use Insipid::Config;
use Insipid::Database;
use Insipid::Bookmarks;

$|=1;

my $opt_help;
my $opt_link = "all";
my $opt_proxy;

# if no arguments passed
&usage if @ARGV < 1;

GetOptions(
	"help|h"		=> \$opt_help,
	"link=s"		=> \$opt_link,
	"proxy=s"		=> \$opt_proxy
) or die(&usage);

&usage if $opt_help;


#
# main
#
my $query = "SELECT `id`, `url` FROM `$tbl_bookmarks`";
$query .= " WHERE `linkcheck_status` = 1" if($opt_link eq "active");
$query .= " WHERE `linkcheck_status` = 0" if($opt_link eq "inactive");

my $sth = $dbh->prepare($query);
$sth->execute;
if($sth->rows ne 0) {
	my $ua = LWP::UserAgent->new;
	$ua->timeout(5);
	$ua->show_progress(1);
	$ua->agent("Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11");

	$ua->proxy(['http', 'ftp'], $opt_proxy) if $opt_proxy;

	$ua->ssl_opts('verify_hostname' => 0);
	$ua->protocols_allowed(undef); #  all are allowed

	$query = "UPDATE `$tbl_bookmarks`
				SET `linkcheck_status` = ?,
				`linkcheck_date` = ?
			WHERE `id` = ?";
	my $sthupdate = $dbh->prepare($query);

	while (my $hr = $sth->fetchrow_hashref) {
		print $hr->{url}." ";

		# ssl not working correctly so avoid those bookmarks
		next if $hr->{url} =~ m/https:/g;

		#my $response = $ua->head($hr->{url});
		my $response = $ua->get($hr->{url});
		my $status = 0;

		if ($response->is_success) {
			print "Ok !\n";
			$status = 1;
		}
		else {
			print $response->status_line."\n";
		}

		$sthupdate->execute($status,time(),$hr->{id});

	}
}


#
# functions
#

sub usage {
	print <<EOT
Usage: linkcheck.pl [OPTION]
Check the bookmark entries from insipid. Check if the URL returns a 200 Status
code. If so set the checkDate and result. Non 200 checks will be marked. Those
can be checked seperately

	-h, --help		display this help message
	--link=			all = check all links
				active = check only those which are not marked as inactive
				inactive = check inactive only
	--proxy=	proxy address if needed

EOT
;
	exit(1);
}

exit(0);
