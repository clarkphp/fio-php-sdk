<?php

/**
 * @see       https://github.com/clarkphp/fio-php-sdk for the canonical source repository
 * @copyright https://github.com/clarkphp/fio-php-sdk/blob/main/LICENSE.md
 * @license   https://github.com/clarkphp/fio-php-sdk/blob/main/LICENSE.md
 */

declare(strict_types=1);

namespace ClarkPhp\FioPhpSdk;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class FioClient
{
    // @todo Use factories to allow use of any PSR-18-compliant client, removing dependency on particular implementations

    const FIO_ADDR_VALID_FORMAT = '/^(?:(?=.{3,64}$)[a-z\d]{1}(?:(?!-{2,}))[a-z\d-]*(?:(?<!-))@[a-z\d]{1}(?:(?!-{2,}))[a-z\d-]*(?:(?<!-))$)/i';

    /** @var GuzzleClient */
    private $httpClient;

    /** @var ResponseInterface|null */
    private $errorResponse;

    /** @var string */
    private $errorDetails;

    /** @var \stdClass|null */
    private $responseBody;

    public function __construct(GuzzleClient $client)
    {
        $this->httpClient = $client;
    }

    public function isFioNameRegistered(string $nameToCheck): bool
    {
        return ! $this->isFioNameAvailable($nameToCheck);
    }

    public function isFioNameAvailable(string $nameToCheck): ?bool
    {
        $this->errorResponse = null;

        if (!$this->isFioNameValid($nameToCheck)) {
            throw new Exception(
                "'$nameToCheck' has invalid format. See https://developers.fioprotocol.io/api/api-spec/models/fio-address"
            );
        }

        try {
            return '{"is_registered":0}' === (string)(
                $this->httpClient->send(
                    (new GuzzleRequest('POST', 'avail_check'))
                        ->withBody(Utils::streamFor('{"fio_name":"' . $nameToCheck . '"}'))
                ))->getBody();
        } catch (RequestException $e) {
                $this->errorDetails = $this->extractErrorDetailsFromRequest($e);
        }

        return null;
    }

    public function isFioNameValid(string $name): bool
    {
        return (bool)preg_match(self::FIO_ADDR_VALID_FORMAT, $name);
    }

    //@todo Refactor error handling to a separate class
    // should this return an error object of some sort?
    public function extractErrorDetailsFromRequest(RequestException $e): string
    {
        if ($e->hasResponse()) {
            $this->errorResponse = $e->getResponse();
            $this->responseBody = $this->convertStreamToObject($e->getResponse()->getBody());
            if (0 !== ($errNum = $e->getResponse()->getStatusCode())) {
                $methodName = "extractError{$errNum}Details";
                return $this->$methodName($this->responseBody);
            }
        }
        return '';
    }

    // @todo possibly return a "useful" PHP class instead?
    private function convertStreamToObject(StreamInterface $body): \stdClass
    {
        return json_decode((string) $body);
    }

    public function getResponseErrorNumber(): int
    {
        return $this->errorResponse->getStatusCode();
    }

    public function getResponseErrorMessage(): string
    {
        return $this->errorResponse->getReasonPhrase();
    }

    /**
     * @see https://developers.fioprotocol.io/api/api-spec/models/error-400
     * @return object
     */
    private function extractError400Details(): object
    {
    /*
    { documentation page
      "type": "object",
      "title": "Error 400",
      "required": [
        "type",
        "message",
        "fields"
      ],
      "properties": {
        "type": {
          "type": "string",
          "default": "invalid_input",
          "description": "invalid_input"
        },
        "message": {
          "type": "string",
          "default": "An invalid request was sent in, please check the nested errors for details.",
          "description": "An invalid request was sent in, please check the nested errors for details."
        },
        "fields": {
          "type": "array",
          "items": {
            "type": "object",
            "properties": {
              "name": {
                "type": "string",
                "description": "Name of the field which triggered the error"
              },
              "value": {
                "type": "string",
                "description": "Value which was sent in and which triggered the error"
              },
              "error": {
                "type": "string",
                "description": "Error message"
              }
            }
          }
        }
      }
    }

     */

        return new \stdClass();
    }

    /**
     * @see https://developers.fioprotocol.io/api/api-spec/models/error-403
     * @return object
     */
    private function extractError403Details():object
    {
        /*
    {
      "type": "object",
      "title": "Error 403",
      "properties": {
        "type": {
          "type": "string",
          "description": "Type of 403 error",
          "example": "invalid_signature"
        },
        "message": {
          "type": "string",
          "example": "Request signature not valid or not allowed.",
          "description": "Message associated with the 403 type"
        }
      },
      "required": [
        "type",
        "message"
      ]
    }
        */
        return new \stdClass();
    }

    /**
     * @see https://developers.fioprotocol.io/api/api-spec/models/error-404
     *
     * @param   \stdClass  $apiResponseBody
     * @return string
     */
    private function extractError404Details(\stdClass $apiResponseBody): string
    {
        /* documentation page
        {
          "type": "object",
          "title": "Error 404",
          "properties": {
            "message": {
              "type": "string",
              "description": "Call specific error message.",
              "example": "Public address not found"
            }
          }
        }

        Value from actual run:
        stdClass Object
        (
            [code] => 404
            [message] => Not Found
            [error] => stdClass Object
                (
                    [code] => 0
                    [name] => exception
                    [what] => unspecified
                    [details] => Array
                        (
                            [0] => stdClass Object
                                (
                                    [message] => Unknown Endpoint
                                    [file] => http_plugin.cpp
                                    [line_number] => 353
                                    [method] => handle_http_request
                                )
                        )
                )
        )
        */

        return (($apiResponseBody->error->details)[0])->message ?? '';
    }
}