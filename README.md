# Dayton Metro Chorus 2025

A WordPress block-theme child project based on the Dayton Metro Barbershop Chorus homepage.

## Requirements

- WordPress 6.7 or newer
- The Twenty Twenty-Five parent theme

## Install

This folder belongs at `wp-content/themes/dmbc-2025`. Activate **Dayton Metro Chorus 2025** under **Appearance > Themes**. The theme supplies its own front-page template, header, and footer; other templates inherit from Twenty Twenty-Five.

The front page reproduces the public site's welcome, upcoming-events link, member invitation, rehearsal details, and recent news sections. Update the navigation destinations in `parts/header.html` and `parts/footer.html` if the site's page slugs differ. Recent news is populated from WordPress posts. The included chorus logo is stored in `assets/dayton-metro-logo.jpg`.

## Tests

Install the development dependency with `composer install`, then run `composer test` and `composer lint`. The PHPUnit 13 test suite requires PHP 8.3 or newer; the theme itself supports PHP 8.0 or newer.

## GitHub Actions

Pull requests and pushes to `main` run PHP lint and the unit tests on PHP 8.3, 8.4, and 8.5. Publishing a GitHub Release runs the same checks, installs production dependencies, then attaches `dmbc-2025.zip`. The ZIP contains the theme and its updater dependency in a `dmbc-2025/` directory and can be installed from **Appearance > Themes > Add New > Upload Theme**. Install Twenty Twenty-Five before activating the child theme.