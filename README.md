# Laravel Nova Text Editor with text templates, text blocks and dynamic variable

[![Latest Version on Packagist](https://img.shields.io/packagist/v/orlyapps/nova-texteditor.svg?style=flat-square)](https://packagist.org/packages/orlyapps/nova-texteditor)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/orlyapps/nova-texteditor/run-tests?label=tests)](https://github.com/orlyapps/nova-texteditor/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/orlyapps/nova-texteditor/Check%20&%20fix%20styling?label=code%20style)](https://github.com/orlyapps/nova-texteditor/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/orlyapps/nova-texteditor.svg?style=flat-square)](https://packagist.org/packages/orlyapps/nova-texteditor)


## Installation

You can install the package via composer:

```bash
composer require orlyapps/nova-texteditor
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Orlyapps\NovaTexteditor\NovaTexteditorServiceProvider" --tag="nova-texteditor-migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Orlyapps\NovaTexteditor\NovaTexteditorServiceProvider" --tag="nova-texteditor-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$nova-texteditor = new Orlyapps\NovaTexteditor();
echo $nova-texteditor->echoPhrase('Hello, Orlyapps!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Florian Strauß](https://github.com/orlyapps)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
