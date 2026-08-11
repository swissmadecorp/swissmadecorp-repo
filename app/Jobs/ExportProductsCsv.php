<?php

namespace App\Jobs;

use App\Exports\ProductsCsvExport as ProductsCsvExporter;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;

class ExportProductsCsv
{
    public function __construct(
        private readonly string $path = 'public/uploads/products.csv'
    ) {}

    public function handle(): void
    {
        $path = $this->outputPath();

        File::ensureDirectoryExists(dirname($path));

        $csv = Excel::raw(new ProductsCsvExporter, ExcelWriter::CSV);
        File::replace($path, $csv);
    }

    private function outputPath(): string
    {
        if (preg_match('/^(?:[A-Za-z]:[\\\\\/]|[\\\\\/])/', $this->path) === 1) {
            return $this->path;
        }

        return base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->path));
    }
}
