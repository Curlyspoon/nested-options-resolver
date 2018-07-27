<?php

namespace Curlyspoon\NestedOptionsResolver;

use Symfony\Component\OptionsResolver\OptionsResolver as SymfonyOptionsResolver;

class OptionsResolver extends SymfonyOptionsResolver
{
    /**
     * @var array
     */
    protected $nested = [];

    public static function make(array $config = []): OptionsResolver
    {
        $resolver = new static();

        if (!empty($config['defaults'])) {
            $resolver->setDefaults($config['defaults']);
        }

        if (!empty($config['required'])) {
            $resolver->setRequired($config['required']);
        }

        if (!empty($config['types'])) {
            foreach ($config['types'] as $option => $types) {
                $resolver->setAllowedTypes($option, $types);
            }
        }

        if (!empty($config['values'])) {
            foreach ($config['values'] as $option => $values) {
                $resolver->setAllowedValues($option, $values);
            }
        }

        if (!empty($config['nested'])) {
            foreach ($config['nested'] as $option => $nestedConfig) {
                $resolver->setNested($option, static::make($nestedConfig));
            }
        }

        return $resolver;
    }

    public function setNested(string $key, OptionsResolver $resolver): OptionsResolver
    {
        $this->nested[$key] = $resolver;

        return $this;
    }

    public function isNested(): bool
    {
        return !empty($this->nested);
    }

    public function resolve(array $options = [])
    {
        $resolved = parent::resolve($options);

        foreach ($this->nested as $key => $resolver) {
            $isLoop = substr($key, -2) == '.*';
            if ($isLoop) {
                $key = substr($key, 0, -2);
            }
            $data = $resolved[$key];
            if (!$isLoop) {
                $resolved[$key] = $resolver->resolve($data);
                continue;
            }

            foreach ($data as $subKey => $subData) {
                $resolved[$key][$subKey] = $resolver->resolve($subData);
            }
        }

        return $resolved;
    }
}
