<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use SplFileObject;

class CsvImportBooksNormalized extends Command
{
    protected $signature = 'csv:import {path : Path to the CSV file}';
    protected $description = 'Import data from a CSV file into the normalized books table';

    public function handle()
    {
        ini_set('memory_limit', '-1');
        $path = $this->argument('path');
        $file = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV);

        $header = null;
        $data = [];
        $i = 0;

        foreach ($file as $row) {
            if ($i >= 300) {
                DB::table('books_normalized')->insert($data);
                $this->info('CSV batch imported.');
                $data = [];
                $i = 0;
            }
            if (!$header) {
                $header = $row;
            } else {
                if(count($header) == count($row))
                $data[] = array_combine($header, $row);
            }
            $i++;
            $this->info($i);
        }
        $this->info('CSV data imported successfully.');
    }
}
