<?php

/**
 * @see       https://github.com/clarkphp/fio-php-sdk for the canonical source repository
 * @copyright https://github.com/clarkphp/fio-php-sdk/blob/main/LICENSE.md
 * @license   https://github.com/clarkphp/fio-php-sdk/blob/main/LICENSE.md
 */

declare(strict_types=1);

namespace ClarkPhpTest\FioPhpSdk;

use ClarkPhp\FioPhpSdk\FioClient;
use GuzzleHttp\Client as GuzzleClient;
use PHPUnit\Framework\TestCase;

class FioClientTest extends TestCase
{
    private const BASE_URI = 'http://testnet.fioprotocol.io/v1/chain/';
    private const HEADER_JSON_CONTENT_TYPE = ['content-type' => 'application/json'];

    /**
     * @var FioClient
     */
    private $client;

    public function setUp(): void
    {
        $this->client = new FioClient(
            new GuzzleClient(
                [
                    'base_uri' => self::BASE_URI,
                    'headers'  => self::HEADER_JSON_CONTENT_TYPE
                ]
            )
        );
    }

    public function tearDown(): void
    {
        unset($this->client);
    }

    /**
     * Please note that Network test group is by default not invoked, because accessing
     * remote resources is normally a test smell. See phpunit.xml.dist groups section.
     * Change 'Network' to 'default' or something else to run Network tests.
     *
     * @group Network
     * @group Validation
     */
    public function testIsFioNameAvailable()
    {
        $this->assertTrue(
            $this->client->isFioNameAvailable('idonotexist@idonotexist')
        );

        $this->assertFalse(
            $this->client->isFioNameAvailable('fiodemo@edgetest')
        );

        $this->expectExceptionMessage(
            'has invalid format. See https://developers.fioprotocol.io/api/api-spec/models/fio-address'
        );
        $this->client->isFioNameAvailable('');
    }

    /**
     * @group Validation
     */
    public function testIsFioNameValid()
    {
        $this->assertFalse($this->client->isFioNameValid('qa'));
        $this->assertTrue($this->client->isFioNameValid('a@b'));

        $this->assertFalse($this->client->isFioNameValid('no-trailing-dash-@domain'));
        $this->assertFalse($this->client->isFioNameValid('name@no-trailing-dash-'));

        $this->assertFalse($this->client->isFioNameValid('-no-leading-dash@domain'));
        $this->assertFalse($this->client->isFioNameValid('name@-no-leading-dash'));

        $this->assertFalse($this->client->isFioNameValid('no_underscore@domain'));
        $this->assertFalse($this->client->isFioNameValid('name@no_underscore'));

        $this->assertTrue($this->client->isFioNameValid('64-chars-is-not-too-long-to-make-for-valid-fio-address@fiodomain'));
        $this->assertFalse($this->client->isFioNameValid('65-chars-is-too-long-to-be-a-valid-fio-address@listen-to-this-bud'));

        $this->assertTrue($this->client->isFioNameValid('1-numbers-are-okay-1@domain1'));
        $this->assertTrue($this->client->isFioNameValid('vAlId-foRmaT1@FIO-address1'));
    }
}