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

1. Upload directory and files to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Usage examples ==

wp help gfutil notification

This will give you all the available options.

Essentially you can use "list" (default) or "send".

I recommend you use "list" first to identify the correct forms, and only "send" when you
are confident you have the right ones.

You can specify --start_date and --end_date, which can include an optional time.

For the formats Gravity Forms can understand, see the get_date_range_where() method in
/wp-content/plugins/gravity-forms/forms_model.php

== Changelog ==

= 0.0.1 =
* First version.

