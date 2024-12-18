<?php

namespace Tests\Feature\Password;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        $this->actingAs($this->user, 'sanctum');
    }

    public function test_user_can_change_password_with_valid_verification_code()
    {
        $verificationCode = 'ABC123';
        Cache::put('password_change_code_' . $this->user->id, $verificationCode, now()->addMinutes(20));

        $response = $this->postJson(route('password.change'), [
            'verification_code' => $verificationCode,
            'new_password' => 'newsecurepassword',
            'new_password_confirmation' => 'newsecurepassword',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Your password has been changed successfully.',
            ]);

        $this->assertTrue(Hash::check('newsecurepassword', $this->user->refresh()->password));
        $this->assertFalse(Cache::has('password_change_code_' . $this->user->id));
    }

    public function test_user_cannot_change_password_with_invalid_verification_code()
    {
        Cache::put('password_change_code_' . $this->user->id, 'ABC123', now()->addMinutes(20));

        $response = $this->postJson(route('password.change'), [
            'verification_code' => 'WRONGCODE',
            'new_password' => 'newsecurepassword',
            'new_password_confirmation' => 'newsecurepassword',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid or expired verification code.',
            ]);

        $this->assertTrue(Hash::check('oldpassword', $this->user->refresh()->password));
        $this->assertTrue(Cache::has('password_change_code_' . $this->user->id));
    }

    public function test_user_cannot_change_password_with_expired_verification_code()
    {
        $response = $this->postJson(route('password.change'), [
            'verification_code' => 'EXPIRED123',
            'new_password' => 'newsecurepassword',
            'new_password_confirmation' => 'newsecurepassword',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid or expired verification code.',
            ]);

        $this->assertTrue(Hash::check('oldpassword', $this->user->refresh()->password));
    }

    public function test_user_cannot_change_password_with_invalid_password_confirmation()
    {
        $verificationCode = 'ABC123';
        Cache::put('password_change_code_' . $this->user->id, $verificationCode, now()->addMinutes(20));

        $response = $this->postJson(route('password.change'), [
            'verification_code' => $verificationCode,
            'new_password' => 'newsecurepassword',
            'new_password_confirmation' => 'differentpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    // public function test_guest_user_cannot_change_password()
    // {
    //     Cache::put('password_change_code_' . $this->user->id, 'ABC123', now()->addMinutes(20));

    //     Auth::logout();
    //     $response = $this->postJson(route('password.change'), [
    //         'verification_code' => 'ABC123',
    //         'new_password' => 'newsecurepassword',
    //         'new_password_confirmation' => 'newsecurepassword',
    //     ]);

    //     $response->assertStatus(401)
    //         ->assertJson([
    //             'message' => 'Unauthenticated.',
    //         ]);

    //     $this->assertTrue(Hash::check('oldpassword', $this->user->refresh()->password));
    // }
}
