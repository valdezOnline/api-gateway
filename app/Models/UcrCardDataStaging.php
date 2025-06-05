<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UcrCardDataStaging extends Model
{
    use HasFactory;

    protected $table = 'ucr_card_data_staging';
    protected $guarded = [];
}
