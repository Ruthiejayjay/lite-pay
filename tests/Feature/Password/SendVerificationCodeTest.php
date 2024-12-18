<?php

namespace Tests\Feature\Password;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendVerificationCodeTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Step 1: Create a user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Step 2: Authenticate the user
        $this->actingAs($this->user, 'sanctum');
    }

    public function test_user_receives_verification_code_successfully()
    {
        Mail::fake();

        $response = $this->postJson(
            route('password.verify'),
            [
                'email' => $this->user->email,
            ]
        );
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'A verification code has been sent to your email address.'
            ]);
        $this->assertTrue(Cache::has('password_change_code_' . $this->user->id));
    }

    public function test_user_cannot_send_code_with_invalid_email()
    {
        $response = $this->postJson(route('password.verify'), [
            'email' => 'wrong@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJson([
                'message' => 'The selected email is invalid.',
            ]);
    }
}
