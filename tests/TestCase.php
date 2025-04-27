<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Mockery;

class TestCase extends BaseTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Define ABS_PATH
        if (!defined('ABS_PATH')) {
            define('ABS_PATH', str_replace('//', '/', str_replace('\\', '/', dirname(dirname(__FILE__))) . '/'));
        }
        
        // Set up test environment
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_URI'] = '/';
        
        // Mock database connection
        $this->db = Mockery::mock('DBConnectionClass');
        $this->db->shouldReceive('getOsclassDb')->andReturn($this->db);
        $this->db->shouldReceive('query')->andReturn(true);
        $this->db->shouldReceive('result')->andReturn([]);
        
        // Skip loading Osclass core for unit tests
        // require_once ABS_PATH . 'oc-load.php';
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    protected function mockUser($userId = 1, $email = 'test@example.com', $isAdmin = false)
    {
        $user = Mockery::mock();
        $user->shouldReceive('getId')->andReturn($userId);
        $user->shouldReceive('getEmail')->andReturn($email);
        $user->shouldReceive('isAdmin')->andReturn($isAdmin);
        return $user;
    }

    protected function mockItem($itemId = 1, $title = 'Test Item', $description = 'Test Description')
    {
        $item = Mockery::mock();
        $item->shouldReceive('getId')->andReturn($itemId);
        $item->shouldReceive('getTitle')->andReturn($title);
        $item->shouldReceive('getDescription')->andReturn($description);
        return $item;
    }
} 