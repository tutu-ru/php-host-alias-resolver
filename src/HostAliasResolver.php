<?php
declare(strict_types=1);

namespace TutuRu\HostAliasResolver;

use TutuRu\Config\ConfigInterface;

class HostAliasResolver
{
    private const HOST_DELIMITER = '.';

    private const REGISTRY_DEFAULT_SCHEME = 'https';
    private const DYNAMIC_DEFAULT_SCHEME = 'http';

    /** @var ConfigInterface */
    private $config;


    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }


    /**
     * @param  string $uri PRODUCT.APPLICATION_TYPE.LOCALIZATION/query?arg=test#hash
     * @return string
     * @throws HostAliasResolverException
     */
    public function resolve(string $uri): string
    {
        $result = $this->render(
            $uri,
            function (string $host): ?string {
                return $this->getDomainForAlias($host);
            },
            self::REGISTRY_DEFAULT_SCHEME
        );
        if (is_null($result)) {
            $result = $this->render(
                $uri,
                function (string $host): ?string {
                    return $this->getWithEnvDomain($host);
                },
                self::DYNAMIC_DEFAULT_SCHEME
            );
        }

        if (is_null($result)) {
            throw new HostAliasResolverException(
                "URI '{$uri}' not found in registry and empty environment configuration"
            );
        }

        return $result;
    }


    public function getHostByAlias(string $alias): string
    {
        $hostAlias = $this->reverseHostParts($alias);
        $domain = $this->getDomainForAlias($hostAlias);
        if (is_null($domain)) {
            $domain = $this->getWithEnvDomain($alias);
        }
        if (is_null($domain)) {
            throw new HostAliasResolverException("No alias for domain and env.domain not specified");
        }
        return $domain;
    }


    private function getDomainForAlias(string $key): ?string
    {
        $domain = $this->config->getValue('host_alias_resolver.' . $key);
        return is_null($domain) ? null : (string)$domain;
    }


    public function getWithEnvDomain(string $alias): ?string
    {
        $envDomain = $this->config->getValue('env.domain');
        return is_null($envDomain) ? null : sprintf("%s.%s", $alias, $envDomain);
    }


    private function render(string $uri, callable $get, string $defaultScheme): ?string
    {
        $uri = $this->getUriWithScheme($uri, $defaultScheme);
        $hostToReplace = $this->parseUriHost($uri);
        $renderedHostWithScheme = $get($this->reverseHostParts($hostToReplace));
        if (is_null($renderedHostWithScheme)) {
            return null;
        }
        $renderedHost = $this->parseUriHost($renderedHostWithScheme);
        if (($startPosition = strpos($uri, $hostToReplace)) !== false) {
            return substr_replace($uri, $renderedHost, $startPosition, strlen($hostToReplace));
        }
        return $uri;
    }


    private function reverseHostParts(string $host): string
    {
        $hostParts = explode(self::HOST_DELIMITER, $host);
        return implode(self::HOST_DELIMITER, array_reverse($hostParts));
    }


    /**
     * @param $uriToParse
     * @return mixed
     * @throws InvalidUriException
     */
    private function parseUriHost($uriToParse): string
    {
        $result = parse_url(
            $this->getUriWithScheme($uriToParse, self::REGISTRY_DEFAULT_SCHEME),
            PHP_URL_HOST
        );

        if (false === $result) {
            throw new InvalidUriException("Cannot parse host '{$uriToParse}'");
        }

        return $result;
    }


    /**
     * @param  string $uri
     * @param  string $defaultScheme
     * @return string
     */
    private function getUriWithScheme(string $uri, string $defaultScheme = null): string
    {
        $hasScheme = $this->uriHasScheme($uri);

        if (!$hasScheme) {
            $uri = $this->addScheme($defaultScheme, $uri);
        }

        return $uri;
    }


    private function addScheme(string $scheme, string $uri): string
    {
        return $scheme . '://' . $uri;
    }


    /**
     * @param  string $uri
     * @return bool
     */
    private function uriHasScheme(string $uri): bool
    {
        return false !== strpos($uri, '//');
    }
}
