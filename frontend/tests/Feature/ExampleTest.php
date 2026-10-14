<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test that root '/' redirects to dashboard.
     */
    public function test_root_redirects_to_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
    }

    /**
     * Test that login page loads successfully with HTTP 200.
     */
    public function test_login_page_returns_successful_response(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Checklist Sarfas HSSE');
        $response->assertSee('Pertamina');
    }

    /**
     * Test that unauthenticated users accessing protected routes are redirected to login.
     */
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Test that unauthenticated users cannot access sarfas inventory directly.
     */
    public function test_unauthenticated_user_cannot_access_sarfas(): void
    {
        $response = $this->get('/sarfas');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Test that unauthenticated users cannot access reports directly.
     */
    public function test_unauthenticated_user_cannot_access_reports(): void
    {
        $response = $this->get('/reports');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
