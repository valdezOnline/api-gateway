<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdmsCardDataStaging extends Model
{
    use HasFactory;

    protected $table = 'idms_card_data_staging';

    protected $guarded = [];

    protected $casts = [
        'issue_date' => 'date',
        'edit_date' => 'date',
        'photo_date' => 'date',
        'imported_date' => 'date',
    ];
}
