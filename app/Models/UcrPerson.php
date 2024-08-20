<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nette\Schema\Elements\Type;

class UcrPerson extends Model
{
    use HasFactory;

    public function __construct(Type $var = string) {
        $this->var = $var;
        
    }
    protected $fillable = [
        'netId',
        'displayName',
        'emailAddress',
        'employeeId',
        'firstName',
        'middleName',
        'lastName',
        'preferredFirstName',
        'preferredMiddleName',
        'preferredLastName',
        'phoneNumber',
        'officePhoneNumber',
        'eduPersonPrimaryAffiliation',
        'eduPersonAffilation',
        'ucrOrg',
        'ou',
        'title',
        'homeDepartmentCode',
        'homeDepartment',
        'ucrUniversityId', //StudentId
        'ucrEnrollmentStatus',
        'ucrCollege',
        'ucrClassStanding',
        'ucrLastRegistered',
        'udcIdentifier',
        'titleCode',
        'separationDate',
        'privacyCode',
        'isActive',
        'organizationalStatus',
        'facultyStatus',
        'ucrEntryExpirationDate',
    ];
}