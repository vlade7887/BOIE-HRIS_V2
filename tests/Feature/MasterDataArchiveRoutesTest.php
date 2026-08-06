<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MasterDataArchiveRoutesTest extends TestCase
{
    public function test_master_data_resources_do_not_expose_destroy_routes(): void
    {
        foreach (self::masterDataResourceNames() as [$resource]) {
            $this->assertFalse(Route::has("{$resource}.destroy"));
        }
    }

    public function test_master_data_archive_and_restore_routes_are_retained(): void
    {
        foreach (self::masterDataResourceNames() as [$prefix]) {
            $this->assertTrue(Route::has("{$prefix}.archive"));
            $this->assertTrue(Route::has("{$prefix}.restore"));
        }
    }

    public static function masterDataResourceNames(): array
    {
        return [
            ['companies'],
            ['bases'],
            ['units'],
            ['departments'],
            ['sections'],
            ['positions'],
            ['employment-statuses'],
            ['employee-classes'],
        ];
    }

}
