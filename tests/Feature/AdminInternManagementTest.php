<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInternManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_interns_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.interns.index'));

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_view_interns_page(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $intern = User::factory()->create(['role' => 'intern']);

        $responseMentor = $this->actingAs($mentor)->get(route('admin.interns.index'));
        $responseMentor->assertStatus(403);

        $responseIntern = $this->actingAs($intern)->get(route('admin.interns.index'));
        $responseIntern->assertStatus(403);
    }

    public function test_admin_can_promote_intern_to_mentor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $intern = User::factory()->create([
            'name' => 'John Intern',
            'email' => 'intern@example.com',
            'role' => 'intern',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.interns.promote', $intern->id));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', [
            'id' => $intern->id,
            'role' => 'mentor',
        ]);
    }

    public function test_promoted_intern_can_login_as_mentor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $intern = User::factory()->create([
            'email' => 'promoted@example.com',
            'password' => bcrypt('password123'),
            'role' => 'intern',
        ]);

        $this->actingAs($admin)->post(route('admin.interns.promote', $intern->id));

        $this->post('/logout');

        $response = $this->post(route('login'), [
            'email' => 'promoted@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();
        $this->assertEquals('mentor', auth()->user()->role);
    }
}
