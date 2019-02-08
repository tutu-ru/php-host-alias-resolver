<?php
declare(strict_types=1);

namespace TutuRu\Tests\HostAliasResolver;

use TutuRu\Config\JsonConfig\MutableJsonConfig;
use TutuRu\HostAliasResolver\HostAliasResolverException;
use TutuRu\HostAliasResolver\HostAliasResolver;
use TutuRu\HostAliasResolver\InvalidUriException;
use PHPUnit\Framework\TestCase;

class HostAliasResolverTest extends TestCase
{
    /** @var MutableJsonConfig */
    private $config;


    public function setUp()
    {
        parent::setUp();
        $this->config = new MutableJsonConfig(__DIR__ . '/config/app.json');
    }


    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(string $expected, string $uri)
    {
        $this->config->setValue('host_alias_resolver.rus.desktop.mainpage', 'main.host.com');
        $this->config->setValue('env.domain', 'internal.host.com');

        $controller = new HostAliasResolver($this->config);
        $this->assertEquals($expected, $controller->resolve($uri));
    }


    public function renderDataProvider()
    {
        return [
            [
                'expected' => 'https://main.host.com',
                'uri'      => 'mainpage.desktop.rus',
            ],
            [
                'expected' => '//main.host.com',
                'uri'      => '//mainpage.desktop.rus',
            ],
            [
                'expected' => 'http://main.host.com',
                'uri'      => 'http://mainpage.desktop.rus',
            ],
            [
                'expected' => 'https://main.host.com/',
                'uri'      => 'mainpage.desktop.rus/',
            ],
            [
                'expected' => 'https://main.host.com/query?arg=1#hash',
                'uri'      => 'mainpage.desktop.rus/query?arg=1#hash',
            ],
            [
                'expected' => 'https://main.host.com/query?arg=1#hash',
                'uri'      => 'https://mainpage.desktop.rus/query?arg=1#hash',
            ],


            [
                'expected' => 'http://legal.internal.host.com',
                'uri'      => 'legal',
            ],
            [
                'expected' => 'https://legal.internal.host.com/',
                'uri'      => 'https://legal/',
            ],
            [
                'expected' => '//legal.internal.host.com',
                'uri'      => '//legal',
            ],
            [
                'expected' => 'http://legal.internal.host.com/api/v1/legal/query?arg=1#hash',
                'uri'      => 'legal/api/v1/legal/query?arg=1#hash',
            ],
            [
                'expected' => 'https://legal.internal.host.com/api/v1/query?arg=1#hash',
                'uri'      => 'https://legal/api/v1/query?arg=1#hash',
            ],
            [
                'expected' => '//legal.internal.host.com/api/v1/query?arg=1#hash',
                'uri'      => '//legal/api/v1/query?arg=1#hash',
            ],
        ];
    }


    /**
     * @dataProvider renderExceptionDataProvider
     */
    public function testRenderException(string $uri, string $expectedException)
    {
        $this->config->setValue('host_alias_resolver.rus.desktop.mainpage', 'main.host.com');

        $this->expectException($expectedException);

        $resolver = new HostAliasResolver($this->config);
        $resolver->resolve($uri);
    }


    public function renderExceptionDataProvider()
    {
        return [
            ['uri' => '', 'expectedException' => InvalidUriException::class],
            ['uri' => 'undefined.uri.test', 'expectedException' => HostAliasResolverException::class],
        ];
    }


    /**
     * @dataProvider getHostByAliasDataProvider
     */
    public function testGetHostByAlias(string $alias, string $expectedHost)
    {
        $this->config->setValue('host_alias_resolver.rus.desktop.mainpage', 'main.host.com');
        $this->config->setValue('env.domain', 'internal.host.com');

        $resolver = new HostAliasResolver($this->config);
        $this->assertEquals($expectedHost, $resolver->getHostByAlias($alias));
    }


    public function getHostByAliasDataProvider()
    {
        return [
            [
                'alias'        => 'test',
                'expectedHost' => 'test.internal.host.com',
            ],
            [
                'alias'        => 'mainpage.desktop.rus',
                'expectedHost' => 'main.host.com',
            ],
            [
                'alias'        => 'unknown',
                'expectedHost' => 'unknown.internal.host.com',
            ],
        ];
    }


    public function testGetHostByAliasException()
    {
        $this->config->setValue('host_alias_resolver.rus.desktop.mainpage', 'main.host.com');

        $this->expectException(HostAliasResolverException::class);
        $resolver = new HostAliasResolver($this->config);
        $resolver->getHostByAlias('test');
    }
}
