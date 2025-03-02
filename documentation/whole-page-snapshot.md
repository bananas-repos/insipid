# Whole page screenshot of a website.

It uses browserless.io to generate a whole page screenshot of a link.
There is a free account which allows ap to 1000 requests per month.

# Usage

To use this feature edit the `config.php` and set `COMPLETE_PAGE_SCREENSHOT` to `true`
and provide your browserles.io API key to `COMPLETE_PAGE_SCREENSHOT_API_KEY`.

# Limitations and expectations

Every link and its target is different. The settings to generate the screenshot should work for the most of
them. But there are a lot of things which influence the result and can return in a not-so-good screenshot.
