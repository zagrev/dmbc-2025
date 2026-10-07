<?php
/**
 * Theme setup.
 *
 * @package Dmbc2025
 */

declare(strict_types=1);

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		wp_enqueue_style(
			'dmbc-2025-style',
			get_stylesheet_uri(),
			array(),
			(string) filemtime( get_stylesheet_directory() . '/style.css' )
		);
	}
);

add_filter(
	'render_block_core/navigation-link',
	static function ( string $block_content, array $block ): string {
		if ( 'Members Only' !== ( $block['attrs']['label'] ?? '' ) ) {
			return $block_content;
		}

		if ( ! is_user_logged_in() ) {
			return '';
		}

		return in_array( 'um_member', wp_get_current_user()->roles, true ) ? $block_content : '';
	},
	10,
	2
);