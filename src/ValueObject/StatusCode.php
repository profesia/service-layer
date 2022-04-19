<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\ValueObject;

use InvalidArgumentException;

final class StatusCode
{
    public const HTTP_CODE_OK                                   = 200;
    public const HTTP_CODE_OK_EMPTY                             = 201;
    public const HTTP_CODE_ACCEPTED                             = 202;
    public const HTTP_CODE_NON_AUTHORITATIVE                    = 203;
    public const HTTP_CODE_NO_CONTENT                           = 204;
    public const HTTP_CODE_RESET_CONTENT                        = 205;
    public const HTTP_CODE_PARTIAL_CONTENT                      = 206;
    public const HTTP_CODE_MULTI_STATUS                         = 207;
    public const HTTP_CODE_ALREADY_REPORTED                     = 208;
    public const HTTP_CODE_IM_USED                              = 226;
    public const HTTP_CODE_MULTIPLE_CHOICES                     = 300;
    public const HTTP_CODE_MOVED_PERMANENTLY                    = 301;
    public const HTTP_CODE_FOUND                                = 302;
    public const HTTP_CODE_SEE_OTHER                            = 303;
    public const HTTP_CODE_NOT_MODIFIED                         = 304;
    public const HTTP_CODE_USE_PROXY                            = 305;
    public const HTTP_CODE_UNUSED                               = 306;
    public const HTTP_CODE_TEMPORARY_REDIRECT                   = 307;
    public const HTTP_CODE_PERMANENT_REDIRECT                   = 308;
    public const HTTP_CODE_BAD_REQUEST                          = 400;
    public const HTTP_CODE_UNAUTHORIZED                         = 401;
    public const HTTP_CODE_PAYMENT_REQUIRED                     = 402;
    public const HTTP_CODE_FORBIDDEN                            = 403;
    public const HTTP_CODE_NOT_FOUND                            = 404;
    public const HTTP_CODE_METHOD_NOT_ALLOWED                   = 405;
    public const HTTP_CODE_NOT_ACCEPTABLE                       = 406;
    public const HTTP_CODE_PROXY_AUTHENTICATION_REQUIRED        = 407;
    public const HTTP_CODE_REQUEST_TIMEOUT                      = 408;
    public const HTTP_CODE_CONFLICT                             = 409;
    public const HTTP_CODE_GONE                                 = 410;
    public const HTTP_CODE_LENGTH_REQUIRED                      = 411;
    public const HTTP_CODE_PRECONDITION_FAILED                  = 412;
    public const HTTP_CODE_REQUEST_ENTITY_TOO_LARGE             = 413;
    public const HTTP_CODE_REQUEST_URI_TOO_LONG                 = 414;
    public const HTTP_CODE_UNSUPPORTED_MEDIA_TYPE               = 415;
    public const HTTP_CODE_REQUESTED_RANGE_NOT_SATISFIABLE      = 416;
    public const HTTP_CODE_EXPECTATION_FAILED                   = 417;
    public const HTTP_CODE_I_M_A_TEAPOT                         = 418;
    public const HTTP_CODE_ENHANCE_YOUR_CALM                    = 420;
    public const HTTP_CODE_UNPROCESSABLE_ENTITY                 = 422;
    public const HTTP_CODE_LOCKED                               = 423;
    public const HTTP_CODE_FAILED_DEPENDENCY                    = 424;
    public const HTTP_CODE_RESERVED_FOR_WEBDAV                  = 425;
    public const HTTP_CODE_UPGRADE_REQUIRED                     = 426;
    public const HTTP_CODE_PRECONDITION_REQUIRED                = 428;
    public const HTTP_CODE_TOO_MANY_REQUESTS                    = 429;
    public const HTTP_CODE_REQUEST_HEADER_FIELDS_TOO_LARGE      = 431;
    public const HTTP_CODE_NO_RESPONSE                          = 444;
    public const HTTP_CODE_RETRY_WITH                           = 449;
    public const HTTP_CODE_BLOCKED_BY_WINDOWS_PARENTAL_CONTROLS = 450;
    public const HTTP_CODE_UNAVAILABLE_FOR_LEGAL_REASONS        = 451;
    public const HTTP_CODE_CLIENT_CLOSED_REQUEST                = 499;
    public const HTTP_CODE_INTERNAL_SERVER_ERROR                = 500;
    public const HTTP_CODE_NOT_IMPLEMENTED                      = 501;
    public const HTTP_CODE_BAD_GATEWAY                          = 502;
    public const HTTP_CODE_SERVICE_UNAVAILABLE                  = 503;
    public const HTTP_CODE_GATEWAY_TIMEOUT                      = 504;
    public const HTTP_CODE_HTTP_VERSION_NOT_SUPPORTED           = 505;
    public const HTTP_CODE_VARIANT_ALSO_NEGOTIATES              = 506;
    public const HTTP_CODE_INSUFFICIENT_STORAGE                 = 507;
    public const HTTP_CODE_LOOP_DETECTED                        = 508;
    public const HTTP_CODE_BANDWIDTH_LIMIT_EXCEEDED             = 509;
    public const HTTP_CODE_NOT_EXTENDED                         = 510;
    public const HTTP_CODE_NETWORK_AUTHENTICATION_REQUIRED      = 511;
    public const HTTP_CODE_NETWORK_READ_TIMEOUT_ERROR           = 598;
    public const HTTP_CODE_NETWORK_CONNECT_TIMEOUT_ERROR        = 599;

    private int $code;

    private function __construct(int $code)
    {
        $this->code = $code;
    }

    public static function createFromInteger(int $code): StatusCode
    {
        if (($code < 100) || ($code >= 600)) {
            throw new InvalidArgumentException('Non supported code passed in');
        }

        return new self($code);
    }

    public function isSuccess(): bool
    {
        return ($this->code >= 200) && ($this->code < 300);
    }

    public function equalsWithInt(int $statusCode): bool
    {
        return ($this->code === $statusCode);
    }

    public function equals(StatusCode $statusCode): bool
    {
        return ($this->code === $statusCode->code);
    }

    public function toString(): string
    {
        return (string)$this;
    }

    public function __toString(): string
    {
        return (string)$this->code;
    }

    /**
     * @return array<int, int>
     */
    public static function getListOfStatusCodes(): array
    {
        return array_keys(
            self::getMapOfStatusCodes()
        );
    }

    /**
     * @return array<int, int>
     */
    public static function getListOfSuccessStatusCodes(): array
    {
        return array_keys(
            self::getMapOfSuccessStatusCodes()
        );
    }

    /**
     * @return array<int, bool>
     */
    private static function getMapOfStatusCodes(): array
    {
        return [
            self::HTTP_CODE_OK                                   => true,
            self::HTTP_CODE_OK_EMPTY                             => true,
            self::HTTP_CODE_ACCEPTED                             => true,
            self::HTTP_CODE_NON_AUTHORITATIVE                    => true,
            self::HTTP_CODE_NO_CONTENT                           => true,
            self::HTTP_CODE_RESET_CONTENT                        => true,
            self::HTTP_CODE_PARTIAL_CONTENT                      => true,
            self::HTTP_CODE_MULTI_STATUS                         => true,
            self::HTTP_CODE_ALREADY_REPORTED                     => true,
            self::HTTP_CODE_IM_USED                              => true,
            self::HTTP_CODE_MULTIPLE_CHOICES                     => true,
            self::HTTP_CODE_MOVED_PERMANENTLY                    => true,
            self::HTTP_CODE_FOUND                                => true,
            self::HTTP_CODE_SEE_OTHER                            => true,
            self::HTTP_CODE_NOT_MODIFIED                         => true,
            self::HTTP_CODE_USE_PROXY                            => true,
            self::HTTP_CODE_UNUSED                               => true,
            self::HTTP_CODE_TEMPORARY_REDIRECT                   => true,
            self::HTTP_CODE_PERMANENT_REDIRECT                   => true,
            self::HTTP_CODE_BAD_REQUEST                          => true,
            self::HTTP_CODE_UNAUTHORIZED                         => true,
            self::HTTP_CODE_PAYMENT_REQUIRED                     => true,
            self::HTTP_CODE_FORBIDDEN                            => true,
            self::HTTP_CODE_NOT_FOUND                            => true,
            self::HTTP_CODE_METHOD_NOT_ALLOWED                   => true,
            self::HTTP_CODE_NOT_ACCEPTABLE                       => true,
            self::HTTP_CODE_PROXY_AUTHENTICATION_REQUIRED        => true,
            self::HTTP_CODE_REQUEST_TIMEOUT                      => true,
            self::HTTP_CODE_CONFLICT                             => true,
            self::HTTP_CODE_GONE                                 => true,
            self::HTTP_CODE_LENGTH_REQUIRED                      => true,
            self::HTTP_CODE_PRECONDITION_FAILED                  => true,
            self::HTTP_CODE_REQUEST_ENTITY_TOO_LARGE             => true,
            self::HTTP_CODE_REQUEST_URI_TOO_LONG                 => true,
            self::HTTP_CODE_UNSUPPORTED_MEDIA_TYPE               => true,
            self::HTTP_CODE_REQUESTED_RANGE_NOT_SATISFIABLE      => true,
            self::HTTP_CODE_EXPECTATION_FAILED                   => true,
            self::HTTP_CODE_I_M_A_TEAPOT                         => true,
            self::HTTP_CODE_ENHANCE_YOUR_CALM                    => true,
            self::HTTP_CODE_UNPROCESSABLE_ENTITY                 => true,
            self::HTTP_CODE_LOCKED                               => true,
            self::HTTP_CODE_FAILED_DEPENDENCY                    => true,
            self::HTTP_CODE_RESERVED_FOR_WEBDAV                  => true,
            self::HTTP_CODE_UPGRADE_REQUIRED                     => true,
            self::HTTP_CODE_PRECONDITION_REQUIRED                => true,
            self::HTTP_CODE_TOO_MANY_REQUESTS                    => true,
            self::HTTP_CODE_REQUEST_HEADER_FIELDS_TOO_LARGE      => true,
            self::HTTP_CODE_NO_RESPONSE                          => true,
            self::HTTP_CODE_RETRY_WITH                           => true,
            self::HTTP_CODE_BLOCKED_BY_WINDOWS_PARENTAL_CONTROLS => true,
            self::HTTP_CODE_UNAVAILABLE_FOR_LEGAL_REASONS        => true,
            self::HTTP_CODE_CLIENT_CLOSED_REQUEST                => true,
            self::HTTP_CODE_INTERNAL_SERVER_ERROR                => true,
            self::HTTP_CODE_NOT_IMPLEMENTED                      => true,
            self::HTTP_CODE_BAD_GATEWAY                          => true,
            self::HTTP_CODE_SERVICE_UNAVAILABLE                  => true,
            self::HTTP_CODE_GATEWAY_TIMEOUT                      => true,
            self::HTTP_CODE_HTTP_VERSION_NOT_SUPPORTED           => true,
            self::HTTP_CODE_VARIANT_ALSO_NEGOTIATES              => true,
            self::HTTP_CODE_INSUFFICIENT_STORAGE                 => true,
            self::HTTP_CODE_LOOP_DETECTED                        => true,
            self::HTTP_CODE_BANDWIDTH_LIMIT_EXCEEDED             => true,
            self::HTTP_CODE_NOT_EXTENDED                         => true,
            self::HTTP_CODE_NETWORK_AUTHENTICATION_REQUIRED      => true,
            self::HTTP_CODE_NETWORK_READ_TIMEOUT_ERROR           => true,
            self::HTTP_CODE_NETWORK_CONNECT_TIMEOUT_ERROR        => true,
        ];
    }

    /**
     * @return array<int, bool>
     */
    private static function getMapOfSuccessStatusCodes(): array
    {
        return [
            self::HTTP_CODE_OK                => true,
            self::HTTP_CODE_OK_EMPTY          => true,
            self::HTTP_CODE_ACCEPTED          => true,
            self::HTTP_CODE_NON_AUTHORITATIVE => true,
            self::HTTP_CODE_NO_CONTENT        => true,
            self::HTTP_CODE_RESET_CONTENT     => true,
            self::HTTP_CODE_PARTIAL_CONTENT   => true,
            self::HTTP_CODE_MULTI_STATUS      => true,
            self::HTTP_CODE_ALREADY_REPORTED  => true,
            self::HTTP_CODE_IM_USED           => true,
        ];
    }
}
