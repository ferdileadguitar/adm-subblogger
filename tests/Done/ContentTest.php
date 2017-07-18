<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ContentTest extends TestCase
{
    use WithoutMiddleware;

    public function _testPage()
    {
        $response = $this->get('/contents');
        $response->assertViewHas('pageTitle');
    }

    public function testAPI()
    {
    	// Test All
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'all-time',
			'status'    => 'all-status',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test Today
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'today',
			'status'    => 'all-status',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test Yesterday
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'yesterday',
			'status'    => 'all-status',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test 7 Days
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'last-7-days',
			'status'    => 'all-status',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test 30 Days
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'last-30-days',
			'status'    => 'all-status',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test 90 Days
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'last-90-days',
			'status'    => 'all-status',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test This Month
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'this-month',
			'status'    => 'all-status',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test This Year
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'this-year',
			'status'    => 'all-status',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test Custom Date
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'startDate' => '2015-01-01',
			'endDate' 	=> '2015-05-01',
			'status'    => 'all-status',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test Page
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 2,
			'dateRange' => 'all-time',
			'status'    => 'all-status',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 2]);

    	// Test Search
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'yesterday',
			'status'    => 'all-status',
			'search'    => 'Test',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test Moderate
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'all-time',
			'status'    => 'moderated',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test Today
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'today',
			'status'    => 'moderated',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test Yesterday
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'yesterday',
			'status'    => 'moderated',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test 7 Days
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'last-7-days',
			'status'    => 'moderated',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test 30 Days
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'last-30-days',
			'status'    => 'moderated',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test 90 Days
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'last-90-days',
			'status'    => 'moderated',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test This Month
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'this-month',
			'status'    => 'moderated',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test This Year
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'this-year',
			'status'    => 'moderated',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test Custom Date
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'startDate' => '2015-01-01',
			'endDate' 	=> '2015-05-01',
			'status'    => 'moderated',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test Page
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 2,
			'dateRange' => 'all-time',
			'status'    => 'moderated',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 2]);

    	// Test Search
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'yesterday',
			'status'    => 'moderated',
			'search'    => 'Test',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test Status
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'yesterday',
			'status'    => 'private',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'yesterday',
			'status'    => 'public',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'yesterday',
			'status'    => 'approved',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'yesterday',
			'status'    => 'rejected',
			'search'    => '',
			'key'       => 'created',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	// Test Sort
    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'yesterday',
			'status'    => 'rejected',
			'search'    => '',
			'key'       => 'channel',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'yesterday',
			'status'    => 'rejected',
			'search'    => '',
			'key'       => 'post_type',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'yesterday',
			'status'    => 'rejected',
			'search'    => '',
			'key'       => 'view',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'yesterday',
			'status'    => 'rejected',
			'search'    => '',
			'key'       => 'share',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);

    	$response = $this->json('GET', '/api/contents', [
			'page'      => 1,
			'dateRange' => 'yesterday',
			'status'    => 'rejected',
			'search'    => '',
			'key'       => 'embed',
			'reverse'   => TRUE,
    	]);
    	$response->assertStatus(200)
    			 ->assertJson(['current_page' => 1]);
    }
}
