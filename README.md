# MyAdmin Kayako Chat Plugin

[![Build Status](https://github.com/detain/myadmin-kayako-chat/actions/workflows/tests.yml/badge.svg)](https://github.com/detain/myadmin-kayako-chat/actions/workflows/tests.yml)
[![Latest Stable Version](https://poser.pugx.org/detain/myadmin-kayako-chat/version)](https://packagist.org/packages/detain/myadmin-kayako-chat)
[![Total Downloads](https://poser.pugx.org/detain/myadmin-kayako-chat/downloads)](https://packagist.org/packages/detain/myadmin-kayako-chat)
[![License](https://poser.pugx.org/detain/myadmin-kayako-chat/license)](https://packagist.org/packages/detain/myadmin-kayako-chat)

A MyAdmin plugin that integrates Kayako Live Chat functionality into the hosting management platform. This package provides event-driven hooks for managing chat-related settings, menu entries, and dependency requirements through the Symfony EventDispatcher component.

## Requirements

- PHP >= 5.0
- ext-soap
- symfony/event-dispatcher ^5.0

## Installation

Install via Composer:

```sh
composer require detain/myadmin-kayako-chat
```

## Usage

The plugin registers itself through the MyAdmin plugin system and provides event handlers for:

- **Menu integration** - Adds Kayako-related entries to the admin menu
- **Dependency loading** - Registers required classes and includes
- **Settings management** - Integrates with the MyAdmin settings subsystem

## Running Tests

```sh
composer install
vendor/bin/phpunit
```

## License

Licensed under the LGPL-2.1. See [LICENSE](LICENSE) for details.
