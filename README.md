# Real-Time monitoring package using Palzin Monitor

[![Latest Stable Version](http://poser.pugx.org/palzin-apm/palzin-symfony/v?style=for-the-badge)](https://packagist.org/packages/palzin-apm/palzin-symfony) [![Total Downloads](http://poser.pugx.org/palzin-apm/palzin-symfony/downloads?style=for-the-badge)](https://packagist.org/packages/palzin-apm/palzin-symfony) [![License](http://poser.pugx.org/palzin-apm/palzin-laravel/license?style=for-the-badge)](https://packagist.org/packages/palzin-apm/palzin-symfony)

Palzin Monitor offers real-time performance monitoring capabilities that allow you to effectively monitor and analyze the performance of your applications. With Palzin Monitor, you can capture and track all requests without the need for any code modifications. This feature enables you to gain valuable insights into the impact of your methods, database statements, and external requests on the overall user experience.


- [Requirements](#requirements)
- [Install](#install)
- [Configure the INGESTION key](#key)
- [Official Documentation](https://palzin.app/guides/symfony-introduction)
- [License](#license)

<a name="requirements"></a>

## Requirements

- PHP >= 7.2
- Symfony ^4.4|^5.2|^6.0

<a name="install"></a>

## Install

Install the latest version of the bundle:

```
composer require palzin-apm/palzin-symfony
```

<a name="key"></a>

### Configure the INGESTION Key

Create the `palzin.yaml` configuration file in your `config/packages` directory, and put the `ingestion_key` field inside:

```yaml
palzin:
    ingestion_key: [your-ingestion-key]
    url: [your palzin base url]
```

You can obtain the `ingestion_key` creating a new project in your [Palzin Monitor (APM)](https://palzin.app) dashboard.

## Official documentation

**[Go to the official documentation](https://palzin.app/guides/symfony-introduction)**


## LICENSE

This bundle is licensed under the [MIT](LICENSE) license.
