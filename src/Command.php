<?php
/**
 * Defines the WP-CLI command for WP Orphans.
 *
 * @package LiquidWeb/WPOrphans
 * @author  Liquid Web
 */

namespace LiquidWeb\WPOrphans;

use WP_CLI;
use WP_CLI_Command;
use WP_Query;

class Command {

	/**
	 * Locate orphaned media in the current WordPress site's media library.
	 *
	 * ## OPTIONS
	 *
	 * [--cleanup]
	 * : If set, orphaned media will automatically be deleted.
	 *
	 * @subcommand remove-orphans
	 */
	public function __invoke( $args = [], $assoc_args = [] ) {
		$assoc_args = wp_parse_args( $assoc_args, [
			'cleanup' => false,
		] );

		// Collect all known files.
		$upload_dir = wp_upload_dir();
		$filesystem = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $upload_dir['basedir'], \FilesystemIterator::SKIP_DOTS )
		);
		$files      = array_keys( iterator_to_array( $filesystem ) );

		// Loop through attachments in the database and remove corresponding entries from $files.
		$query_args = [
			'post_type'              => 'attachment',
			'post_status'            => 'any',
			'posts_per_page'         => 50,
			'paged'                  => 1,
			'fields'                 => 'ids',
			'update_post_term_cache' => false,
		];
		$query      = new WP_Query( $query_args );
		$count      = 0;

		while ( $query->have_posts() ) {
			$query->the_post();

			$metadata = wp_get_attachment_metadata( $query->get_the_ID() );
			$batch    = [];

			// Original file.
			$batch[] = $upload_dir['basedir'] . '/' . $metadata['file'];

			// Thumbnails.
			foreach ( $metadata['sizes'] as $size ) {
				$batch[] = str_replace( basename( $metadata['file'] ), $size['file'], $upload_dir['basedir'] . '/' . $metadata['file'] );
			}

			$files = array_diff( $files, $batch );

			$count++;

			if ( 0 === $count % 50 ) {
				$query_args['paged']++;
				$query = new WP_Query( $query_args );
			}
		}

		// Once we've looped through the files, remove what's left orphaned.
		foreach ( $files as $file ) {
			if ( $assoc_args['cleanup'] ) {
				wp_delete_file( $file );
			}

			WP_CLI::log( str_replace( $upload_dir['basedir'] . '/', '', $file ) );
		}
	}
}
