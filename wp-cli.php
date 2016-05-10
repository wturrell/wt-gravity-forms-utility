<?php

class GFUtility_Command extends WP_CLI_Command {

	/**
	 * Maximum number of entries to return (Gravity Forms defaults to 20)
	*/
	private static $max_entries = 200;

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
