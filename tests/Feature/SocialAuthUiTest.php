<?php

namespace Tests\Feature;

use Tests\TestCase;

class SocialAuthUiTest extends TestCase
{
    public function test_login_page_shows_google_auth_option(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee(route('social.redirect', 'google'), false);
        $response->assertSee('Google');
    }

    public function test_register_page_shows_google_auth_option(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertSee(route('social.redirect', 'google'), false);
        $response->assertSee('Google');
    }
}
