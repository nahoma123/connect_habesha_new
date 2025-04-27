<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testBasicAssertion()
    {
        $this->assertTrue(true);
    }

    public function testStringOperations()
    {
        $string = 'Hello, World!';
        $this->assertEquals('Hello, World!', $string);
        $this->assertStringContainsString('World', $string);
    }

    public function testArrayOperations()
    {
        $array = [1, 2, 3];
        $this->assertCount(3, $array);
        $this->assertContains(2, $array);
    }
} 