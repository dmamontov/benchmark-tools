[![Latest Stable Version](https://poser.pugx.org/dmamontov/benchmark-tools/v/stable.svg)](https://packagist.org/packages/dmamontov/benchmark-tools)
[![License](https://poser.pugx.org/dmamontov/benchmark-tools/license.svg)](https://packagist.org/packages/dmamontov/benchmark-tools)
[![Total Downloads](https://poser.pugx.org/dmamontov/benchmark-tools/downloads.svg)](https://packagist.org/packages/dmamontov/benchmark-tools)
# Benchmark Tools

Server benchmark for all that is possible.

[Demonstration](http://slobel.ru/benchmark)

Example of use can be found in the file [example.php](https://github.com/dmamontov/benchmark-tools/blob/master/example.php).

## Requirements

* PHP version 5.3 or higher.

## Installation

1) Install [composer](https://getcomposer.org/download/)

2) Follow in the project folder:
```bash
composer require dmamontov/benchmark-tools ~1.0.0
```

In config `composer.json` your project will be added to the library` retailcrm / api-client-php`, who settled in the folder `vendor /`. In the absence of a config file or folder with vendors they will be created.

If before your project is not used `composer`, connect the startup file vendors. To do this, enter the code in the project:
```php
require 'path/to/vendor/autoload.php';
```
