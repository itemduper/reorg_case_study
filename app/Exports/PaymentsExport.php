<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PaymentsExport implements FromCollection, ShouldAutoSize
{
    /**
     * Payments to export
     *
     * @var Collection
     */
    protected $payments;

    /**
     * Construct an export of Payments
     *
     * @param Collection $payments
     */
    function __construct(Collection $payments) {
        $this->payments = $payments;
    }

    /**
     * Generates a collection of Payments to export
     *
     * @return Collection
     */
    public function collection()
    {
        $output = [];

        if(isset($this->payments[0])) {
            // Build Header Row
            $header = [];
            foreach($this->payments[0]->toRecord() as $key => $val) {
                $header[$key] = $key;
            };
    
            // Build Payment Rows and establish which columns are in use.
            $columns_in_use = [];
            foreach($this->payments as $payment) {
                $row = $payment->toRecord();
                foreach($row as $key => $val) {
                    if(!isset($columns_in_use[$key])) $columns_in_use[$key] = false;
                    if($val != null) {
                        $columns_in_use[$key] = true;
                    }
                }
    
                $output[] = $row;
            }

            // Merge in Header Row
            $output = array_merge([$header], $output);
            
            // Unset attributes that are not in use.
            foreach($columns_in_use as $col_name => $in_use) {
                foreach($output as $key => $val) {
                    if($in_use != true) {
                        unset($output[$key][$col_name]);
                    }
                }
            }
        }
        
        return collect($output);
    }
}