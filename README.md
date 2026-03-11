# TacticalRMM Remote for GLPI

TacticalRMM Remote for GLPI is a lightweight plugin that adds a direct TacticalRMM remote access button to Computer assets in GLPI.

It is designed for environments where GLPI Agent inventories remote management data and technicians need a fast and reliable way to launch TacticalRMM Take Control directly from the asset page.

## Why this plugin exists

In many GLPI environments, remote management information is inventoried correctly, but technicians still need to switch to TacticalRMM and manually search for the device.

This plugin improves that workflow by displaying a clickable TacticalRMM button directly on the Computer form when a valid TacticalRMM remote identifier is available.

## Features

* Adds a TacticalRMM button on Computer assets
* Uses inventoried remote management data already present in GLPI
* Opens the device directly in TacticalRMM Take Control
* Reduces manual search time for technicians
* Keeps the integration simple and lightweight
* Designed for easy deployment in Docker and standard GLPI installations

## How it works

When a computer has a TacticalRMM remote management entry inventoried in GLPI, the plugin builds a direct URL using your TacticalRMM server base address and the detected remote identifier.

Example format:

`https://rmm.example.com/takecontrol/<REMOTE_ID>`

This gives technicians one-click access to the device from the GLPI computer record.

## Current scope

| Area                    | Status  |
| ----------------------- | ------- |
| Computer asset button   | Yes     |
| TacticalRMM direct link | Yes     |
| Lightweight integration | Yes     |
| Ticket form integration | No      |
| Settings page           | Planned |
| Translation support     | Planned |

## Target use case

This plugin is intended for organizations that:

* use GLPI for inventory and asset management
* use TacticalRMM for remote administration
* collect remote management information through GLPI Agent
* want faster technician access from the GLPI asset page

## Installation

Download the latest release and extract it into your GLPI plugins directory.

Example:

`cd /var/www/html/glpi/plugins`
`curl -L -o tacticalrmmremote.tar.gz https://github.com/z0gg/glpi-tacticalrmmremote/releases/latest/download/tacticalrmmremote.tar.gz`
`tar -xzf tacticalrmmremote.tar.gz`
`rm -f tacticalrmmremote.tar.gz`

Then in GLPI:

* Go to Configuration
* Open Plugins
* Click Install
* Click Enable

## Requirements

| Item          | Value               |
| ------------- | ------------------- |
| GLPI          | 10.x to 11.x target |
| PHP           | 8.1+ recommended    |
| Plugin folder | tacticalrmmremote   |

## Development

Clone the repository:

`git clone https://github.com/z0gg/glpi-tacticalrmmremote.git`
`cd glpi-tacticalrmmremote`

Build local release archives:

`bash scripts/package-release.sh v0.1.0`

## Roadmap

* Add configurable TacticalRMM base URL in plugin settings
* Improve compatibility across more GLPI versions
* Add English and French translations
* Improve UI placement for read-only profiles
* Publish stable GitHub Releases with `.tar.gz` and `.zip`
* Prepare the project for broader GLPI ecosystem distribution

## Contributing

Contributions are welcome.

Please keep changes focused, test on a non-production GLPI instance, and update the changelog for user-visible changes.

## License

This project is licensed under the GPL-2.0-or-later license.

## Author

Created and maintained by z0gg
