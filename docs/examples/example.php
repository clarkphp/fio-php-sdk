<?php

/**
 * @see       https://github.com/clarkphp/fio-php-sdk for the canonical source repository
 * @copyright https://github.com/clarkphp/fio-php-sdk/blob/main/LICENSE.md
 * @license   https://github.com/clarkphp/fio-php-sdk/blob/main/LICENSE.md
 */

declare(strict_types=1);

namespace YourProjectNamespace;

use ClarkPhp\FioPhpSdk\FioClient;
use GuzzleHttp\Client as GuzzleClient;
// @todo Use factories to allow use of any PSR-18-compliant client,
// removing dependency on particular implementations


require __DIR__ . '/../../vendor/autoload.php';

$candidateFioNames = [
    'fiodemo@edgetest',
    'idonotexist@idonotexist',
    'bad-@fio-address',
];

// Example: check if the desired FIO name is available
try {
    $client = new FioClient(
        new GuzzleClient(
            [
                'base_uri' => 'http://testnet.fioprotocol.io/v1/chain/',
                'headers'  => ['content-type' => 'application/json']
            ]
        )
    );

    foreach ($candidateFioNames as $fioName) {
        print "$fioName is "
            . ($client->isFioNameAvailable($fioName) ? '' : 'not ') . 'available' . PHP_EOL;
    }
} catch (\Throwable $e) {
    print $e->getMessage() . PHP_EOL;
}