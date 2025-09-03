<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use ReflectionClass;

class DisplayDeltaAnalysis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:display-delta-analysis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display the delta analysis result';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $job = new \App\Jobs\MergeCardDataDeltas();

        $reflection = new ReflectionClass($job);
        $method = $reflection->getMethod('analyzeTableDeltas');
        $method->setAccessible(true);

        $result = $method->invoke($job);

        $this->info('Delta Analysis Results:');
        $this->line(json_encode($result, JSON_PRETTY_PRINT));
    }
}
