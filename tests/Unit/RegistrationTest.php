<?php

namespace Sourcetoad\EnhancedResources\Tests\Unit;

use InvalidArgumentException;
use Mockery\Mock;
use Sourcetoad\EnhancedResources\EnhancedResource;
use Sourcetoad\EnhancedResources\Tests\ExampleEnhancement;
use Sourcetoad\EnhancedResources\Tests\TestCase;
use Sourcetoad\EnhancedResources\Tests\User;
use Sourcetoad\EnhancedResources\Tests\UserResource;

class RegistrationTest extends TestCase
{
    public function testItCanRegisterAnEnhancement(): void
    {
        # Act
        EnhancedResource::enhance('example', ExampleEnhancement::class);

        # Assert
        $this->assertTrue(
            EnhancedResource::hasEnhancement('example'),
            'The enhancement was not registered.'
        );
    }

    public function testItCanRegisterCallableEnhancements(): void
    {
        # Act
        EnhancedResource::enhance('example', function() {});

        # Assert
        $this->assertTrue(
            EnhancedResource::hasEnhancement('example'),
            'The enhancement was not registered.'
        );
    }

    public function testItThrowsAnExceptionWhenProvidedAnInvalidEnhancement(): void
    {
        # Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid enhancement.');

        # Act
        EnhancedResource::enhance('example', 2);
    }

    public function testItInheritsEnhancements(): void
    {
        # Act
        EnhancedResource::enhance('example', ExampleEnhancement::class);

        # Assert
        $this->assertTrue(
            UserResource::hasEnhancement('example'),
            'The enhancement was not inherited.'
        );
    }

    public function testItPrioritizesEnhancementsRegisteredOnDescendents(): void
    {
        # Arrange
        EnhancedResource::enhance('example', function() {});

        # Act
        UserResource::enhance('example', ExampleEnhancement::class);

        # Assert
        $this->assertSame(
            ExampleEnhancement::class,
            UserResource::getEnhancement('example'),
            'Enhancements were not prioritized correctly.'
        );
    }

    public function testItCanCallRegisteredEnhancements(): void
    {
        # Expect
        $this->expectNotToPerformAssertions();

        # Arrange
        EnhancedResource::enhance('example', ExampleEnhancement::class);

        # Act
        UserResource::make(new User)->example();
    }

    public function testItCanCallRegisteredCallableEnhancements(): void
    {
        # Expect
        $this->expectNotToPerformAssertions();

        # Arrange
        EnhancedResource::enhance('example', function () {});

        # Act
        UserResource::make(new User)->example();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $reflectionClass = new \ReflectionClass(EnhancedResource::class);
        $reflectionProperty = $reflectionClass->getProperty('enhancements');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue([]);
        $reflectionProperty->setAccessible(false);
    }
}