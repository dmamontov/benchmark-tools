[![Latest Stable Version](https://poser.pugx.org/dmamontov/benchmark-tools/v/stable.svg)](https://packagist.org/packages/dmamontov/benchmark-tools)
[![License](https://poser.pugx.org/dmamontov/benchmark-tools/license.svg)](https://packagist.org/packages/dmamontov/benchmark-tools)
[![Total Downloads](https://poser.pugx.org/dmamontov/benchmark-tools/downloads)](https://packagist.org/packages/dmamontov/benchmark-tools)
# Benchmark Tools

This package can show a report of PHP configuration information.

It provides several classes that retrieve information of configuration of resources that can be used by PHP on the server side.

The class can compose a report and display it on a Web page

Currently the classes provide information about:
* `Database` support like MySQL configuration variables and speed of insertion and selection of records
* `File system` support like disk space and access permissions, speed of creating files
* Resources needed for `high load` applications like available memory, accessing shared memory, sending large email messages, upload large files
* `HTTP` connection related resources like get the server IP address, HTTP comnnection protocol, HTTP authentication, session support
* Server `platform` information like installed applications such as Wordpress, Drupal, etc., other programming languages
* `Servers ISP` information like network, country, city, geographic coordinates
* `PHP server` configuration like PHP version, cache extensions, available extensions

[Demonstration](http://slobel.ru/benchmark)

Example of use can be found in the file [example.php](https://github.com/dmamontov/benchmark-tools/blob/master/example.php).

## Requirements

* PHP version 5.3 or higher.

## Installation

1) Install [composer](https://getcomposer.org/download/)

2) Follow in the project folder:
```bash
composer require dmamontov/benchmark-tools ~1.0.3
```

In config `composer.json` your project will be added to the library `dmamontov/benchmark-tools`, who settled in the folder `vendor/`. In the absence of a config file or folder with vendors they will be created.

If before your project is not used `composer`, connect the startup file vendors. To do this, enter the code in the project:
```php
require 'path/to/vendor/autoload.php';
```
