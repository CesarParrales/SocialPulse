<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function migrateFreshUsing()
    {
        $modulePaths = collect(glob(base_path('Modules/*/database/migrations')) ?: [])
            ->filter(fn (string $path) => is_dir($path))
            ->values()
            ->all();

        return [
            '--path' => array_merge([database_path('migrations')], $modulePaths),
        ];
    }
}
