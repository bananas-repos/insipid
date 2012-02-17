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

package Insipid::Database;

use strict;
use warnings;

use Insipid::Config;
use Insipid::Schemas;

use DBI qw/:sql_types/;;
use vars qw($version);

use Exporter ();
our (@ISA, @EXPORT);

@ISA = qw(Exporter);
@EXPORT = qw($dbname $dbuser $dbpass $dsn $dbh $dbtype get_option
	install $version $tag_url $feed_url $full_url $snapshot_url
	export_options $dbprefix);

our ($dsn, $dbh, $dbname, $dbuser, $dbpass, $dbhost, $snapshot_url,
	$dbtype, $tag_url, $feed_url, $full_url, $dbprefix);

$dbname = getconfig('dbname');
$dbuser = getconfig('dbuser');
$dbpass = getconfig('dbpass');
$dbhost = getconfig('dbhost');

$dbtype = 'mysql';

$dsn = "DBI:$dbtype:dbname=$dbname;host=$dbhost";
$dbh = DBI->connect($dsn, $dbuser, $dbpass, { 'RaiseError' => 1, 'PrintError' => 1}) or die $DBI::errstr;

my %options;

sub export_options {
	my ($writer) = (@_);
	my ($sth);

	$writer->startTag('options');
	$sth = $dbh->prepare("select name, value from $tbl_options");
	$sth->execute();
	while(my $row = $sth->fetchrow_hashref) {
		if($row->{name} ne 'version') {
			$writer->emptyTag('option',
				'name' => $row->{name},
				'value' => $row->{value});
		}
	}

	$writer->endTag('options');
}

# this function is special for every version.
# there is no generic function for upgrades
# so changes to the DB needs to be saved here too
# in the next version change this
sub dbupgrade {


	my $sql = "ALTER TABLE `$tbl_options` ADD COLUMN `pos` int(10) NOT NULL AFTER `value`;";
	my $sth = $dbh->prepare($sql);
	$sth->execute;

	$sql = "UPDATE $tbl_options SET pos = ? where (name = ?)";
	$sth = $dbh->prepare($sql);
	$sth->execute(3, 'feed_name');
	$sth->execute(1, 'site_name');
	$sth->execute(20, 'proxy_host');
	$sth->execute(21, 'proxy_port');
	$sth->execute(2, 'public_searches');
	$sth->execute(90, 'use_rewrite');
	$sth->execute(9999, 'version');


	$sql = "INSERT INTO `$tbl_options` ( `name`, `value`, `description`, `pos`)
			VALUES ( 'feed_num', '10', 'How many feed entries per default (0 = all)', '4')";
	$sth = $dbh->prepare($sql);
	$sth->execute;

	$sql = "ALTER TABLE `$tbl_bookmarks`
				ADD COLUMN `linkcheck_status` int(1) NOT NULL AFTER `access_level`,
				ADD COLUMN `linkcheck_date` int(10) NOT NULL AFTER `linkcheck_status`;";
	$sth = $dbh->prepare($sql);
	$sth->execute;

	$sql = "update $tbl_options set value = ? where (name = ?)";
	$sth = $dbh->prepare($sql);
	$sth->execute($version, 'version');

	return;
}

sub get_option {
	my ($name) = (@_);

	if(keys (%options) == 0) {
		reload_options();
	}

	# Determine if we need to upgrade the database
	if($version ne $options{'version'}) {
		print STDERR "Upgrading schema from $options{'version'} to $version.\n";
		dbupgrade();
		reload_options();
	}

	return $options{$name};
}

sub reload_options {
	my $sql = "select name, value from $tbl_options";
	my $sth = $dbh->prepare($sql);
	$sth->execute() or die $DBI::errstr;

	while(my $hr = $sth->fetchrow_hashref) {
		$options{$hr->{'name'}} = $hr->{'value'};
	}
}

# This configures the URLs in the application to support mod_rewrite or
# a webserver sans mod_rewrite.
if(get_option('use_rewrite') eq 'yes') {
	$tag_url  	= $site_url . '/bookmarks/';
	$feed_url 	= $site_url . '/feeds/bookmarks';
	$full_url 	= $site_url . '/bookmarks';
	$snapshot_url	= $site_url . '/snapshot/';
} else {
	$tag_url  	= 'insipid.cgi?tag=';
	$feed_url 	= $site_url . '/insipid.cgi?op=rss&tag=';
	$full_url 	= $site_url . '/insipid.cgi';
	$snapshot_url	= 'insipid.cgi?op=viewsnapshot&md5=';
}


1;
__END__
