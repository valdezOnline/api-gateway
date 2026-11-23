<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ScaffoldApiServiceCommand extends Command
{
    protected $signature = 'app:scaffold-api-service
                            {name : Service name in StudlyCase (example: LibraryStatus)}
                            {--service-key= : Config services key (default: camelCase of name)}
                            {--controller-namespace=v1 : API controller namespace under App\Http\Controllers\Api}
                            {--model-name= : Model class name (default: same as service name)}
                            {--with-route : Register a default GET route in routes/api_v1.php}
                            {--route-uri= : Route URI segment (default: kebab-case of service name)}
                            {--force : Overwrite existing scaffold files}';

    protected $description = 'Scaffold API controller, service, provider, and model following project architecture';

    public function handle(): int
    {
        $serviceName = Str::studly((string) $this->argument('name'));
        $serviceKey = (string) ($this->option('service-key') ?: Str::camel($serviceName));
        $controllerNamespace = trim((string) $this->option('controller-namespace'), '\\/');
        $modelName = Str::studly((string) ($this->option('model-name') ?: $serviceName));
        $withRoute = (bool) $this->option('with-route');
        $routeUri = trim((string) ($this->option('route-uri') ?: Str::kebab($serviceName)), '/');
        $force = (bool) $this->option('force');

        $targets = [
            app_path("Http/Controllers/Api/{$controllerNamespace}/{$serviceName}Controller.php") => $this->controllerStub($serviceName, $controllerNamespace),
            app_path("Services/{$serviceName}/{$serviceName}Service.php") => $this->serviceStub($serviceName, $serviceKey),
            app_path("Providers/{$serviceName}ServiceProvider.php") => $this->providerStub($serviceName, $serviceKey),
            app_path("Models/{$modelName}.php") => $this->modelStub($modelName),
        ];

        foreach ($targets as $path => $contents) {
            $status = $this->writeFile($path, $contents, $force);
            if ($status === 'created' || $status === 'updated') {
                $this->line(sprintf('%s %s', $status === 'created' ? 'Created:' : 'Updated:', $this->relativePath($path)));
                continue;
            }

            $this->warn('Skipped (exists): ' . $this->relativePath($path));
        }

        $providerRegistrationStatus = $this->registerProvider($serviceName);
        $serviceConfigStatus = $this->registerServiceConfig($serviceKey);
        $routeRegistrationStatus = $withRoute
            ? $this->registerRoute($serviceName, $controllerNamespace, $routeUri)
            : 'not requested';

        $this->newLine();
        $this->info('Scaffold summary');
        $this->line('---------------');
        $this->line('Controller: App\Http\Controllers\Api\\' . $controllerNamespace . '\\' . $serviceName . 'Controller');
        $this->line('Service: App\Services\\' . $serviceName . '\\' . $serviceName . 'Service');
        $this->line('Provider: App\Providers\\' . $serviceName . 'ServiceProvider');
        $this->line('Model: App\Models\\' . $modelName);
        $this->line('Provider registration in config/app.php: ' . $providerRegistrationStatus);
        $this->line("Service config in config/services.php ('{$serviceKey}'): " . $serviceConfigStatus);
        $this->line('Route registration in routes/api_v1.php: ' . $routeRegistrationStatus);
        $this->newLine();
        $this->info('Next step: customize endpoint URLs and response mapping in the generated service/controller.');

        if (
            $providerRegistrationStatus === 'failed'
            || $serviceConfigStatus === 'failed'
            || $routeRegistrationStatus === 'failed'
        ) {
            $this->error('Failed to update one or more configuration files. Please apply those changes manually.');

            return 1;
        }

        return 0;
    }

    private function writeFile(string $path, string $contents, bool $force): string
    {
        $directory = dirname($path);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $exists = File::exists($path);
        if ($exists && ! $force) {
            return 'skipped';
        }

        File::put($path, $contents);

        if (! File::exists($path)) {
            return 'skipped';
        }

        return $exists ? 'updated' : 'created';
    }

    private function registerProvider(string $serviceName): string
    {
        $configAppPath = config_path('app.php');
        $providerClassEntry = "App\\Providers\\{$serviceName}ServiceProvider::class";
        $configApp = File::get($configAppPath);

        if (Str::contains($configApp, $providerClassEntry)) {
            return 'already present';
        }

        $aliasesPosition = strpos($configApp, "'aliases' =>");
        if ($aliasesPosition === false) {
            return 'failed';
        }

        $beforeAliases = substr($configApp, 0, $aliasesPosition);
        $providersEnd = strrpos($beforeAliases, '],');
        if ($providersEnd === false) {
            return 'failed';
        }

        $insertion = "        {$providerClassEntry},\n";
        $updatedConfig = substr($configApp, 0, $providersEnd) . $insertion . substr($configApp, $providersEnd);
        File::put($configAppPath, $updatedConfig);

        return 'added';
    }

    private function registerServiceConfig(string $serviceKey): string
    {
        $servicesPath = config_path('services.php');
        $servicesConfig = File::get($servicesPath);

        if (Str::contains($servicesConfig, "'{$serviceKey}' => [")) {
            return 'already present';
        }

        $insertion = "\n    '{$serviceKey}' => [\n" .
            "        'key' => env('" . Str::upper(Str::snake($serviceKey)) . "_API_KEY'),\n" .
            "        'baseUrl' => env('" . Str::upper(Str::snake($serviceKey)) . "_API_BASEURL'),\n" .
            "        'singleDataMinutes' => (int) env('SINGLE_DATA_MINUTES', 60),\n" .
            "        'multiDataMinutes' => (int) env('MULTI_DATA_MINUTES', 1440),\n" .
            "    ],\n";

        $endPosition = strrpos($servicesConfig, '];');
        if ($endPosition === false) {
            return 'failed';
        }

        $updated = substr($servicesConfig, 0, $endPosition) . $insertion . substr($servicesConfig, $endPosition);
        File::put($servicesPath, $updated);

        return 'added';
    }

    private function registerRoute(string $serviceName, string $controllerNamespace, string $routeUri): string
    {
        $routeFilePath = base_path('routes/api_v1.php');
        $routeFile = File::get($routeFilePath);

        $controllerImport = "use App\\Http\\Controllers\\Api\\{$controllerNamespace}\\{$serviceName}Controller;";
        $routeLine = "    Route::get('/{$routeUri}', [{$serviceName}Controller::class, 'index']);";

        if (Str::contains($routeFile, $routeLine)) {
            return 'already present';
        }

        $updatedRouteFile = $routeFile;
        if (! Str::contains($updatedRouteFile, $controllerImport)) {
            $updatedRouteFile = preg_replace(
                '/(use App\\\\Http\\\\Controllers\\\\Api\\\\v1\\\\[^;]+;\n)+/m',
                '$0' . $controllerImport . "\n",
                $updatedRouteFile,
                1
            ) ?? $updatedRouteFile;

            if (! Str::contains($updatedRouteFile, $controllerImport)) {
                return 'failed';
            }
        }

        $routeBlock = "\nRoute::middleware(['auth:sanctum', 'ability:gateway:read,user:admin'])->group(function () {\n{$routeLine}\n});\n";
        $updatedRouteFile .= $routeBlock;

        File::put($routeFilePath, $updatedRouteFile);

        return 'added';
    }

    private function controllerStub(string $serviceName, string $controllerNamespace): string
    {
        return <<<PHP
<?php

namespace App\Http\Controllers\Api\\{$controllerNamespace};

use App\Http\Controllers\Controller;
use App\Services\\{$serviceName}\\{$serviceName}Service;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class {$serviceName}Controller extends Controller
{
    use ApiResponses;

    public function index({$serviceName}Service \${$this->camel($serviceName)}Service, Request \$request)
    {
        return \${$this->camel($serviceName)}Service->index(\$request);
    }
}
PHP;
    }

    private function serviceStub(string $serviceName, string $serviceKey): string
    {
        return <<<PHP
<?php

namespace App\Services\\{$serviceName};

use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class {$serviceName}Service
{
    use ApiResponses;

    public function __construct(
        private readonly string \$key,
        private readonly string \$baseUrl,
        private readonly int \$singleDataMinutes,
        private readonly int \$multiDataMinutes,
    ) {
    }

    public function index(Request \$request)
    {
        return \$this->ok('Scaffold created for {$serviceName}. Customize this service implementation.', [
            'service' => '{$serviceName}',
            'serviceKey' => '{$serviceKey}',
            'query' => \$request->query(),
        ]);
    }
}
PHP;
    }

    private function providerStub(string $serviceName, string $serviceKey): string
    {
        return <<<PHP
<?php

namespace App\Providers;

use App\Services\\{$serviceName}\\{$serviceName}Service;
use Illuminate\Support\ServiceProvider;

class {$serviceName}ServiceProvider extends ServiceProvider
{
    public function register()
    {
        \$this->app->singleton(
            {$serviceName}Service::class,
            fn() => new {$serviceName}Service(
                config('services.{$serviceKey}.key'),
                config('services.{$serviceKey}.baseUrl'),
                config('services.{$serviceKey}.singleDataMinutes'),
                config('services.{$serviceKey}.multiDataMinutes'),
            )
        );
    }
}
PHP;
    }

    private function modelStub(string $modelName): string
    {
        $tableName = Str::snake(Str::pluralStudly($modelName));

        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {$modelName} extends Model
{
    use HasFactory;

    protected \$table = '{$tableName}';

    protected \$guarded = [];
}
PHP;
    }

    private function camel(string $name): string
    {
        return Str::camel($name);
    }

    private function relativePath(string $path): string
    {
        return Str::after($path, base_path() . DIRECTORY_SEPARATOR);
    }
}
