<?php

use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use Curlyspoon\NestedOptionsResolver\OptionsResolver;

class OptionsResolverTest extends PhpUnitTestCase
{
    /** @test */
    public function make_resolver()
    {
        $this->assertInstanceOf(OptionsResolver::class, OptionsResolver::make());
    }

    /** @test */
    public function make_basic_resolver()
    {
        $resolver = OptionsResolver::make([
            'defaults' => [
                'name' => 'John Doe',
            ],
        ]);

        $this->assertFalse($resolver->isNested());
        $this->assertEquals('John Doe', $resolver->resolve()['name']);
    }

    /** @test */
    public function make_nested_resolver()
    {
        $resolver = OptionsResolver::make([
            'required' => [
                'images',
            ],
            'types' => [
                'images' => 'array[]',
            ],
            'nested' => [
                'images.*' => [
                    'required' => [
                        'source',
                        'size',
                    ],
                    'defaults' => [
                        'size' => 'original',
                    ],
                ],
            ],
        ]);
        $this->assertTrue($resolver->isNested());

        $resolved = $resolver->resolve([
            'images' => [
                [
                    'source' => 'https://example.com/image.jpg',
                ],
            ],
        ]);

        $this->assertEquals('original', $resolved['images'][0]['size']);
        $this->assertEquals('https://example.com/image.jpg', $resolved['images'][0]['source']);
    }

    /** @test */
    public function make_full_resolver()
    {
        $resolver = OptionsResolver::make([
            'defaults' => [
                'name' => 'John Doe',
            ],
            'required' => [
                'name',
                'images',
            ],
            'types' => [
                'name' => 'string',
                'images' => 'array[]',
            ],
            'nested' => [
                'images.*' => [
                    'required' => [
                        'source',
                        'size',
                    ],
                    'defaults' => [
                        'size' => 'original',
                    ],
                    'types' => [
                        'source' => 'string',
                        'size' => 'string',
                    ],
                    'values' => [
                        'size' => [
                            'original',
                            'medium',
                            'small',
                            'thumb',
                        ],
                    ],
                ],
            ],
        ]);

        $resolved = $resolver->resolve([
            'name' => 'Jane Doe',
            'images' => [
                [
                    'source' => 'https://example.com/image.jpg',
                ],
            ],
        ]);

        $this->assertEquals('Jane Doe', $resolved['name']);
        $this->assertEquals('original', $resolved['images'][0]['size']);
        $this->assertEquals('https://example.com/image.jpg', $resolved['images'][0]['source']);
    }
}
