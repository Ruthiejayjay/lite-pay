<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Notification $notification;

    protected function setup(): void
    {
        parent::setup();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);
    }

    public function test_it_can_fetch_notifications_for_authenticated_user()
    {
        $otherNotification = Notification::factory()->create();

        $response = $this->getJson(route('notifications.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'type',
                        'message',
                        'is_read',
                        'created_at',
                        'updated_at'
                    ],
                ],
            ]);

        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $this->notification->id]);
        $response->assertJsonMissing(['id' => $otherNotification->id]);
    }

    public function test_it_can_mark_a_notification_as_read()
    {
        $response = $this->patchJson(route('notifications.markAsRead', $this->notification->id));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Notification marked as read.',
            ]);

        $this->assertDatabaseHas('notifications', [
            'id' => $this->notification->id,
            'is_read' => true,
        ]);
    }

    public function test_it_returns_404_if_notification_does_not_exist()
    {
        $response = $this->patchJson(route('notifications.markAsRead', 99999));

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'failure',
                'message' => 'Notification not found.',
            ]);
    }



    public function test_it_prevents_unauthorized_user_from_marking_a_notification_as_read()
    {
        $otherUserNotification = Notification::factory()->create();

        $response = $this->patchJson(route('notifications.markAsRead', $otherUserNotification->id));

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'This action is unauthorized.',
            ]);
    }
}
