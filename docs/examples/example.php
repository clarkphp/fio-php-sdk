<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;

require __DIR__ . '/../../vendor/autoload.php';

$candidateFioNames = [
    'fiodemo@edgetest',
    'idonotexist@idonotexist',
    '',
];

try {
    $client = new Client(
        [
            'base_uri' => 'http://testnet.fioprotocol.io/v1/chain/',
            'headers'  => ['content-type' => 'application/json']
        ]
    );

    foreach ($candidateFioNames as $fioName) {
        $request = (new Request('POST', 'avail_check'))
            ->withBody(Utils::streamFor('{"fio_name":"' . $fioName . '"}'));

        $response = $client->send($request);
        printf("%s\n", $response->getBody());
    }
} catch (GuzzleException $e) {
    printf("%s\n", $e->getMessage());
    exit(1);
}