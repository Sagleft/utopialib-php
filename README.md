
![logo](https://github.com/Sagleft/utopialib-php/raw/master/img/logo.png)

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

You can find out more about all the methods [in the interface of the main class.](src/ClientInterface.php)

For convenience, all methods in the library have the same names as in the Utopia API.

Additional methods:
* checkClientConnection(): bool;
* isUserMyContact($pkOrNick): bool;
* getNetworkSummary(): array;
* isCryptonEngineReady(): bool;
* isNATDetectionON(): bool;
* isUPNPDetectionON(): bool;
* isChannelDatabaseReady(): bool;
* getChannelDecription($channelid): string;
* getChannelOwnerPubkey($channelid): string;
* getChannelTitle($channelid): string;
* getChannelType($channelid): string;
* getNetworkChannelsCount(): int;
* getTotalChannelsCount(): int;
* getLastDownloadedChannelTitle(): string;
* getMyPubkey(): string;
* getMyNick(): string;
* getMyAvatarHash(): string;
* isPOSenabled(): bool;
* findChannelsByPubkey($pubkey): array;
* isNetworkEnabled(): bool;

License
-------

UtopiaLib is licensed under [The MIT License](LICENSE).
