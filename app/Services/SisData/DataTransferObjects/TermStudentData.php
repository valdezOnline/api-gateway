<?php

namespace App\Services\SisData\DataTransferObjects;

use App\Services\SisData\DataTransferObjects\BaseDataTransferObject;

class TermStudentData extends BaseDataTransferObject
{
    public function __construct(
        public readonly ?string $id,// StudentId-TermCode
        public readonly ?string $studentId,
        public readonly ?string $userName, //netId,
        public readonly ?string $enrolledThisTerm,
        public readonly ?string $hasGraduated,
        public readonly ?string $effectiveTermCode,
        public readonly ?string $effectiveTermDescription,
        public readonly ?string $levelCode,
        public readonly ?string $levelDescription,
        public readonly ?string $lastTermAttendedCode,
        public readonly ?string $lastTermAttendedDescription,
        public readonly ?string $expectedGraduationDate,
        public readonly ?string $expectedGraduationTermCode,
        public readonly ?string $expectedGraduationTermDescription,
        public readonly ?string $expectedGraduationAcademicYear,
        public readonly ?string $classificationCode,
        public readonly ?string $classificationDescription,
        public readonly ?string $typeCode,
        public readonly ?string $typeDescription,
        public readonly ?string $creditHoursMax,
        public readonly ?string $creditHoursCurrent,
        public readonly ?string $creditHoursCompleted,
        public readonly ?string $gpaLevelHoursEarned,
        public readonly ?string $gpaLevelHoursAttempted,
        public readonly ?string $gpaLevelQualityPoints,
        public readonly ?string $gpaLevelGpa,
        public readonly ?string $entryTermCode,
        public readonly ?string $entryTermDescription,
        public readonly ?string $personId,
        public readonly ?string $firstName,
        public readonly ?string $lastName,
        public readonly ?string $termCode,
        public readonly ?string $termDescription,
        public readonly ?string $termStartDate,
        public readonly ?string $termEndDate,
        public readonly ?string $isCurrentTerm,
        public readonly ?string $primaryCollegeCode,
        public readonly ?string $primaryCollegeDescription,
        public readonly ?string $primaryMajorCode,
        public readonly ?string $primaryMajorDescription,
        public readonly ?string $primaryDegreeCode,
        public readonly ?string $primaryDegreeDescription,
        public readonly ?string $primaryProgramCode,
        public readonly ?string $primaryDepartmentCode,
        public readonly ?string $primaryDepartmentDescription,
        public readonly ?string $statusCode,
        public readonly ?string $statusDescription,
    ) {
    }

    /**
     * Check if the term data has the minimum required information
     */
    public function isValid(): bool
    {
        return !empty($this->studentId) && !empty($this->termCode);
    }

    /**
     * Check if student is currently enrolled
     */
    public function isEnrolled(): bool
    {
        return strtolower($this->enrolledThisTerm ?? '') === 'y' ||
            strtolower($this->enrolledThisTerm ?? '') === 'yes' ||
            strtolower($this->enrolledThisTerm ?? '') === 'true';
    }

    /**
     * Check if student has graduated
     */
    public function hasGraduated(): bool
    {
        return strtolower($this->hasGraduated ?? '') === 'y' ||
            strtolower($this->hasGraduated ?? '') === 'yes' ||
            strtolower($this->hasGraduated ?? '') === 'true';
    }

    /**
     * Check if this is current term
     */
    public function isCurrentTerm(): bool
    {
        return strtolower($this->isCurrentTerm ?? '') === 'y' ||
            strtolower($this->isCurrentTerm ?? '') === 'yes' ||
            strtolower($this->isCurrentTerm ?? '') === 'true';
    }

    /**
     * Get student ID with fallback to empty string
     */
    public function getStudentIdOrEmpty(): string
    {
        return $this->studentId ?? '';
    }

    /**
     * Get full name by combining first and last name
     */
    public function getFullName(): string
    {
        $parts = array_filter([$this->firstName, $this->lastName]);
        return implode(' ', $parts);
    }

    /**
     * Get GPA as float
     */
    public function getGpaAsFloat(): ?float
    {
        $gpa = $this->gpaLevelGpa;
        return is_numeric($gpa) ? (float) $gpa : null;
    }

    /**
     * Get credit hours as integers
     */
    public function getCreditHoursAsInts(): array
    {
        return [
            'max' => is_numeric($this->creditHoursMax) ? (int) $this->creditHoursMax : 0,
            'current' => is_numeric($this->creditHoursCurrent) ? (int) $this->creditHoursCurrent : 0,
            'completed' => is_numeric($this->creditHoursCompleted) ? (int) $this->creditHoursCompleted : 0,
        ];
    }

    /**
     * Convert to array with proper null handling
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'studentId' => $this->studentId,
            'userName' => $this->userName,
            'enrolledThisTerm' => $this->enrolledThisTerm,
            'hasGraduated' => $this->hasGraduated,
            'effectiveTermCode' => $this->effectiveTermCode,
            'effectiveTermDescription' => $this->effectiveTermDescription,
            'levelCode' => $this->levelCode,
            'levelDescription' => $this->levelDescription,
            'lastTermAttendedCode' => $this->lastTermAttendedCode,
            'lastTermAttendedDescription' => $this->lastTermAttendedDescription,
            'expectedGraduationDate' => $this->expectedGraduationDate,
            'expectedGraduationTermCode' => $this->expectedGraduationTermCode,
            'expectedGraduationTermDescription' => $this->expectedGraduationTermDescription,
            'expectedGraduationAcademicYear' => $this->expectedGraduationAcademicYear,
            'classificationCode' => $this->classificationCode,
            'classificationDescription' => $this->classificationDescription,
            'typeCode' => $this->typeCode,
            'typeDescription' => $this->typeDescription,
            'creditHoursMax' => $this->creditHoursMax,
            'creditHoursCurrent' => $this->creditHoursCurrent,
            'creditHoursCompleted' => $this->creditHoursCompleted,
            'gpaLevelHoursEarned' => $this->gpaLevelHoursEarned,
            'gpaLevelHoursAttempted' => $this->gpaLevelHoursAttempted,
            'gpaLevelQualityPoints' => $this->gpaLevelQualityPoints,
            'gpaLevelGpa' => $this->gpaLevelGpa,
            'entryTermCode' => $this->entryTermCode,
            'entryTermDescription' => $this->entryTermDescription,
            'personId' => $this->personId,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'fullName' => $this->getFullName(),
            'termCode' => $this->termCode,
            'termDescription' => $this->termDescription,
            'termStartDate' => $this->termStartDate,
            'termEndDate' => $this->termEndDate,
            'isCurrentTerm' => $this->isCurrentTerm,
            'primaryCollegeCode' => $this->primaryCollegeCode,
            'primaryCollegeDescription' => $this->primaryCollegeDescription,
            'primaryMajorCode' => $this->primaryMajorCode,
            'primaryMajorDescription' => $this->primaryMajorDescription,
            'primaryDegreeCode' => $this->primaryDegreeCode,
            'primaryDegreeDescription' => $this->primaryDegreeDescription,
            'primaryProgramCode' => $this->primaryProgramCode,
            'primaryDepartmentCode' => $this->primaryDepartmentCode,
            'primaryDepartmentDescription' => $this->primaryDepartmentDescription,
            'statusCode' => $this->statusCode,
            'statusDescription' => $this->statusDescription,
            // Helper values
            'isValid' => $this->isValid(),
            'isEnrolled' => $this->isEnrolled(),
            'hasGraduatedBool' => $this->hasGraduated(),
            'isCurrentTermBool' => $this->isCurrentTerm(),
            'gpaFloat' => $this->getGpaAsFloat(),
            'creditHours' => $this->getCreditHoursAsInts(),
        ];
    }

    public static function fromArray(array $data): static
    {
        // Handle case where data is null or empty
        if (empty($data)) {
            return new self(
                id: null,
                studentId: null,
                userName: null,
                enrolledThisTerm: null,
                hasGraduated: null,
                effectiveTermCode: null,
                effectiveTermDescription: null,
                levelCode: null,
                levelDescription: null,
                lastTermAttendedCode: null,
                lastTermAttendedDescription: null,
                expectedGraduationDate: null,
                expectedGraduationTermCode: null,
                expectedGraduationTermDescription: null,
                expectedGraduationAcademicYear: null,
                classificationCode: null,
                classificationDescription: null,
                typeCode: null,
                typeDescription: null,
                creditHoursMax: null,
                creditHoursCurrent: null,
                creditHoursCompleted: null,
                gpaLevelHoursEarned: null,
                gpaLevelHoursAttempted: null,
                gpaLevelQualityPoints: null,
                gpaLevelGpa: null,
                entryTermCode: null,
                entryTermDescription: null,
                personId: null,
                firstName: null,
                lastName: null,
                termCode: null,
                termDescription: null,
                termStartDate: null,
                termEndDate: null,
                isCurrentTerm: null,
                primaryCollegeCode: null,
                primaryCollegeDescription: null,
                primaryMajorCode: null,
                primaryMajorDescription: null,
                primaryDegreeCode: null,
                primaryDegreeDescription: null,
                primaryProgramCode: null,
                primaryDepartmentCode: null,
                primaryDepartmentDescription: null,
                statusCode: null,
                statusDescription: null
            );
        }

        return new self(
            id: self::getNonEmptyStringOrNull($data, 'id'),
            studentId: self::getNonEmptyStringOrNull($data, 'studentId'),
            userName: self::getNonEmptyStringOrNull($data, 'userName'),
            enrolledThisTerm: self::getNonEmptyStringOrNull($data, 'enrolledThisTerm'),
            hasGraduated: self::getNonEmptyStringOrNull($data, 'hasGraduated'),
            effectiveTermCode: self::getNonEmptyStringOrNull($data, 'effectiveTerm.code'),
            effectiveTermDescription: self::getNonEmptyStringOrNull($data, 'effectiveTerm.description'),
            levelCode: self::getNonEmptyStringOrNull($data, 'level.code'),
            levelDescription: self::getNonEmptyStringOrNull($data, 'level.description'),
            lastTermAttendedCode: self::getNonEmptyStringOrNull($data, 'lastTermAttended.code'),
            lastTermAttendedDescription: self::getNonEmptyStringOrNull($data, 'lastTermAttended.description'),
            expectedGraduationDate: self::getNonEmptyStringOrNull($data, 'expectedGraduation.date'),
            expectedGraduationTermCode: self::getNonEmptyStringOrNull($data, 'expectedGraduation.term.code'),
            expectedGraduationTermDescription: self::getNonEmptyStringOrNull($data, 'expectedGraduation.term.description'),
            expectedGraduationAcademicYear: self::getNonEmptyStringOrNull($data, 'expectedGraduation.academicYear'),
            classificationCode: self::getNonEmptyStringOrNull($data, 'classification.code'),
            classificationDescription: self::getNonEmptyStringOrNull($data, 'classification.description'),
            typeCode: self::getNonEmptyStringOrNull($data, 'type.code'),
            typeDescription: self::getNonEmptyStringOrNull($data, 'type.description'),
            creditHoursMax: self::getNumericStringOrNull($data, 'creditHours.max'),
            creditHoursCurrent: self::getNumericStringOrNull($data, 'creditHours.current'),
            creditHoursCompleted: self::getNumericStringOrNull($data, 'gpa.level.hoursEarned'),
            gpaLevelHoursEarned: self::getNumericStringOrNull($data, 'gpa.level.hoursEarned'),
            gpaLevelHoursAttempted: self::getNumericStringOrNull($data, 'gpa.level.hoursAttempted'),
            gpaLevelQualityPoints: self::getNumericStringOrNull($data, 'gpa.level.qualityPoints'),
            gpaLevelGpa: self::getNumericStringOrNull($data, 'gpa.level.gpa'),
            entryTermCode: self::getNonEmptyStringOrNull($data, 'entry.term.code'),
            entryTermDescription: self::getNonEmptyStringOrNull($data, 'entry.term.description'),
            personId: self::getNonEmptyStringOrNull($data, 'person.id'),
            firstName: self::getNonEmptyStringOrNull($data, 'name.firstName'),
            lastName: self::getNonEmptyStringOrNull($data, 'name.lastName'),
            termCode: self::getNonEmptyStringOrNull($data, 'term.code'),
            termDescription: self::getNonEmptyStringOrNull($data, 'term.description'),
            termStartDate: self::getNonEmptyStringOrNull($data, 'term.startDate'),
            termEndDate: self::getNonEmptyStringOrNull($data, 'term.endDate'),
            isCurrentTerm: self::getNonEmptyStringOrNull($data, 'term.isCurrentTerm'),
            primaryCollegeCode: self::getNonEmptyStringOrNull($data, 'primary.college.code'),
            primaryCollegeDescription: self::getNonEmptyStringOrNull($data, 'primary.college.description'),
            primaryMajorCode: self::getNonEmptyStringOrNull($data, 'primary.major.code'),
            primaryMajorDescription: self::getNonEmptyStringOrNull($data, 'primary.major.description'),
            primaryDegreeCode: self::getNonEmptyStringOrNull($data, 'primary.degree.code'),
            primaryDegreeDescription: self::getNonEmptyStringOrNull($data, 'primary.degree.description'),
            primaryProgramCode: self::getNonEmptyStringOrNull($data, 'primary.program.code'),
            primaryDepartmentCode: self::getNonEmptyStringOrNull($data, 'primary.department.code'),
            primaryDepartmentDescription: self::getNonEmptyStringOrNull($data, 'primary.department.description'),
            statusCode: self::getNonEmptyStringOrNull($data, 'status.code'),
            statusDescription: self::getNonEmptyStringOrNull($data, 'status.description'),
        );
    }
}