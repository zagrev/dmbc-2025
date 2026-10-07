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