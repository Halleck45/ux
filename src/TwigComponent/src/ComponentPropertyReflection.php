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

/**
 * @author Jean-François Lépine <lepinejeanfrancois@gmail.com>
 */
class ComponentPropertyReflection
{
    public function __construct(
        private readonly ComponentMetadata $metadata,
        private readonly string $name,
        private readonly string $type = 'mixed',
        private readonly mixed $defaultValue = null,
    ) {
    }

    public function getCode(): string
    {
        if (null === $this->defaultValue) {
            return \sprintf('%s $%s = ""', $this->type, $this->name);
        }

        if (\is_bool($this->defaultValue)) {
            return \sprintf('%s $%s = %s', $this->type, $this->name, $this->defaultValue ? 'true' : 'false');
        }

        return \sprintf('%s $%s = %s', $this->type, $this->name, json_encode($this->defaultValue));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function getMetadata(): ComponentMetadata
    {
        return $this->metadata;
    }
}
