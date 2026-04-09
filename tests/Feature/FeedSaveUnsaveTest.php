<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FeedSaveUnsaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_saves_and_unsaves_a_learning_module_from_the_feed(): void
    {
        $moduleClass = \App\Models\LearningModule::class;
        $user = User::factory()->create();

        $module = $moduleClass::query()->create([
            'title' => 'Test Module',
            'status' => 'published',
        ]);

        $saveResponse = $this
            ->actingAs($user)
            ->post(route('app.feed.save', ['module' => $module->getKey()]));
        $saveResponse->assertStatus(302);

        $this->assertDatabaseHas('saved_learning_modules', [
            'user_id' => $user->id,
            'learning_module_id' => $module->getKey(),
        ]);

        $saveAgainResponse = $this
            ->actingAs($user)
            ->post(route('app.feed.save', ['module' => $module->getKey()]));
        $saveAgainResponse->assertStatus(302);

        $count = DB::table('saved_learning_modules')
            ->where('user_id', $user->id)
            ->where('learning_module_id', $module->getKey())
            ->count();
        $this->assertSame(1, $count);

        $otherUser = User::factory()->create();

        DB::table('saved_learning_modules')->insert([
            'user_id' => $otherUser->id,
            'learning_module_id' => $module->getKey(),
            'created_at' => now(),
        ]);

        $unsaveResponse = $this
            ->actingAs($user)
            ->post(route('app.feed.unsave', ['module' => $module->getKey()]));
        $unsaveResponse->assertStatus(302);

        $this->assertDatabaseMissing('saved_learning_modules', [
            'user_id' => $user->id,
            'learning_module_id' => $module->getKey(),
        ]);

        $this->assertDatabaseHas('saved_learning_modules', [
            'user_id' => $otherUser->id,
            'learning_module_id' => $module->getKey(),
        ]);
    }
}
