#!/usr/bin/perl
#
# Copyright (C) 2008 Luke Reeves
#
# Copyright (C) 2012 Johannes Keßler
# https://github.com/jumpin-banana/insipid
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

#http://search.cpan.org/~wonko/HTML-Template-2.94/
use HTML::Template;

# debug. Send errors to the browser
use CGI::Carp qw(warningsToBrowser fatalsToBrowser);

if(!-e "insipid-config_.cgi") {
	print $html->header('text/html');
	print $html->start_html();
	print $html->h1("Configuration file missing");
	print $html->end_html();
}