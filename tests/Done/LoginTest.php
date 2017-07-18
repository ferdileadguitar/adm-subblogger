<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LoginTest extends TestCase
{
    public function testLoginCredentials()
    {
    	// If Username and Password are empty
    	$response = $this->call('POST', '/login', []);
    	$this->assertEquals(500, $response->status());

    	// If Username or Password is empty
    	$response = $this->call('POST', '/login', ['username' => 'Test']);
    	$this->assertEquals(500, $response->status());

    	$response = $this->call('POST', '/login', ['password' => 'test']);
    	$this->assertEquals(500, $response->status());
    }

    public function testLogin()
    {
    	// If unknown user
    	$response = $this->call('POST', '/login', ['username' => 'asd', 'password' => 'asd']);
    	$this->assertEquals(500, $response->status());

    	// If Username right but Password is invalid
    	$response = $this->call('POST', '/login', ['username' => 'pyokola', 'password' => 'asd']);
    	$this->assertEquals(500, $response->status());

    	// If Username and Password are both right
    	$response = $this->call('POST', '/login', ['username' => 'pyokola', 'password' => 'pyokodesu']);
    	$response->assertStatus(302);

    	// Logout
    	$response = $this->get('/logout');
    	$response->assertRedirect('login');

    	// Login via AJAX
    	$response = $this->json('POST', '/login', ['username' => 'pyokola', 'password' => 'pyokodesu'], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
    	$response->assertStatus(200)
    			 ->assertJson(['status' => 'Welcome aboard, mate!', 'url' => url('/')]);

    	// Logout
    	$response = $this->get('/logout');
    	$response->assertRedirect('login');
    }
}
