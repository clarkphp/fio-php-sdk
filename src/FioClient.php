<?php

/**
 * @see       https://github.com/clarkphp/fio-php-sdk for the canonical source repository
 * @copyright https://github.com/clarkphp/fio-php-sdk/blob/main/LICENSE.md
 * @license   https://github.com/clarkphp/fio-php-sdk/blob/main/LICENSE.md
 */

declare(strict_types=1);

namespace ClarkPhp\FioPhpSdk;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Utils;

final class FioClient
{
    // @todo Eventually, use factories to allow use of any PSR-18-compliant client

    /** @todo move these constants to a validator or somesuch class */
    const FIO_ADDR_MIN_LENGTH = 3; // I think the regex's minimum length isn't necessary
    const FIO_ADDR_MAX_LENGTH = 64;
    const FIO_ADDR_VALID_FORMAT = '/^(?:(?=.{3,64}$)[a-z\d]{1}(?:(?!-{2,}))[a-z\d-]*(?:(?<!-))@[a-z\d]{1}(?:(?!-{2,}))[a-z\d-]*(?:(?<!-))$)/i';

    /** @var GuzzleClient */
    private $client;

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
    }

    public function isFioNameAvailable(string $nameToCheck) : bool
    {
        if (! $this->isFioNameValid($nameToCheck)) {
            throw new \Exception(
                "'$nameToCheck' has invalid format. See https://developers.fioprotocol.io/api/api-spec/models/fio-address"
            );
        }

        $response = $this->client->send(
            (new GuzzleRequest('POST', 'avail_check'))
                ->withBody(Utils::streamFor('{"fio_name":"' . $nameToCheck . '"}')));

        return '{"is_registered":0}' === (string) $response->getBody();
    }

    /**
     * Refoctor a Validator class that indicates WHY a name failed format validation.
     *
     * @param string $name
     *
     * @return bool
     */
    public function isFioNameValid(string $name): bool
    {
        $len = strlen($name);
        return $len >= self::FIO_ADDR_MIN_LENGTH
            && $len <= self::FIO_ADDR_MAX_LENGTH
            && preg_match(self::FIO_ADDR_VALID_FORMAT, $name);
    }
}