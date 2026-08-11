<?php

namespace App\Jobs;

use App\Exports\ProductsCsvExport as ProductsCsvExporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ExportProductsCsv extends Command
{
    protected $signature = 'products:export-csv
        {--path=public/uploads/products.csv : Output path, relative to the application root}';

    protected $description = 'Export available products to the marketplace CSV feed';

    public function handle(): int
    {
        $path = $this->outputPath((string) $this->option('path'));

        try {
            File::ensureDirectoryExists(dirname($path));

            $csv = Excel::raw(new ProductsCsvExporter, ExcelWriter::CSV);
            File::replace($path, $csv);
        } catch (Throwable $exception) {
            report($exception);
            $this->error('The products CSV could not be created: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Products CSV created at '.$path);

        return self::SUCCESS;
    }

    private function outputPath(string $path): string
    {
        if (preg_match('/^(?:[A-Za-z]:[\\\\\/]|[\\\\\/])/', $path) === 1) {
            return $path;
        }

        return base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path));
    }
}
