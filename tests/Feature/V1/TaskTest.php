<?php

namespace Tests\Feature\V1;

use App\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_retrieve_all_tasks()
    {
        $anakin = $this->anakin();
        factory(Task::class, 3)->create(['user_id' => $anakin]);

        $this->actingAs($anakin)
            ->json('GET', '/api/v1/tasks')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'title',
                    'due_at',
                    'author' => [
                        'id',
                        'name',
                        'email'
                    ]
                ]],
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_retrieve_a_task()
    {
        $anakin = $this->anakin();
        $task = factory(Task::class)->create(['user_id' => $anakin]);

        $this->actingAs($anakin)
            ->json('GET', "/api/v1/tasks/{$task->id}")
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'due_at',
                    'author' => [
                        'id',
                        'name',
                        'email'
                    ]
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'due_at' => null,
                    'author' => [
                        'id' => $anakin->id,
                        'name' => $anakin->name,
                        'email' => $anakin->email
                    ]
                ]
            ])
            ->assertJsonCount(1);
    }

    /** @test */
    public function user_can_update_a_task()
    {
        $anakin = $this->anakin();
        $task = factory(Task::class)->create(['user_id' => $anakin]);

        $this->actingAs($anakin)
            ->json('PATCH', "/api/v1/tasks/{$task->id}", [
                'title' => 'Get groceries',
                'due_at' => now()->toDateTimeString(),
                'deleted_at' => now()->toDateTimeString(),
            ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'due_at',
                    'author' => [
                        'id',
                        'name',
                        'email'
                    ]
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $task->id,
                    'title' => 'Get groceries',
                    'due_at' => now()->toATOMString(),
                    'deleted_at' => now()->toATOMString(),
                    'author' => [
                        'id' => $anakin->id,
                        'name' => $anakin->name,
                        'email' => $anakin->email
                    ]
                ]
            ])
            ->assertJsonCount(1);
    }

    /** @test */
    public function user_can_create_a_task()
    {
        $anakin = $this->anakin();

        $this->actingAs($anakin)
            ->json('POST', "/api/v1/tasks", [
                'title' => 'Get groceries',
                'due_at' => now()->toDateTimeString()
            ])
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'due_at',
                    'author' => [
                        'id',
                        'name',
                        'email'
                    ]
                ],
            ])
            ->assertJson([
                'data' => [
                    'title' => 'Get groceries',
                    'due_at' => now()->toATOMString(),
                    'author' => [
                        'id' => $anakin->id,
                        'name' => $anakin->name,
                        'email' => $anakin->email
                    ]
                ]
            ])
            ->assertJsonCount(1);
    }

    /** @test */
    public function user_can_delete_a_task()
    {
        $anakin = $this->anakin();
        $task = factory(Task::class)->create(['user_id' => $anakin]);

        $this->actingAs($anakin)
            ->json('DELETE', "/api/v1/tasks/{$task->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('tasks', $task->toArray());
    }
}
