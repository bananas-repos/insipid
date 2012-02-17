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

use warnings;
use strict;
use Getopt::Long;
use DBI;

BEGIN {
    binmode STDOUT, ':encoding(UTF-8)';
    binmode STDERR, ':encoding(UTF-8)';
}

#push(@INC, "../lib");
use lib "../lib";
use Insipid::Config;
use Insipid::Database;
use Insipid::Bookmarks;

$|=1;

my $opt_help = 0;
my $opt_link = "all";

# if no arguments passed
&usage if @ARGV < 1;

GetOptions(
	"help|h"			=> \$opt_help,
	"link|l"			=> \$opt_link
) or die(&usage);

&usage if $opt_help;


#
# main
#
my $query = "SELECT `url` FROM `$tbl_bookmarks`";
$query .= " WHERE `linkcheck_status` = " if($opt_link == 1);
$query .= " WHERE `linkcheck_status` = " if($opt_link == 0);

print $query;


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
	-l, --link		all = check all links
				active = check only those which are not marked as inactive
				inactive = check inactive only

EOT
;
	exit(1);
}

exit(0);
