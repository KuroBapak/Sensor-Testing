<?php

namespace App\Http\Controllers;

use App\Imports\BatchAddImporter;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** "Contoh Batch Add": the example workbook with instructions. */
class BatchAddExampleController extends Controller
{
    public function __invoke(): BinaryFileResponse
    {
        return Excel::download(BatchAddImporter::example(), 'contoh-batch-add.xlsx');
    }
}
