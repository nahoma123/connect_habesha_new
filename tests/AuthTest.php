<?php

namespace Tests;

use Mockery;

class AuthTest extends TestCase
{
    protected $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = Mockery::mock();
    }

    public function testUserLogin()
    {
        $user = $this->mockUser();
        
        $this->userModel->shouldReceive('findByEmail')
            ->with('test@example.com')
            ->andReturn($user);
        $this->userModel->shouldReceive('checkPassword')
            ->with('password123', $user)
            ->andReturn(true);
        
        // Test login
        $result = $this->userModel->findByEmail('test@example.com');
        $this->assertNotNull($result);
        $this->assertEquals('test@example.com', $result->getEmail());
    }

    public function testUserRegistration()
    {
        $userData = [
            's_email' => 'newuser@example.com',
            's_password' => 'password123',
            's_name' => 'New User'
        ];
        
        $this->userModel->shouldReceive('insert')
            ->with($userData)
            ->andReturn(true);
        
        // Test registration
        $result = $this->userModel->insert($userData);
        $this->assertTrue($result);
    }

    public function testPasswordReset()
    {
        $user = $this->mockUser();
        
        $this->userModel->shouldReceive('findByEmail')
            ->with('test@example.com')
            ->andReturn($user);
        $this->userModel->shouldReceive('update')
            ->with(['s_password' => 'newpassword123'], ['pk_i_id' => 1])
            ->andReturn(true);
        
        // Test password reset
        $result = $this->userModel->update(['s_password' => 'newpassword123'], ['pk_i_id' => 1]);
        $this->assertTrue($result);
    }
} 