<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_members_index(): void
    {
        $response = $this->get(route('members.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_intern_receives_mentors_list(): void
    {
        $intern = User::factory()->create(['role' => 'intern']);
        $mentor = User::factory()->create(['role' => 'mentor', 'name' => 'John Mentor']);
        $anotherIntern = User::factory()->create(['role' => 'intern', 'name' => 'Jane Intern']);

        $response = $this->actingAs($intern)
            ->getJson(route('members.index'));

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'John Mentor', 'role' => 'mentor']);
        $response->assertJsonMissing(['name' => 'Jane Intern']);
    }

    public function test_mentor_receives_interns_list(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $intern = User::factory()->create(['role' => 'intern', 'name' => 'Bob Intern']);
        $anotherMentor = User::factory()->create(['role' => 'mentor', 'name' => 'Alice Mentor']);

        $response = $this->actingAs($mentor)
            ->getJson(route('members.index'));

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Bob Intern', 'role' => 'intern']);
        $response->assertJsonMissing(['name' => 'Alice Mentor']);
    }

    public function test_admin_receives_all_other_members(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mentor = User::factory()->create(['role' => 'mentor', 'name' => 'Mentor Alpha']);
        $intern = User::factory()->create(['role' => 'intern', 'name' => 'Intern Beta']);

        $response = $this->actingAs($admin)
            ->getJson(route('members.index'));

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Mentor Alpha']);
        $response->assertJsonFragment(['name' => 'Intern Beta']);
    }

    public function test_members_inertia_render_success(): void
    {
        $this->withoutVite();

        $intern = User::factory()->create(['role' => 'intern']);
        User::factory()->create(['role' => 'mentor']);

        $response = $this->actingAs($intern)
            ->get(route('members.index'));

        $response->assertStatus(200);
    }
}
