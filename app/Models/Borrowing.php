<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Borrowing extends Model
{
    protected $casts = [
        "is_returned" => "boolean",
    ];

    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function book(){
        return $this->belongsTo(Book::class);
    }
}
