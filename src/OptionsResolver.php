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

        $resolver
            ->loadConfigDefaults($config)
            ->loadConfigRequired($config)
            ->loadConfigTypes($config)
            ->loadConfigValues($config)
            ->loadConfigNested($config);

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

    protected function loadConfigDefaults(array $config): OptionsResolver
    {
        if (!empty($config['defaults'])) {
            $this->setDefaults($config['defaults']);
        }

        return $this;
    }

    protected function loadConfigRequired(array $config): OptionsResolver
    {
        if (!empty($config['required'])) {
            $this->setRequired($config['required']);
        }

        return $this;
    }

    protected function loadConfigTypes(array $config): OptionsResolver
    {
        if (!empty($config['types'])) {
            foreach ($config['types'] as $option => $types) {
                $this->setAllowedTypes($option, $types);
            }
        }

        return $this;
    }

    protected function loadConfigValues(array $config): OptionsResolver
    {
        if (!empty($config['values'])) {
            foreach ($config['values'] as $option => $values) {
                $this->setAllowedValues($option, $values);
            }
        }

        return $this;
    }

    protected function loadConfigNested(array $config): OptionsResolver
    {
        if (!empty($config['nested'])) {
            foreach ($config['nested'] as $option => $nestedConfig) {
                $this->setNested($option, static::make($nestedConfig));
            }
        }

        return $this;
    }
}
