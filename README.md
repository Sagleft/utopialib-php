UtopiaLib
==========

PHP library for Utopia Network API.

Installation with Composer
--------------------------

```shell
composer require sagleft/utopialib-php
```

Usage
-----

```php
$token = "3D4F4A5E34706378B4A4541502E603B6";
$client = new UtopiaLib\Client($token, "http://127.0.0.1", 22824);
print_r($client->getSystemInfo());
```

result:

```
Array
(
    [result] => Array
        (
            [buildAbi] => x86_64-little_endian-llp64
            [buildCpuArchitecture] => x86_64
            [build_number] => 0.3.5115
            [currentCpuArchitecture] => x86_64
            [netCoreRate] => 25
            [networkCores] => 1
            [networkEnabled] => 1
            [numberOfConnections] => 9
            [packetCacheSize] => 7320
            [uptime] => 02:30:09
        )

    [resultExtraInfo] => Array
        (
            [elapsed] => 0
        )

)
```

License
-------

UtopiaLib is licensed under [The MIT License](LICENSE).
