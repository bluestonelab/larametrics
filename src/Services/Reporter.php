<?php

namespace Bluestone\Larametrics\Services;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Bluestone\Larametrics\Exceptions\ReporterException;

class Reporter
{
    public function getReport(string $repositoryPath): string
    {
        $binary = $this->getPhpmetricsBinaryPath();

        $reportPath = $this->getTemporaryReportPath();

        $command = sprintf('%s --report-json=%s %s', $binary, Storage::path($reportPath), $repositoryPath);

        $process = Process::run($command);

        if ($process->failed()) {
            throw new ReporterException($process->errorOutput());
        }

        $report = Storage::get($reportPath);

        Storage::delete($reportPath);

        return $report;
    }

    protected function getPhpmetricsBinaryPath(): string
    {
        return base_path('/vendor/bin/phpmetrics');
    }

    protected function getTemporaryReportPath(): string
    {
        return Str::random() . '.json';
    }
}
