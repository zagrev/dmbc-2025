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

	\add_filter(
		'render_block_core/navigation-link',
		static function ( string $block_content, array $block ): string {
			if ( 'Members Only' !== ( $block['attrs']['label'] ?? '' ) ) {
				return $block_content;
			}

			if ( ! \is_user_logged_in() ) {
				return '';
			}

			return \in_array( 'um_member', \wp_get_current_user()->roles, true ) ? $block_content : '';
		},
		10,
		2
	);
};
init();
