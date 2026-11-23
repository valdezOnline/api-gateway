<?php

namespace App\Support;

use Illuminate\Support\Str;

class ApiServiceCatalog
{
    private const SERVICE_ABILITY_OVERRIDES = [
        'ExLibrisAlma' => ['gateway:read', 'gateway:write'],
        'SisData' => ['gateway:read', 'gateway:write'],
        'IdmsCardData' => ['gateway:read', 'gateway:write'],
    ];

    public function all(): array
    {
        $servicesPath = app_path('Services');
        if (!is_dir($servicesPath)) {
            return [];
        }

        $serviceDirectories = array_filter(
            scandir($servicesPath) ?: [],
            fn ($entry) => $entry !== '.' && $entry !== '..' && is_dir($servicesPath . DIRECTORY_SEPARATOR . $entry)
        );

        $catalog = [];
        foreach ($serviceDirectories as $directory) {
            $folderPath = $servicesPath . DIRECTORY_SEPARATOR . $directory;
            $serviceFiles = glob($folderPath . DIRECTORY_SEPARATOR . '*Service.php');

            if (!is_array($serviceFiles) || $serviceFiles === []) {
                continue;
            }

            $requiredAbilities = self::SERVICE_ABILITY_OVERRIDES[$directory] ?? ['gateway:read'];

            $catalog[] = [
                'key' => Str::kebab($directory),
                'name' => (string) Str::of($directory)->headline(),
                'sourceFolder' => $directory,
                'requiredAbilities' => $requiredAbilities,
            ];
        }

        usort($catalog, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $catalog;
    }
}
