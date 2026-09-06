<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('start:fresh')]
#[Description('Clean tenant storage, run fresh migrations with seed, and clean orphaned media files')]
class StartFreshCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Cleaning up tenant storage directories...');
        $tenantPublicStorage = storage_path('app/public/tenant');
        if (File::exists($tenantPublicStorage)) {
            File::deleteDirectory($tenantPublicStorage);
        }

        $storagePath = storage_path();
        $directories = File::directories($storagePath);
        foreach ($directories as $directory) {
            if (str_starts_with(basename($directory), 'tenant')) {
                File::deleteDirectory($directory);
            }
        }

        $this->info('Starting fresh migration and seeding...');
        $this->call('migrate:fresh', [
            '--seed' => true,
        ]);

        $this->info('Cleaning up orphaned media files...');
        $this->call('media-library:clean');

        $this->info('Done!');
    }
}
