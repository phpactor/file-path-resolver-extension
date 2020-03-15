<?php

namespace Phpactor\FilePathResolverExtension\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\FilePathResolverExtension\FilePathResolverExtension;
use Phpactor\FilePathResolver\PathResolver;
use RuntimeException;

class FilePathResolverExtensionTest extends TestCase
{
    public function testPathResolver()
    {
        $resolver = $this->createResolver([
        ]);

        $this->assertContains('cache/phpactor', $resolver->resolve('%cache%'));
        $this->assertContains('config/phpactor', $resolver->resolve('%config%'));
        $this->assertContains('/phpactor', $resolver->resolve('%data%'));
        $this->assertContains(getcwd(), $resolver->resolve('%project_root%'));
    }

    public function testPathResolverWithApplicationRoot()
    {
        $resolver = $this->createResolver([
            FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__,
        ]);

        $this->assertEquals(__DIR__, $resolver->resolve('%application_root%'));
    }

    public function testProjectId()
    {
        $resolver = $this->createResolver([
            FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__,
            FilePathResolverExtension::PARAM_PROJECT_ROOT => '/foobar/barfoo',
        ]);

        $this->assertEquals('barfoo-2c52a9', $resolver->resolve('%project_id%'));
    }

    public function testPathResolverLogging()
    {
        $resolver = $this->createResolver([
            FilePathResolverExtension::PARAM_ENABLE_LOGGING => true,
            FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__,
        ]);

        $this->assertEquals(__DIR__, $resolver->resolve('%application_root%'));
    }

    /**
     * @dataProvider provideProjectIdCalculate
     */
    public function testProjectIdCalculate(string $input, ?string $expectedId = null, ?string $expectedException = null)
    {
        if ($expectedException) {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage($expectedException);
        }
        self::assertEquals($expectedId, FilePathResolverExtension::calculateProjectId($input));
    }

    public function provideProjectIdCalculate()
    {
        yield [
            false,
            null,
            'Project root must be a non-empty string'
        ];

        yield [
            '/foobar',
            'foobar-1b9590',
        ];

        yield [
            'file:///foobar',
            'foobar-1b9590',
        ];
    }


    public function createResolver(array $config): PathResolver
    {
        $container = PhpactorContainer::fromExtensions([
            FilePathResolverExtension::class,
            LoggingExtension::class
        ], $config);

        return $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER);
    }
}
