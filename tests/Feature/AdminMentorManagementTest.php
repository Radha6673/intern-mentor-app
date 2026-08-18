<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMentorManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_mentors_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.mentors.index'));

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_view_mentors_page(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $intern = User::factory()->create(['role' => 'intern']);

        $responseMentor = $this->actingAs($mentor)->get(route('admin.mentors.index'));
        $responseMentor->assertStatus(403);

        $responseIntern = $this->actingAs($intern)->get(route('admin.mentors.index'));
        $responseIntern->assertStatus(403);
    }

    public function test_admin_can_add_new_mentor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.mentors.store'), [
            'name' => 'New Mentor',
            'email' => 'newmentor@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', [
            'name' => 'New Mentor',
            'email' => 'newmentor@example.com',
            'role' => 'mentor',
        ]);
    }

    public function test_added_mentor_can_login(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.mentors.store'), [
            'name' => 'Login Mentor',
            'email' => 'loginmentor@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->post('/logout');

        $response = $this->post(route('login'), [
            'email' => 'loginmentor@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();
    }

    public function test_admin_can_delete_mentor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mentor = User::factory()->create(['role' => 'mentor']);

        $response = $this->actingAs($admin)->delete(route('admin.mentors.destroy', $mentor->id));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('users', [
            'id' => $mentor->id,
        ]);
    }
}
