<?php

namespace Database\Seeders;

use App\Models\Exercise;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ExerciseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dir = database_path('seeders/data/exercises');

        if (! File::exists($dir)) {
            $dir = storage_path('app/public/exercises');
        }

        if (! File::exists($dir)) {
            $this->command?->warn("No exercises directory found at {$dir}. Skipping ExerciseSeeder.");
            return;
        }

        $files = File::files($dir);

        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());
            if (! in_array($extension, ['mov', 'mp4', 'm4v', 'avi', 'mkv', 'webm'])) {
                continue;
            }

            $rawName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $cleanTitle = $this->formatTitle($rawName);

            $exercise = Exercise::withoutGlobalScope('center_scope')->firstOrCreate(
                [
                    'title' => $cleanTitle,
                    'is_system' => true,
                ],
                [
                    'center_id' => null,
                    'therapist_id' => null,
                    'default_sets' => 3,
                    'default_repeats' => 12,
                    'default_duration' => 90,
                    'therapist_notes' => 'Standard system-approved exercise.',
                ]
            );

            if (! $exercise->hasMedia('video')) {
                $exercise->addMedia($file->getRealPath())
                    ->preservingOriginal()
                    ->toMediaCollection('video');
            }
        }
    }

    /**
     * Format filename into clean exercise title.
     */
    private function formatTitle(string $rawName): string
    {
        // Remove trailing timestamp artifacts if present e.g. "1101 PM"
        $cleaned = preg_replace('/\s*\d{3,4}\s*(AM|PM)\s*/i', '', $rawName);
        // Replace multiple spaces and trim
        $cleaned = trim(preg_replace('/\s+/', ' ', $cleaned));

        return Str::title($cleaned);
    }
}
