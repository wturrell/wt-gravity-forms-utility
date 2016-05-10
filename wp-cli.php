<?php

class GFUtility_Command extends WP_CLI_Command {

	/**
	 * Maximum number of entries to return (Gravity Forms defaults to 20)
	*/
	private static $max_entries = 200;

	/**
	 * Summarise notification settings for each form
	 *
	 * ## OPTIONS
	 * 
	 * [--forms=<form_ids>]
	 * : Limit to specified form(s). Gravity Form numeric ID, comma-separated 
	 *
	 * [--sort=<sort>]
	 * : Field to sort by. Options: id, title
	 *
	 * ## EXAMPLES
	 *      wp gfutil notifications 
	 *      wp gfutil notifications --forms=1
	 *      wp gfutil notifications --forms=1,2,3
	 *      wp gfutil notifications --sort=title
	 */
	function notifications($args, $assoc_args) {
		// Get all forms and parse the data

		$errors = [];   // Error code is a placeholder. I've used it in customised versions of this code but 
						// am not running any specific validation of notification settings in this plugin yet.

		$parsed_forms = [];

		// Has user asked for only certain forms?
		$chosen_forms = [];
		
		if (isset($assoc_args['forms'])) {
			$chosen_forms = explode( ',', trim( $assoc_args['forms'] ) );
		}
		
		// Read in forms
		$forms = GFAPI::get_forms();

		foreach ( $forms as $form ) {

			if ( ! empty ( $chosen_forms)) {
				// Skip forms as appropriate if we're filtering by ID
				if ( ! in_array( $form['id'], $chosen_forms ) ) {
					continue;	
				}
			}
			
			$result = self::parse_notifications( $form, $errors );

			if ( $result ) {
				$parsed_forms[] = $result;
			}
		}

		// Check: have we actually got any forms to look at?
		if ( empty ( $parsed_forms ) ) {
			WP_CLI::error( 'No (active) forms found.' );
		}

		switch ( $assoc_args['sort'] ) {
			case 'title':
				self::sort_forms( $parsed_forms, $assoc_args['sort'] );
				break;
		}

		// Display output
		foreach ( $parsed_forms as $form ) {
			self::notification_summary( $form );
		}

		if ( ! empty ( $errors ) ) {
			WP_CLI::warning( 'Errors found: ' . count( $errors ) );

			foreach ( $errors as $e ) {
				echo $e, "\n";
			}
		}

	}

	/**
	 * Sort forms array by specified field 
	 * 
	 * @param array $parsed_forms
	 * @param string $field
	 * 
	 * @author william@wturrell.co.uk
	 */
	private function sort_forms( array &$parsed_forms, $field = 'title' ) {
		$field_in_order = [ ];

		foreach ( $parsed_forms as $key => $row ) {
			$field_in_order[ $key ] = $row[ $field ];
		}

		array_multisort( $field_in_order, SORT_ASC, $parsed_forms );
	}
	
	
	/**
	 * Read GravityForms form, store the key variables and loop through the notifications
	 * Check notifications for common errors
	 *
	 * @param array $form a Form object from GFAPI
	 * @param array $errors passed as reference, so we can store any errors we find
	 *
	 * @return array
	 * 
	 * @author william@wturrell.co.uk
	 */
	private function parse_notifications( array $form, array &$errors ) {
		$output = [
			'id'            => $form['id'],
			'title'         => $form['title'],
			'notifications' => [],
		];

		foreach ( array_values( $form['notifications'] ) as $not ) {
			$output['notifications'][] = $not;
		}

		return $output;
	}

	/**
	 * Print a summary of the notification fields for each form.
	 *
	 * @param array $form returned by GFAPI::get_form()
	 * 
	 * @author william@wturrell.co.uk
	 */
	private function notification_summary( array $form ) {
		$fields = [
			'Subject'   => 'subject',
			'Send To'   => 'to',
			'BCC'       => 'bcc',
			'From'      => 'from',
			'From Name' => 'fromName',
			'Reply To'  => 'replyTo',
		];

		foreach ( $form['notifications'] as $not ) {

			// Print form title followed by the name of the notification(s)
			$title_format = "%4s  %-60s\n";
			printf( $title_format, '#' . $form['id'], $form['title'] . ' - ' . $not['name'] );

			echo str_repeat( '-', 80 ), "\n";

			// Is send to an email, a email field or custom routing?
			if ( $not['toType'] === 'field' ) {
				// They're using an email field from the form
				$notification_format = "%15s: %-60s\n";
				printf($notification_format, 'Send To', 'Value of field #' . $not['to']);
				
				unset($not['to']);  // prevent it being displayed again as a number
			} elseif (! empty($not['routing'])) {
				// They're using rules-based routing
				$notification_format = "%15s: %-60s\n";
				printf($notification_format, 'Send To', 'Using custom routing');
			}
			
			foreach ( $fields as $k => $v ) {
				self::print_notification_line( $not, $k, $v );
			}

			echo "\n";
		}
	}

	/**
	 * Displays one line of the status report (but only if the specified field isn't empty)
	 *
	 * @param array $data parsed form notification data
	 * @param string $title e.g. "Send To", "From"
	 * @param string $field e.g. "to", "from"
	 * 
	 * @author william@wturrell.co.uk
	 */
	private function print_notification_line( array $data, $title, $field ) {
		$notification_format = "%15s: %-60s\n";

		if ( ! empty( $data[ $field ] ) ) {
			printf( $notification_format, $title, $data[ $field ] );
		}
	}
	
	/**
	 * Renotify
	 * Send entry notifications again. Filter by form and date/time.
	 *
	 * ## OPTIONS
	 * <form_id>
	 * : Gravity Form ID
	 *
	 * <action>
	 * : list OR send
	 * 
	 * [--start_date=<start_date>]
	 * : Inclusive (time optional) e.g. --start_date="2016-05-09 19:00:00"
	 * 
	 * [--end_date=<end_date>]
	 * : Inclusive (time optional) e.g. --end_date="2016-12-31"
	 *
	 * [--max_entries=<number>]
	 * : Maximum entries to return at once. Increase this or send in batches by narrowing date window
	 *
	 * ## EXAMPLES
	 *      wp gfutil renotify 1 list
	 *      wp gfutil renotify 1 send
	 *      wp gfutil renotify 1 send --start_date=2015-01-01
	 *      wp gfutil renotify 1 send --start_date=2015-01-01 --end_date=2015-01-04 
	 */
	function renotify( $args, $assoc_args ) {
		list( $form_id, $action ) = $args;

		if ( ! class_exists( 'GFAPI' ) ) {
			WP_CLI::error( 'Please install and activate the Gravity Forms plugin. ');
			
			return;
		}

		// Do they want to override max entries returned?
		if ( isset( $assoc_args['max_entries'] ) ) {
			self::$max_entries = (int) $assoc_args['max_entries'];
		}

		// Load form
		$form = GFAPI::get_form( $form_id );

		if ( ! $form ) {
			WP_CLI::error( 'Cannot find form matching specified ID.' );

			return;
		}

		WP_CLI::line( 'Form: #' . $form['id'] . ': ' . $form['title'] );

		// Load entries
		$search_criteria = [ 'status' => 'active' ]; // i.e. not Spam or Trash

		$date_fields = [ 'start_date', 'end_date' ];

		foreach ( $date_fields as $df ) {
			if ( isset( $assoc_args[ $df ] ) ) {
				$search_criteria[ $df ] = $assoc_args[ $df ];
			}
		}

		$entries = GFAPI::get_entries( $form['id'], $search_criteria, null, [ 
			'offset' =>0,
			'page_size' => self::$max_entries
		]);

		$no_of_entries = count( $entries );
		
		if ( ! $no_of_entries ) {
			WP_CLI::error( 'No entries found for this form.' );
		}

		foreach ( $entries as $entry ) {
			switch ( $action ) {
				case 'send':
					WP_CLI::line( 'Sending notification for entry ' . $entry['id'] );
					GFAPI::send_notifications( $form, $entry );
					ob_end_clean();
					break;
				case 'list':
				default:
					WP_CLI::line( self::entry_summary( $entry ) );
					break;
			}	
		}
		
		WP_CLI::line( 'Entries: ' . $no_of_entries );
		
		if ( $no_of_entries === self::$max_entries ) {
			WP_CLI::warning( 'There MAY be more – you have reached the maximum returned ('.self::$max_entries .')');
		}
	}

	private function entry_summary( $entry ) {
		$format = "%6d %20s %10s";
		return sprintf( $format, $entry['id'], $entry['date_created'], $entry['status'] );
	}

}

WP_CLI::add_command( 'gfutil', 'GFUtility_Command' );
