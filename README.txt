=== Plugin Name ===
Contributors: wturrell
Tags: gravityforms, notifications
Requires at least: 4.5.2
Tested up to: 4.5.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Resend Gravity Forms notifications, filtered by form and optinal start/end dates. View notification settings.

== Requirements ==

Gravity Forms plugin installed.  http://www.gravityforms.com/
Tested with version 1.9.18

WP-CLI installed. http://wp-cli.org/

== Installation ==

1. Upload `wt-gravity-forms-utility` directory and it's contents to the `/wp-content/plugins/` directory
2. Activate the plugin: `wp plugin activate wt-gravity-forms-utility` OR via the 'Plugins' menu in WordPress admin

== Usage ==

Use the help command to view all settings.

wp help gfutil notifications

The 'notifications' command shows a summary of notification settings for all your forms.

wp help gfutil renotify

The 'renotify' command lets you resend batches of notification emails, filtered by form or start/end date.

Use the "list" option first.  When you're happy you have the correct forms, use "send" to trigger the emails.

You can specify --start_date and --end_date, which can include an optional time.

For the date/time formats Gravity Forms can understand, see the get_date_range_where() method in
/wp-content/plugins/gravity-forms/forms_model.php

== Limitations ==

By default, `renotify` returns a maximum of 200 entries at a time.
You can increase (or decrease) this with the --max_entries switch.

Alternatively, process records in batches by narrowing the date range.
(You may run out of memory with very high values.)

== Changelog ==

= 0.1.0 =
* New feature: `wp gfutil notifications` - view notification settings for all forms

= 0.0.2 =
* New feature: --max_entries option
* Change: notification command renamed 'renotify'
* Bugfix: entry list no longer double spaced
* Improvement: warn if max_entries reached ("There MAY be more")
* Improvement: show entry count at bottom of list (no need to scroll back up)

= 0.0.1 =
* First version.

