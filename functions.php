<?php
/**
 * Theme setup.
 *
 * @package Dmbc2025
 */

declare(strict_types=1);

namespace Dmbc\DaytonMetro2025;

use YahnisElsts\PluginUpdateChecker\v5p7\Vcs\PluginUpdateChecker;
use YahnisElsts\PluginUpdateChecker\v5p7\Vcs\GitHubApi;

/**
 * Register the theme specifics.
 *
 * @return void
 */
function init(): void {

	$autoload = __DIR__ . '/vendor/autoload.php';
	if ( \is_readable( $autoload ) ) {
		require_once $autoload;
	}

	if ( ! \defined( 'DMBC_THEME_TESTING' ) && \class_exists( PluginUpdateChecker::class ) && \class_exists( GitHubApi::class ) ) {
		$github_api = new GitHubApi( 'https://github.com/zagrev/dmbc-2025' );
		$github_api->enableReleaseAssets();
		$update_checker = new PluginUpdateChecker( $github_api, __FILE__ );
	}

	\add_action(
		'wp_enqueue_scripts',
		static function (): void {
			\wp_enqueue_style(
				'dmbc-2025-style',
				\get_stylesheet_uri(),
				array(),
				(string) \filemtime( \get_stylesheet_directory() . '/style.css' )
			);
		}
	);

	$members_visibility = static function ( string $block_content, array $block ): string {
		if ( 'Members Only' !== ( $block['attrs']['label'] ?? '' ) ) {
			return $block_content;
		}

		if ( ! \is_user_logged_in() ) {
			return '';
		}

		return \in_array( 'um_member', \wp_get_current_user()->roles, true ) ? $block_content : '';
	};
	foreach ( array( 'core/navigation-link', 'core/navigation-submenu' ) as $block_name ) {
		\add_filter( 'render_block_' . $block_name, $members_visibility, 10, 2 );
	}

	\add_filter(
		'render_block_data',
		static function ( array $block ): array {
			if ( 'core/navigation-link' !== ( $block['blockName'] ?? '' ) ) {
				return $block;
			}

			$classes = \preg_split( '/\s+/', \trim( $block['attrs']['className'] ?? '' ) );
			if ( \in_array( 'dmbc-nav-admin', $classes, true ) ) {
				$block['attrs']['url'] = \admin_url();
			} elseif ( \in_array( 'dmbc-nav-logoff', $classes, true ) ) {
				$block['attrs']['url'] = \wp_logout_url( \home_url( '/' ) );
			}

			return $block;
		}
	);
}
init();
