<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginPageTest extends TestCase
{
    public function test_user_can_view_the_login_page(): void
    {
        $response = $this->get('/login');

        $response
            ->assertOk()
            ->assertSee('Sign in to your account')
            ->assertSee('name="user_name"', false)
            ->assertSee('name="password"', false)
            ->assertSee('/api/v1/login', false);
    }

    public function test_login_route_is_named_for_auth_redirects(): void
    {
        $this->assertSame('/login', route('login', [], false));
    }
}
