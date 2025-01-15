<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Tests\Unit;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\ComponentFactory;
use Symfony\UX\TwigComponent\ComponentPropertyReflection;
use Symfony\UX\TwigComponent\ComponentReflection;
use Twig\Environment;

class ComponentReflectionTest extends KernelTestCase
{
    public function testPropsAreFoundInTwigComponent(): void
    {
        /** @var ComponentFactory $factory */
        $factory = self::getContainer()->get('ux.twig_component.component_factory');
        $twig = self::getContainer()->get(Environment::class);
        $metadata = $factory->metadataFor('DivComponent5');

        $extractor = new ComponentReflection($twig);
        $attributes = $extractor->getProperties($metadata);

        $this->assertCount(1, $attributes);
        $this->assertInstanceOf(ComponentPropertyReflection::class, $attributes['divComponentName']);
        $property = $attributes['divComponentName'];
        $this->assertEquals('string $divComponentName = "foo"', $property->getCode());
        $this->assertEquals('divComponentName', $property->getName());
        $this->assertEquals('string', $property->getType());
        $this->assertEquals('foo', $property->getDefaultValue());
    }

    public function testPropsAreFoundInTwigComponentWithoutProps(): void
    {
        /** @var ComponentFactory $factory */
        $factory = self::getContainer()->get('ux.twig_component.component_factory');
        $twig = self::getContainer()->get(Environment::class);
        $metadata = $factory->metadataFor('DivComponent6');

        $extractor = new ComponentReflection($twig);
        $attributes = $extractor->getProperties($metadata);

        $this->assertEmpty($attributes);
    }

    public function testPropsAreFoundInTwigAnonymousComponent(): void
    {
        /** @var ComponentFactory $factory */
        $factory = self::getContainer()->get('ux.twig_component.component_factory');
        $twig = self::getContainer()->get(Environment::class);
        $metadata = $factory->metadataFor('Button');

        $extractor = new ComponentReflection($twig);
        $attributes = $extractor->getProperties($metadata);

        $this->assertCount(2, $attributes);
        $this->assertInstanceOf(ComponentPropertyReflection::class, $attributes['label']);
        $this->assertInstanceOf(ComponentPropertyReflection::class, $attributes['primary']);

        $this->assertEquals('mixed $label = ""', $attributes['label']->getCode());
        $this->assertEquals('label', $attributes['label']->getName());
        $this->assertEquals('mixed', $attributes['label']->getType());
        $this->assertNull($attributes['label']->getDefaultValue());

        $this->assertEquals('mixed $primary = true', $attributes['primary']->getCode());
        $this->assertEquals('primary', $attributes['primary']->getName());
        $this->assertEquals('mixed', $attributes['primary']->getType());
        $this->assertEquals('true', $attributes['primary']->getDefaultValue());
    }

    public function testPropsAreFoundInTwigAnonymousComponentWithJusteAttributes(): void
    {
        /** @var ComponentFactory $factory */
        $factory = self::getContainer()->get('ux.twig_component.component_factory');
        $twig = self::getContainer()->get(Environment::class);
        $metadata = $factory->metadataFor('JustAttributes');

        $extractor = new ComponentReflection($twig);
        $attributes = $extractor->getProperties($metadata);

        $this->assertEmpty($attributes);
    }

    public function testPropsAreFoundInTwigAnonymousComponentWithEmptyProps(): void
    {
        /** @var ComponentFactory $factory */
        $factory = self::getContainer()->get('ux.twig_component.component_factory');
        $twig = self::getContainer()->get(Environment::class);
        $metadata = $factory->metadataFor('EmptyProps');

        $extractor = new ComponentReflection($twig);
        $attributes = $extractor->getProperties($metadata);

        $this->assertEmpty($attributes);
    }

    public function testGetPropertyByName(): void
    {
        /** @var ComponentFactory $factory */
        $factory = self::getContainer()->get('ux.twig_component.component_factory');
        $twig = self::getContainer()->get(Environment::class);
        $metadata = $factory->metadataFor('DivComponent5');

        $extractor = new ComponentReflection($twig);
        $property = $extractor->getProperty($metadata, 'divComponentName');

        $this->assertInstanceOf(ComponentPropertyReflection::class, $property);
        $this->assertEquals('string $divComponentName = "foo"', $property->getCode());
        $this->assertEquals('divComponentName', $property->getName());
        $this->assertEquals('string', $property->getType());
        $this->assertEquals('foo', $property->getDefaultValue());
    }

    public function testUnexistantPropertyByNameReturnsNull(): void
    {
        /** @var ComponentFactory $factory */
        $factory = self::getContainer()->get('ux.twig_component.component_factory');
        $twig = self::getContainer()->get(Environment::class);
        $metadata = $factory->metadataFor('DivComponent5');

        $extractor = new ComponentReflection($twig);
        $property = $extractor->getProperty($metadata, 'unexistant');

        $this->assertNull($property);
    }
}
