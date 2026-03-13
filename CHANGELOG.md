# Changelog

All notable changes to this project should be written here.

## v0.2.0

- Added plugin configuration page with configurable TacticalRMM base URL
- Added TacticalRMM action button rendering on Computer forms
- Improved compatibility by using broader GLPI hook registration and runtime checks
- Added English and French translation catalogs
- Improved action visibility for read-only profiles with safer placement logic
- Hardened release workflow to publish stable versioned archives without `v` prefix in plugin metadata

## Unreleased

- Refactored plugin classes into `src/` and aligned the Computer display hook with GLPI 11 callback conventions.
- Reworked the Computer action rendering to output a standard form row instead of moving DOM elements with JavaScript.
- Added plugin metadata assets and a dedicated plugin icon using GLPI-standard plugin filenames.
- Added plugin metadata links for Homepage, Get help, and Readme in plugin information.
- Added explicit GLPI plugin configuration registration (`config_page` + `menu_toadd`) and a `front/config.php` entry point for GLPI 11 compatibility.
- Extended configuration with a URL template field to support both TacticalRMM and MeshCentral link formats.
- Improved remote identifier resolution by accepting URL/link fields and full URL values from inventory rows.

## v0.1.0

- Initial public repository skeleton
- Added GitHub Actions workflow for `.tar.gz` and `.zip` release artifacts
- Added package script for local release builds
- Added starter README and contribution-friendly repo structure
