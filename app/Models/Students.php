<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StudentScope;

class Students extends Model
{
    use HasFactory;

    protected $table = 'students';

    protected static $publicUsernameSalutations = [
        'mr', 'mrs', 'ms', 'miss', 'dr', 'prof', 'master', 'mst', 'shri', 'smt', 'kumari', 'er', 'adv', 'mx',
        'jr', 'sr', 'ii', 'iii', 'iv', 'esq', 'phd', 'md', 'dds',
    ];

    protected static function booted()
    {
        /**
         * STUDENT SCOPE TO THE SCHOOL
         */
        static::addGlobalScope(new StudentScope);

        static::creating(function (Students $student) {
            if (empty($student->public_username) && !empty($student->school_id) && !empty($student->name)) {
                $student->public_username = static::generatePublicUsername($student->name, $student->school_id);
            }
        });
    }

    /**
     * Build a unique public_username for a school from a student's name: salutations
     * stripped, lowercased, words concatenated into one alphanumeric username, and
     * deduped globally with a trailing count (swatigauba, swatigauba1, ...).
     */
    public static function generatePublicUsername(string $name, $schoolId = null, $excludeStudentId = null): string
    {
        $base = static::basePublicUsername($name);

        $query = static::withoutGlobalScopes()->whereNotNull('public_username');

        if ($excludeStudentId) {
            $query->where('id', '!=', $excludeStudentId);
        }

        $usedUsernames = $query->pluck('public_username')->flip();

        if (!$usedUsernames->has($base)) {
            return $base;
        }

        $suffix = 1;
        while ($usedUsernames->has("{$base}{$suffix}")) {
            $suffix++;
        }

        return "{$base}{$suffix}";
    }

    public static function basePublicUsername(?string $fullName): string
    {
        $name = preg_replace('/[^A-Za-z0-9\s.]/', ' ', trim((string) $fullName));
        $name = str_replace('.', ' ', $name);
        $words = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY);

        while (!empty($words) && in_array(strtolower($words[0]), static::$publicUsernameSalutations, true)) {
            array_shift($words);
        }
        while (!empty($words) && in_array(strtolower(end($words)), static::$publicUsernameSalutations, true)) {
            array_pop($words);
        }

        return empty($words) ? 'student' : strtolower(implode('', $words));
    }

    public function stdUser()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }

    public function level()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function submission()
    {
        return $this->hasMany(Submission::class, 'student_id', 'id');
    }

    public function assignmentfiles()
    {
        return $this->hasMany(AssingmentFiles::class, 'assignment_id', 'assignment');
    }

    public function getproject()
    {
        return $this->hasMany(Project::class, 'student_id', 'id');
    }

    public function projectfiles()
    {
        return $this->hasMany(Projectfiles::class, 'student_id', 'id');
    }

    public function getgrades()
    {
        return $this->hasMany(Grade::class, 'id', 'grade_id');
    }

    public function studentcomminucate()
    {
        return $this->hasMany(StudentFeedback::class, 'student_id');
    }

    public function notifications()
    {
        return $this->hasMany(StudentNotification::class, 'student_id');
    }

    public function getAssignedGrade()
    {
        return $this->belongsTo(StudentGrade::class, 'student_grade_id');
    }

    public function getAssignedBatch()
    {
        return $this->belongsTo(SchoolBatch::class, 'school_batch_id');
    }

    public function affirmationViews()
    {
        return $this->hasMany(ViewTracking::class);
    }

     public function country()
    {
       return $this->belongsTo(Country::class, 'country_id');
    }

    public function certificates()
    {
        return $this->hasMany(StudentCertificates::class, 'student_id');
    }
}
