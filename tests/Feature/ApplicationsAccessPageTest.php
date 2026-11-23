<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApplicationsAccessPageTest extends TestCase
{
    public function test_user_can_view_the_applications_access_page(): void
    {
        $response = $this->get('/applications-access');

        $response
            ->assertOk()
            ->assertSee('Application access dashboard')
            ->assertSee('id="applications-body"', false)
            ->assertSee('/applications-access/create', false)
            ->assertSee('Sign out');
    }

    public function test_user_can_view_the_create_application_page(): void
    {
        $response = $this->get('/applications-access/create');

        $response
            ->assertOk()
            ->assertSee('Create an application')
            ->assertSee('id="create-application-form"', false)
            ->assertSee('id="service-options"', false)
            ->assertSee('Generate key')
            ->assertSee('/applications-access', false);
    }

    public function test_user_can_view_the_edit_application_page(): void
    {
        $response = $this->get('/applications-access/1/edit');

        $response
            ->assertOk()
            ->assertSee('Edit application')
            ->assertSee('id="edit-application-form"', false)
            ->assertSee('id="renew-button"', false);
    }
}
