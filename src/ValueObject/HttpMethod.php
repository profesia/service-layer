<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\ValueObject;

use InvalidArgumentException;

final class HttpMethod
{
    public const HTTP_METHOD_GET     = 'GET';
    public const HTTP_METHOD_POST    = 'POST';
    public const HTTP_METHOD_PUT     = 'PUT';
    public const HTTP_METHOD_DELETE  = 'DELETE';
    public const HTTP_METHOD_HEAD    = 'HEAD';
    public const HTTP_METHOD_OPTIONS = 'OPTIONS';
    public const HTTP_METHOD_PATCH   = 'PATCH';

    private string $method;

    private function __construct(string $method)
    {
        $this->method = $method;
    }

    public static function createFromString(string $method): HttpMethod
    {
        if (!array_key_exists($method, self::getListOfHttpMethods())) {
            throw new InvalidArgumentException("Supplied HTTP method: [{$method}] is not supported");
        }

        return new self($method);
    }

    public static function createGet(): HttpMethod
    {
        return self::createFromString(self::HTTP_METHOD_GET);
    }

    public static function createPost(): HttpMethod
    {
        return self::createFromString(self::HTTP_METHOD_POST);
    }

    public static function createPut(): HttpMethod
    {
        return self::createFromString(self::HTTP_METHOD_PUT);
    }

    public static function createDelete(): HttpMethod
    {
        return self::createFromString(self::HTTP_METHOD_DELETE);
    }

    public function toString(): string
    {
        return (string)$this;
    }

    public function __toString(): string
    {
        return $this->method;
    }

    /**
     * @return array<string, bool>
     */
    private static function getListOfHttpMethods(): array
    {
        return [
            self::HTTP_METHOD_GET     => true,
            self::HTTP_METHOD_POST    => true,
            self::HTTP_METHOD_PUT     => true,
            self::HTTP_METHOD_DELETE  => true,
            self::HTTP_METHOD_HEAD    => true,
            self::HTTP_METHOD_OPTIONS => true,
            self::HTTP_METHOD_PATCH   => true,
        ];
    }
}
