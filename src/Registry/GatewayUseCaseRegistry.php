<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Registry;

use Profesia\ServiceLayer\Adapter\AdapterInterface;
use Profesia\ServiceLayer\Adapter\Config\AbstractAdapterConfig;
use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Registry\Config\RequestConfig;
use Profesia\ServiceLayer\Registry\Exception\BadConfigException;
use Profesia\ServiceLayer\Registry\Exception\BadStateException;
use Profesia\ServiceLayer\Registry\Exception\RequestNotRegisteredException;
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;
use Profesia\ServiceLayer\Transport\Logging\GatewayLoggerInterface;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\Transport\GatewayInterface;

final class GatewayUseCaseRegistry
{
    private GatewayInterface $defaultGateway;
    /** @var GatewayUseCase[] */
    private array $gatewayUseCases = [];

    /**
     * @param GatewayInterface $defaultGateway
     * @param RequestConfig[] $config
     */
    private function __construct(GatewayInterface $defaultGateway, array $config)
    {
        $this->defaultGateway = $defaultGateway;
        foreach ($config as $requestName => $requestConfig) {
            $this->gatewayUseCases[$requestName] = new GatewayUseCase(
                $this->defaultGateway,
                $requestConfig->getRequest(),
                $requestConfig->getMapper(),
                $requestConfig->getConfigOverride()
            );

            if ($requestConfig->hasOverriddenGateway()) {
                $this->gatewayUseCases[$requestName]->throughGatewayOverride(
                    $requestConfig->getGatewayOverride()
                );
            }

            if ($requestConfig->hasOverriddenAdapter()) {
                $this->gatewayUseCases[$requestName]->viaAdapter(
                    $requestConfig->getAdapterOverride()
                );
            }

            if ($requestConfig->hasOverriddenLogger()) {
                $this->gatewayUseCases[$requestName]->useLogger(
                    $requestConfig->getLoggerOverride()
                );
            }
        }
    }

    /**
     * @param mixed[] $config
     *
     * @return static
     * @throws BadConfigException
     */
    public static function createFromArrayConfig(array $config): self
    {
        if (array_key_exists('defaultGateway', $config) === false) {
            throw new BadConfigException('Required key: [defaultGateway] is not set in config');
        }

        /** @var GatewayInterface $defaultGateway */
        $defaultGateway = $config['defaultGateway'];
        if (array_key_exists('requests', $config) === false) {
            throw new BadConfigException('Required key: [requests] is not set in config');
        }

        if ($config['requests'] === []) {
            throw new BadConfigException('Required key: [requests] is empty');
        }

        $groupConfig = [];
        foreach ($config['requests'] as $requestName => $requestConfig) {
            /** @var GatewayRequestInterface|null $request */
            $request = $requestConfig['request'] ?? null;

            /** @var AbstractAdapterConfig|null $configOverride */
            $configOverride = $requestConfig['configOverride'] ?? null;

            /** @var AdapterInterface|null $adapterOverride */
            $adapterOverride = $requestConfig['adapterOverride'] ?? null;

            /** @var GatewayLoggerInterface|null $loggerOverride */
            $loggerOverride = $requestConfig['loggerOverride'] ?? null;

            /** @var GatewayInterface|null $gatewayOverride */
            $gatewayOverride = $requestConfig['gatewayOverride'] ?? null;

            /** @var ResponseDomainMapperInterface|null $mapper */
            $mapper = $requestConfig['mapper'] ?? null;

            $requestConfig = new RequestConfig(
                $request,
                $configOverride,
                $adapterOverride,
                $loggerOverride,
                $gatewayOverride,
                $mapper
            );

            $groupConfig[$requestName] = $requestConfig;
        }

        return new self(
            $defaultGateway,
            $groupConfig
        );
    }

    /**
     * @param string $requestName
     *
     * @return DomainResponseInterface
     * @throws BadStateException
     * @throws RequestNotRegisteredException
     */
    public function processUseCase(string $requestName): DomainResponseInterface
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
