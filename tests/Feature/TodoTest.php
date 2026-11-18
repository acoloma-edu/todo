<?php

namespace Tests\Feature;

use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoTest extends TestCase
{
    use RefreshDatabase;

    private function makeTodo(array $attributes = []): Todo
    {
        return Todo::create($attributes + [
            'title' => 'Buy some groceries',
            'body' => 'Milk, eggs and bread for the week',
        ]);
    }

    public function test_index_lists_todos(): void
    {
        $this->makeTodo();

        $this->get('/')
            ->assertOk()
            ->assertSee('Buy some groceries');
    }

    public function test_it_creates_a_todo(): void
    {
        $this->from('/')
            ->post(route('todo.store'), [
                'title' => 'Write the report',
                'body' => 'Quarterly report for the team',
            ])
            ->assertRedirect('/')
            ->assertSessionHas('successMessage');

        $this->assertDatabaseHas('todos', ['title' => 'Write the report', 'is_done' => false]);
    }

    public function test_it_validates_input(): void
    {
        $this->post(route('todo.store'), ['title' => 'short', 'body' => ''])
            ->assertSessionHasErrors(['title', 'body']);

        $this->assertDatabaseCount('todos', 0);
    }

    public function test_it_shows_the_edit_form(): void
    {
        $todo = $this->makeTodo();

        $this->get(route('todo.edit', $todo->id))
            ->assertOk()
            ->assertSee('Update');
    }

    public function test_it_updates_a_todo(): void
    {
        $todo = $this->makeTodo();

        $this->put(route('todo.update', $todo->id), [
            'title' => 'Buy lots of groceries',
            'body' => 'Milk, eggs, bread and coffee',
        ])->assertSessionHas('successMessage');

        $this->assertSame('Buy lots of groceries', $todo->fresh()->title);
    }

    public function test_it_toggles_done(): void
    {
        $todo = $this->makeTodo();

        $this->post(route('todo.change-is-done', $todo->id));
        $this->assertTrue($todo->fresh()->is_done);

        $this->post(route('todo.change-is-done', $todo->id));
        $this->assertFalse($todo->fresh()->is_done);
    }

    public function test_it_deletes_a_todo(): void
    {
        $todo = $this->makeTodo();

        $this->delete(route('todo.destroy', $todo->id))
            ->assertRedirect(route('todo.index'));

        $this->assertModelMissing($todo);
    }

    public function test_missing_todo_returns_404(): void
    {
        $this->get(route('todo.edit', 999))->assertNotFound();
    }
}
