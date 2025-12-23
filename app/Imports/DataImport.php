<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class DataImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        //
        // $rows = [];
        // foreach ($collection as $row)
        // {
        //     if ($row[1] != '' && is_numeric($row[1])) {
        //         $rows[] = $row[1];
        //     }
        // }
        // dd( $rows);
    }
}
