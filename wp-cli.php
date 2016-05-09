<?php

class GFUtility_Command extends WP_CLI_Command {

	/**
	 * Notifications
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
	 * ## EXAMPLES
	 *      wp gfutil notification 1 list
	 */
	function notification( $args, $assoc_args ) {
		list( $form_id, $action ) = $args;

		if ( ! class_exists( 'GFAPI' ) ) {
			WP_CLI::error( 'Please install and activate the Gravity Forms plugin. ');
			
			return;
		}
		
		// Load form
		$form = GFAPI::get_form( $form_id );

		if ( ! $form ) {
			WP_CLI::error( 'Cannot find form matching specified ID.' );

			return;
		}

		WP_CLI::line( 'Found form: ' . $form['id'] . ': ' . $form['title'] );

		// Load entries
		$search_criteria = [ 'status' => 'active' ];

		$date_fields = [ 'start_date', 'end_date' ];

		foreach ( $date_fields as $df ) {
			if ( isset( $assoc_args[ $df ] ) ) {
				$search_criteria[ $df ] = $assoc_args[ $df ];
			}
		}

		$entries = GFAPI::get_entries( $form['id'], $search_criteria );   // i.e. not Spam or Trash

		if ( ! count( $entries ) ) {
			WP_CLI::error( 'No entries found for this form.' );
		}

		WP_CLI::line( 'Entries: ' . count( $entries ) );

		foreach ( $entries as $entry ) {
			switch ( $action ) {
				case 'send':
					WP_CLI::line( 'Sending notification for entry ' . $entry['id'] );
					GFAPI::send_notifications( $form, $entry );
					break;
				case 'list':
				default:
					WP_CLI::line( self::entry_summary( $entry ) );
					break;
			}	
		}
	}

	private function entry_summary( $entry ) {
		$format = "%6d %20s %10s \n";
		return sprintf( $format, $entry['id'], $entry['date_created'], $entry['status'] );
	}

}

WP_CLI::add_command( 'gfutil', 'GFUtility_Command' );
