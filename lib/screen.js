/*
 * this file is iused by phantomjs (http://phantomjs.org/) to load the given bookmark and save the output to a file
 *
 * Copyright (C) 2012 jumpin.banana
 *
 */
var page = require('webpage').create(), system = require('system'), address, output;
page.settings.userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11';

if (system.args.length < 2) {
    console.log('Usage: screen.js URL Outputfilename');
    phantom.exit(1);
}
else {
	address = system.args[1];
    output = system.args[2];

	page.open(address, function (status) {
		if (status !== 'success') {
	        //console.log('Unable to access network');
	        phantom.exit(1);
	    } else {
    		window.setTimeout(function () {
    			//console.log('rendering');

				page.render(output);

				phantom.exit(0);
			}, 50);
	    }
	});
}