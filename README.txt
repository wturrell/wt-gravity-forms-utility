=== Plugin Name ===
Contributors: wturrell
Tags: gravityforms, notifications
Requires at least: 4.5.2
Tested up to: 4.5.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Utility to resend batches of Gravity Forms notifications, filtered by form and optinal start/end dates.

== Requirements ==

Gravity Forms plugin installed.  http://www.gravityforms.com/
Tested with version 1.9.18

WP-CLI installed. http://wp-cli.org/

== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
2. Activate the plugin: `wp plugin activate wt-gravity-forms-utility` or via the 'Plugins' menu in WordPress admin
3. Place `<?php do_action('plugin_name_hook'); ?>` in your templates

== Usage examples ==

wp help gfutil renotify

This will give you all the available options.

Essentially you can use "list" (default) or "send".

I recommend you use "list" first to identify the correct forms, and only "send" when you
are confident you have the right ones.

You can specify --start_date and --end_date, which can include an optional time.

For the formats Gravity Forms can understand, see the get_date_range_where() method in
/wp-content/plugins/gravity-forms/forms_model.php

== Limitations ==

By default, a maximum of 200 entries are returned at a time.
You can increase (or decrease) this with the --max_entries switch.

Alternatively, process records in batches by narrowing the date range.
(You may run out of memory with very high values.)

== Changelog ==

= 0.0.2 =
* New feature: --max_entries option
* Change: notification command renamed 'renotify'
* Bugfix: entry list no longer double spaced
* Improvement: warn if max_entries reached ("There MAY be more")
* Improvement: show entry count at bottom of list (no need to scroll back up)

= 0.0.1 =
* First version.

