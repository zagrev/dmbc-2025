<?php

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}
define( 'DMBC_THEME_TESTING', true );

$GLOBALS['dmbc_theme_test_state'] = array(
	'actions'      => array(),
	'filters'      => array(),
	'enqueued'     => array(),
	'logged_in'    => false,
	'current_roles' => array(),
);

function add_action( string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1 ): bool {
	$GLOBALS['dmbc_theme_test_state']['actions'][ $hook_name ][] = compact( 'callback', 'priority', 'accepted_args' );
	return true;
}

function add_filter( string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1 ): bool {
	$GLOBALS['dmbc_theme_test_state']['filters'][ $hook_name ][] = compact( 'callback', 'priority', 'accepted_args' );
	return true;
}

function wp_enqueue_style( string $handle, string $src = '', array $deps = array(), string|bool|null $version = false, string $media = 'all' ): void {
	$GLOBALS['dmbc_theme_test_state']['enqueued'][] = compact( 'handle', 'src', 'deps', 'version', 'media' );
}

function get_stylesheet_uri(): string {
	return 'https://example.test/wp-content/themes/dmbc-2025/style.css';
}

function get_stylesheet_directory(): string {
	return dirname( __DIR__ );
}

function is_user_logged_in(): bool {
	return $GLOBALS['dmbc_theme_test_state']['logged_in'];
}

function wp_get_current_user(): object {
	return (object) array( 'roles' => $GLOBALS['dmbc_theme_test_state']['current_roles'] );
}

require_once dirname( __DIR__ ) . '/functions.php';