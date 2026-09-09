<?php

namespace Tests\Feature;

use App\Models\Exercise;
use Database\Seeders\ExerciseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ExerciseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_exercise_seeder_seeds_all_baseline_exercises_with_media(): void
    {
        $this->seed(ExerciseSeeder::class);

        $exercises = Exercise::withoutGlobalScope('center_scope')->get();

        $this->assertGreaterThanOrEqual(30, $exercises->count());

        foreach ($exercises as $exercise) {
            $this->assertTrue($exercise->is_system);
            $this->assertNull($exercise->center_id);
            $this->assertNull($exercise->therapist_id);
            $this->assertTrue($exercise->hasMedia('video'));
        }
    }

    public function test_system_exercises_cannot_be_deleted(): void
    {
        $this->seed(ExerciseSeeder::class);

        $exercise = Exercise::withoutGlobalScope('center_scope')->where('is_system', true)->first();

        $this->assertNotNull($exercise);

        $this->expectException(ValidationException::class);

        $exercise->delete();
    }
}
