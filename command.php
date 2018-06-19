<?php

use LiquidWeb\WPOrphans\Command;

require_once __DIR__ . '/src/Command.php';

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

WP_CLI::add_command( 'media remove-orphans', Command::class );
