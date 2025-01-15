<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent;

use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Symfony\UX\TwigComponent\Twig\PropsNode;
use Twig\Environment;

/**
 * @author Jean-François Lépine <lepinejeanfrancois@gmail.com>
 */
final class ComponentReflection
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    /**
     * @return ComponentPropertyReflection[]
     */
    public function getProperties(ComponentMetadata $medata): array
    {
        if ($medata->isAnonymous()) {
            return $this->getAnonymousComponentProperties($medata);
        }

        return $this->getNonAnonymousComponentProperties($medata);
    }

    public function getProperty(ComponentMetadata $medata, string $name): ?ComponentPropertyReflection
    {
        $properties = $this->getProperties($medata);

        return $properties[$name] ?? null;
    }

    /**
     * @return ComponentPropertyReflection[]
     */
    private function getNonAnonymousComponentProperties(ComponentMetadata $metadata): array
    {
        $properties = [];
        $reflectionClass = new \ReflectionClass($metadata->getClass());
        foreach ($reflectionClass->getProperties() as $property) {
            $propertyName = $property->getName();

            if ($metadata->isPublicPropsExposed() && $property->isPublic()) {
                $type = $property->getType();
                if ($type instanceof \ReflectionNamedType) {
                    $typeName = $type->getName();
                } else {
                    $typeName = (string) $type;
                }
                $value = $property->getDefaultValue();
                $properties[$propertyName] = new ComponentPropertyReflection($metadata, $propertyName, $typeName, $value);
            }

            foreach ($property->getAttributes(ExposeInTemplate::class) as $exposeAttribute) {
                /** @var ExposeInTemplate $attribute */
                $attribute = $exposeAttribute->newInstance();
                $properties[$property->name] = new ComponentPropertyReflection($metadata, $attribute->name ?? $property->name);
            }
        }

        return $properties;
    }

    /**
     * Extract properties from {% props %} tag in anonymous template.
     *
     * @return ComponentPropertyReflection[]
     */
    private function getAnonymousComponentProperties(ComponentMetadata $metadata): array
    {
        $source = $this->twig->load($metadata->getTemplate())->getSourceContext();
        $tokenStream = $this->twig->tokenize($source);
        $moduleNode = $this->twig->parse($tokenStream);

        $propsNode = null;
        foreach ($moduleNode->getNode('body') as $bodyNode) {
            foreach ($bodyNode as $node) {
                if (PropsNode::class === $node::class) {
                    $propsNode = $node;
                    break 2;
                }
            }
        }
        if (!$propsNode instanceof PropsNode) {
            return [];
        }

        $propertyNames = $propsNode->getAttribute('names');
        $properties = [];
        foreach ($propertyNames as $propName) {
            $properties[$propName] = new ComponentPropertyReflection($metadata, $propName, 'mixed');
        }

        foreach ($propertyNames as $propName) {
            if ($propsNode->hasNode($propName)
                && ($valueNode = $propsNode->getNode($propName))
                && $valueNode->hasAttribute('value')
            ) {
                $value = $valueNode->getAttribute('value');
                $properties[$propName] = new ComponentPropertyReflection($metadata, $propName, 'mixed', $value);
            }
        }

        return $properties;
    }
}
