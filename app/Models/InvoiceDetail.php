<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    protected $fillable = ['invoice_id', 'invoice_number', 'spare', 'qty', 'price', 'row_sub_total'];

    protected $casts = [
        'price' => 'decimal:2',
        'row_sub_total' => 'decimal:2',
    ];
}
