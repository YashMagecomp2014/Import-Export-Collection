<?php
namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

class UsersImport implements ToCollection, WithHeadingRow
{

    public function __construct()
    {
        HeadingRowFormatter::default('custom');
    }
    public function collection(Collection $rows)
    {
       

    }

    public function headings(): array
    {
        return [
            'title',
            'Description',
            'Conditions',
            'Products',
            'Products must match',
            'Sort Order',
            'Template Suffix',
            'Published',
            'SEO Title',
            'SEO Description',
            'Collection Image',
        ];
    }
}
