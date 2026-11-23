<?php

namespace Tests\Unit\Support;

use App\Support\ApiServiceCatalog;
use Tests\TestCase;

class ApiServiceCatalogTest extends TestCase
{
    public function test_catalog_is_built_from_service_folders(): void
    {
        $catalog = app(ApiServiceCatalog::class)->all();

        $this->assertNotEmpty($catalog);
        $this->assertTrue(collect($catalog)->contains(fn ($item) => ($item['sourceFolder'] ?? null) === 'ExLibrisAlma'));
        $this->assertTrue(collect($catalog)->contains(fn ($item) => ($item['sourceFolder'] ?? null) === 'UcrPerson'));
    }
}
