<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Registry;

use Profesia\ServiceLayer\Adapter\AdapterInterface;
use Profesia\ServiceLayer\Adapter\Config\AbstractAdapterConfigBuilder;
use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Registry\Exception\BadConfigException;
use Profesia\ServiceLayer\Registry\Exception\BadStateException;
use Profesia\ServiceLayer\Registry\Exception\RequestNotRegisteredException;
use Profesia\ServiceLayer\Response\Domain\GatewayDomainResponseInterface;
use Profesia\ServiceLayer\Transport\Logging\RequestGatewayLoggerInterface;
use Profesia\ServiceLayer\Transport\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\Transport\GatewayInterface;

final class GatewayUseCaseRegistry
{
    private GatewayInterface $defaultGateway;
    /** @var GatewayUseCase[] */
    private array $gatewayUseCases = [];

    private function __construct(GatewayInterface $defaultGateway, array $config)
    {
        $this->defaultGateway = $defaultGateway;
        foreach ($config as $requestName => $requestConfig) {
            $this->gatewayUseCases[$requestName] = new GatewayUseCase(
                $this->defaultGateway,
                $requestConfig['request'] ?? null,
                $requestConfig['mapper'] ?? null,
                $requestConfig['configOverride'] ?? null
            );

            if (array_key_exists('gatewayOverride', $requestConfig)) {
                $this->gatewayUseCases[$requestName]->throughGatewayOverride(
                    $requestConfig['gatewayOverride']
                );
            }

            if (array_key_exists('adapterOverride', $requestConfig)) {
                $this->gatewayUseCases[$requestName]->viaAdapter(
                    $requestConfig['adapterOverride']
                );
            }

            if (array_key_exists('loggerOverride', $requestConfig)) {
                $this->gatewayUseCases[$requestName]->useLogger(
                    $requestConfig['loggerOverride']
                );
            }
        }
    }

    /**
     * @param array $config
     *
     * @return static
     * @throws BadConfigException
     */
    public static function createFromArrayConfig(array $config): self
    {
        if (array_key_exists('defaultGateway', $config) === false) {
            throw new BadConfigException('Required key: [defaultGateway] is not set in config');
        }

        $defaultGateway = $config['defaultGateway'];
        if (array_key_exists('requests', $config) === false) {
            throw new BadConfigException('Required key: [requests] is not set in config');
        }

        if ($config['requests'] === []) {
            throw new BadConfigException('Required key: [requests] is empty');
        }

        $groupConfig = [];
        foreach ($config['requests'] as $requestName => $requestConfig) {
            /** @var GatewayRequestInterface $request */
            $request = $requestConfig['request'] ?? null;

            /** @var AbstractAdapterConfigBuilder|null $configOverride */
            $configOverride = $requestConfig['configOverride'] ?? null;

            /** @var AdapterInterface|null $adapterOverride */
            $adapterOverride = $requestConfig['adapterOverride'] ?? null;

            /** @var RequestGatewayLoggerInterface|null $loggerOverride */
            $loggerOverride = $requestConfig['loggerOverride'] ?? null;

            /** @var GatewayInterface|null $gatewayOverride */
            $gatewayOverride = $requestConfig['gatewayOverride'] ?? null;

            /** @var ResponseDomainMapperInterface|null $mapper */
            $mapper = $requestConfig['mapper'] ?? null;

            $groupConfig[$requestName] = [
                'request'         => $request,
                'configOverride'  => $configOverride,
                'mapper'          => $mapper,
                'adapterOverride' => $adapterOverride,
                'loggerOverride'  => $loggerOverride,
                'gatewayOverride' => $gatewayOverride,
            ];
        }

        return new self(
            $defaultGateway,
            $groupConfig
        );
    }

    /**
     * @param string $requestName
     *
     * @return GatewayDomainResponseInterface
     * @throws BadStateException
     * @throws RequestNotRegisteredException
     */
    public function processUseCase(string $requestName): GatewayDomainResponseInterface
    {
        return
            $this->getConfiguredGatewayUseCase(
                $requestName
            )->performRequest();
    }

    /**
     * @param string $requestName
     *
     * @return GatewayUseCase
     * @throws RequestNotRegisteredException
     */
    public function getConfiguredGatewayUseCase(string $requestName): GatewayUseCase
    {
        if (array_key_exists($requestName, $this->gatewayUseCases) === false) {
            throw new RequestNotRegisteredException("Request with name: [{$requestName}] is not registered");
        }

        return $this->gatewayUseCases[$requestName];
    }
}
