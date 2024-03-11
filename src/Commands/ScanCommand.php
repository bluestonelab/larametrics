<?php

namespace Bluestone\Larametrics\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Bluestone\Larametrics\Services\Reporter;
use function Laravel\Prompts\table;

class ScanCommand extends Command
{
    protected const DEFAULT_NAMESPACES = ['App'];
    protected const DEFAULT_METRICS = ['ccn', 'lloc', 'lcom', 'afferentCoupling', 'efferentCoupling'];

    public $signature = 'larametrics:scan {--namespace=*} {--metric=*}';

    public $description = 'Show phpmetrics report on cli';

    public function __construct(protected readonly Reporter $reporter)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $classesMetrics = $this->getClassesMetrics();

        $namespaces = $this->option('namespace') ?: self::DEFAULT_NAMESPACES;

        $metrics = [];

        foreach ($namespaces as $namespace) {
            $metrics[] = ['namespace' => $namespace] + $this->getMetricsByNamespace($classesMetrics, $namespace);
        }

        $headers = array_merge(['namespace'], ($this->option('metric') ?: self::DEFAULT_METRICS));

        table(
            $headers,
            $metrics
        );

        return Command::SUCCESS;
    }

    protected function getClassesMetrics(): Collection
    {
        $report = $this->reporter->getReport(base_path());

        return collect(json_decode($report, associative: true))
            ->filter(fn (array $class) => $class['_type'] === 'Hal\Metric\ClassMetric');
    }

    private function getMetricsByNamespace(Collection $report, string $namespace): array
    {
        $classesInNamespace = $report->filter(function (array $class) use ($namespace) {
            return Str::startsWith($class['name'], $namespace);
        });

        $metrics = $this->option('metric') ?: self::DEFAULT_METRICS;

        $metrics = array_flip($metrics);

        foreach ($metrics as $metric => $value) {
            $value = $classesInNamespace->average(function (array $class) use ($metric) {
                return $class[$metric];
            });

            $metrics[$metric] = number_format($value, 2);
        }

        return $metrics;
    }
}
