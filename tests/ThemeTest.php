<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ThemeTest extends TestCase {
	protected function setUp(): void {
		$GLOBALS['dmbc_theme_test_state']['enqueued'] = array();
		$GLOBALS['dmbc_theme_test_state']['logged_in'] = false;
		$GLOBALS['dmbc_theme_test_state']['current_roles'] = array();
	}

	public function test_members_only_link_is_hidden_for_guests(): void {
		$this->assertSame( '', $this->render_members_link() );
	}

	public function test_members_only_link_is_hidden_for_logged_in_non_members(): void {
		$GLOBALS['dmbc_theme_test_state']['logged_in'] = true;
		$GLOBALS['dmbc_theme_test_state']['current_roles'] = array( 'subscriber' );

		$this->assertSame( '', $this->render_members_link() );
	}

	public function test_members_only_link_is_visible_for_logged_in_members(): void {
		$GLOBALS['dmbc_theme_test_state']['logged_in'] = true;
		$GLOBALS['dmbc_theme_test_state']['current_roles'] = array( 'um_member' );
		$link = '<a href="/account/">Members Only</a>';

		$this->assertSame( $link, $this->render_members_link( $link ) );
	}

	public function test_other_navigation_links_are_not_filtered(): void {
		$block = array( 'attrs' => array( 'label' => 'About Us' ) );
		$content = '<a href="/about-us/">About Us</a>';

		$this->assertSame( $content, $this->navigation_filter()( $content, $block ) );
	}

	public function test_members_only_submenu_preserves_member_visibility(): void {
		$filter = $GLOBALS['dmbc_theme_test_state']['filters']['render_block_core/navigation-submenu'][0]['callback'];
		$block = array( 'attrs' => array( 'label' => 'Members Only' ) );
		$content = '<li>Members Only<ul><li>Learning Tracks</li></ul></li>';

		$this->assertSame( '', $filter( $content, $block ) );
		$GLOBALS['dmbc_theme_test_state']['logged_in'] = true;
		$GLOBALS['dmbc_theme_test_state']['current_roles'] = array( 'subscriber' );
		$this->assertSame( '', $filter( $content, $block ) );
		$GLOBALS['dmbc_theme_test_state']['current_roles'] = array( 'um_member' );
		$this->assertSame( $content, $filter( $content, $block ) );
		$this->assertSame( $content, $filter( $content, array( 'attrs' => array( 'label' => 'About Us' ) ) ) );
	}

	public function test_admin_and_logoff_links_use_wordpress_urls(): void {
		$filter = $GLOBALS['dmbc_theme_test_state']['filters']['render_block_data'][0]['callback'];
		$block = array(
			'blockName' => 'core/navigation-link',
			'attrs'     => array( 'className' => 'custom-class dmbc-nav-admin', 'url' => '/wp-admin/' ),
		);
		$this->assertSame( admin_url(), $filter( $block )['attrs']['url'] );
		$block['attrs']['className'] = 'dmbc-nav-logoff custom-class';
		$result = $filter( $block );
		$this->assertStringContainsString( 'action=logout&_wpnonce=test-logout-nonce', $result['attrs']['url'] );
		$this->assertSame( home_url( '/' ), $GLOBALS['dmbc_theme_test_state']['logout_redirect'] );
		$block['attrs']['className'] = 'ordinary-link';
		$this->assertSame( $block, $filter( $block ) );
		$block['blockName'] = 'core/group';
		$block['attrs']['className'] = 'dmbc-nav-logoff';
		$this->assertSame( $block, $filter( $block ) );
	}

	public function test_header_contains_members_submenu_with_all_six_links_in_order(): void {
		$header = file_get_contents( dirname( __DIR__ ) . '/parts/header.html' );
		$this->assertSame( 1, preg_match( '/<!-- wp:navigation-submenu (.*?)-->(.*?)<!-- \/wp:navigation-submenu -->/s', $header, $matches ) );
		$this->assertStringContainsString( '"label":"Members Only"', $matches[1] );
		preg_match_all( '/<!-- wp:navigation-link (\{.*?\}) \/-->/', $matches[2], $links );
		$attributes = array_map( static fn( string $json ): array => json_decode( $json, true, 512, JSON_THROW_ON_ERROR ), $links[1] );
		$this->assertSame( array( 'Learning Tracks', 'Song Lists', 'Member News', 'Roster', 'Admin', 'Logoff' ), array_column( $attributes, 'label' ) );
		$this->assertSame( array( '/learning-tracks/', '/song-lists/', '/member-news/', '/roster/' ), array_slice( array_column( $attributes, 'url' ), 0, 4 ) );
		$this->assertSame( array_fill( 0, 6, false ), array_column( $attributes, 'isTopLevelLink' ) );
	}

	public function test_theme_enqueues_its_stylesheet_with_a_file_version(): void {
		$callback = $GLOBALS['dmbc_theme_test_state']['actions']['wp_enqueue_scripts'][0]['callback'];
		$callback();

		$this->assertSame(
			array(
				'handle'  => 'dmbc-2025-style',
				'src'     => get_stylesheet_uri(),
				'deps'    => array(),
				'version' => (string) filemtime( dirname( __DIR__ ) . '/style.css' ),
				'media'   => 'all',
			),
			$GLOBALS['dmbc_theme_test_state']['enqueued'][0]
		);
	}

	public function test_theme_setup_does_not_leak_updater_variables_into_global_scope(): void {
		$this->assertArrayNotHasKey( 'github_api', $GLOBALS );
		$this->assertArrayNotHasKey( 'update_checker', $GLOBALS );
	}

	public function test_manrope_is_registered_and_bundled(): void {
		$theme = json_decode( file_get_contents( dirname( __DIR__ ) . '/theme.json' ), true, 512, JSON_THROW_ON_ERROR );
		$families = $theme['settings']['typography']['fontFamilies'];
		$manrope = array_values( array_filter( $families, static fn( array $family ): bool => 'manrope' === $family['slug'] ) )[0];
		$this->assertSame( '200 800', $manrope['fontFace'][0]['fontWeight'] );
		$this->assertFileExists( dirname( __DIR__ ) . '/assets/fonts/manrope/Manrope-VariableFont_wght.woff2' );
	}

	public function test_front_page_and_header_contain_expected_chorus_content(): void {
		$front_page = file_get_contents( dirname( __DIR__ ) . '/templates/front-page.html' );
		$header = file_get_contents( dirname( __DIR__ ) . '/parts/header.html' );

		$this->assertStringContainsString( 'Welcome!', $front_page );
		$this->assertStringContainsString( 'Members wanted', $front_page );
		$this->assertStringContainsString( 'St. Paul’s Episcopal Church', $front_page );
		$this->assertStringContainsString( 'wp:query', $front_page );
		$this->assertStringContainsString( '"label":"Members Only"', $header );
	}

	private function render_members_link( string $content = '<a href="/account/">Members Only</a>' ): string {
		$block = array( 'attrs' => array( 'label' => 'Members Only' ) );
		return $this->navigation_filter()( $content, $block );
	}

	private function navigation_filter(): callable {
		return $GLOBALS['dmbc_theme_test_state']['filters']['render_block_core/navigation-link'][0]['callback'];
	}
}