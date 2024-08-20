<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SisActiveStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'personGuid',
        'principalId',
        'principalName',
        'affiliationType',
        'campus',
        'nameCode',
        'firstName',
        'middleName',
        'lastName',
        'nameDefault',
        'nameActive',
        'phoneType',
        'phoneNumber',
        'phoneDefault',
        'phoneActive',
        'emailType',
        'emailAddress',
        'emailDefault',
        'emailActive',
        'addressTypeCode',
        'addressLine1',
        'addressLine2',
        'addressLine3',
        'city',
        'stateOrProvince',
        'postalCode',
        'country',
        'addressDefault',
        'addressActive',        
    ];
}