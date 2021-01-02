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

// for unit tests only, not needed by users of FioClient
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Utils;

class FioClientTest extends TestCase
{
    private const BASE_URI = 'http://testnet.fioprotocol.io/v1/chain/';
    private const HEADER_JSON_CONTENT_TYPE = ['content-type' => 'application/json'];

    /** @var FioClient */
    public $fioClient;

    /** @var GuzzleClient  */
    public $httpClient;

    public function setUp(): void
    {
        $this->httpClient = new GuzzleClient(
            [
                'base_uri' => self::BASE_URI,
                'headers'  => self::HEADER_JSON_CONTENT_TYPE
            ]
        );

        $this->fioClient = new FioClient($this->httpClient);
    }

    public function tearDown(): void
    {
        unset($this->fioClient);
    }

    /**
     * Please note that Network test group is by default not invoked, because accessing
     * remote resources is normally a test smell. See phpunit.xml.dist groups section.
     * Copy to phpunit.xml and change 'Network' to 'default' or something else to run
     * Network tests.
     *
     * @group Network
     * group choose-a-good-test-group-name
     */
    public function testIsFioNameAvailable()
    {
        $this->assertTrue(
            $this->fioClient->isFioNameAvailable('idonotexist@idonotexist')
        );

        $this->assertFalse(
            $this->fioClient->isFioNameAvailable('fiodemo@edgetest')
        );

        $this->expectExceptionMessage(
            'has invalid format. See https://developers.fioprotocol.io/api/api-spec/models/fio-address'
        );
        $this->fioClient->isFioNameAvailable('');
    }

    /** @group Validation */
    public function testIsFioNameValid()
    {
        $this->assertFalse($this->fioClient->isFioNameValid('qa'));
        $this->assertTrue($this->fioClient->isFioNameValid('a@b'));

        $this->assertFalse($this->fioClient->isFioNameValid('no-trailing-dash-@domain'));
        $this->assertFalse($this->fioClient->isFioNameValid('name@no-trailing-dash-'));

        $this->assertFalse($this->fioClient->isFioNameValid('-no-leading-dash@domain'));
        $this->assertFalse($this->fioClient->isFioNameValid('name@-no-leading-dash'));

        $this->assertFalse($this->fioClient->isFioNameValid('no_underscore@domain'));
        $this->assertFalse($this->fioClient->isFioNameValid('name@no_underscore'));

        $this->assertTrue($this->fioClient->isFioNameValid('64-chars-is-not-too-long-to-make-for-valid-fio-address@fiodomain'));
        $this->assertFalse($this->fioClient->isFioNameValid('65-chars-is-too-long-to-be-a-valid-fio-address@listen-to-this-bud'));

        $this->assertTrue($this->fioClient->isFioNameValid('1-numbers-are-okay-1@domain1'));
        $this->assertTrue($this->fioClient->isFioNameValid('vAlId-foRmaT1@FIO-address1'));
    }

    /** @group Network */
    public function testGetErrorCodeFromRequestException()
    {
        $this->callNonExistentEndPoint();
        $this->assertSame(404, $this->fioClient->getResponseErrorNumber());
        $this->assertSame(
//            '{"code":404,"message":"Not Found","error":{"code":0,"name":"exception","what":"unspecified","details":[{"message":"Unknown Endpoint","file":"http_plugin.cpp","line_number":353,"method":"handle_http_request"}]}}',
            'Not Found',
            $this->fioClient->getResponseErrorMessage()
        );
    }

    private function callNonExistentEndPoint()
    {
        try {
            $response = $this->httpClient->send(
                (new GuzzleRequest('POST', 'idonotexist'))
                    ->withBody(Utils::streamFor('{"fio_name":"wilecoyote"}'))
            );

            return (string) $response->getBody();
        } catch(RequestException $e) {
            return $this->fioClient->extractErrorDetailsFromRequest($e);
        }
    }
}