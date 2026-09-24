<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Ramsey\Uuid\Uuid;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use App\Models\Domain;
use App\Models\SchoolAcademicYear;
use App\Scopes\TenantScope;

class School extends BaseTenant
{
    use HasDatabase, HasDomains;
    protected $table = 'schools';
    protected $primarykey = 'id';
    protected $fillable = ['id', 'created_by', 'created_type', 'school_name', 'city', 'principle_name', 'official_email_id', 'number_of_student', 'country_id', 'school_address', 'year_establish', 'incharge_name', 'incharge_email', 'contact_number', 'venturekids_representative', 'course_start_date', 'status', 'weekly _class_for_grade', 'membership_plan', 'school_logo', 'school_cover_image', 'logout_redirect_url', 'standard_assessment_assigned', 'standard_assessment_enabled', 'standard_assessment_enabled_from', 'standard_assessment_enabled_to'];
    // public $incrementing = true; // Indicates the primary key is auto-incrementing

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($school) {

            // AUTOMATICALLY SET TENANT_ID IF NOT SET
            // @TODO - do we need to validate generated string ?
            $school->tenant_id = Uuid::uuid4()->toString();

        });
    }

    protected static function booted()
    {
        /**
         * IF SCHOOL AS TENANT FOUND THEN ADD SCOPE
         */
        static::addGlobalScope(new TenantScope);
    }

    public static function getCustomColumns(): array
    {
        return [
            "id", "created_by", "created_type", "user_id", "school_name", "city", "principle_name", "official_email_id", "contact_number", "number_of_student", "country_id", "membership_plan", "school_address", "year_establish", "incharge_name", "incharge_email", "currency_type", "fee_per_student", "venturekids_representative", "course_start_date", "course_end_date", "entrepreneurship_lab", "school_logo", "school_cover_image", "weekly_class_for_grade", "status", "tenant_id", "logout_redirect_url", "standard_assessment_assigned", "standard_assessment_enabled", "standard_assessment_enabled_from", "standard_assessment_enabled_to"
        ];
    }

    // Implementing Tenant interface methods
    public function getTenantKeyName(): string
    {
        return 'tenant_id';
    }

    public function getTenantKey()
    {
        return $this->tenant_id;
    }

    public function getInternal(string $key)
    {
        // Implement the logic to get the internal value based on your application's requirements
        return $this->getAttribute($key);
    }

    public function setInternal(string $key, $value)
    {
        // Implement the logic to set the internal value based on your application's requirements
        $this->setAttribute($key, $value);
    }

    public function run(callable $callback)
    {
        // Implement the logic to run a callback in this tenant's environment
        return $callback($this);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function ClassSchedule()
    {
        return $this->hasMany(ClassSchedule::class, 'school_id', 'id');
    }

    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class, 'school_id', 'id');
    }

    public function students()
    {
        return $this->hasMany(Students::class, 'school_id');
    }

    public function country()
    {
        return $this->hasOne(Country::class, 'id');
    }

     public function countrynew()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function batches()
    {
        return $this->hasMany(SchoolBatch::class, 'school_id');
    }

    public function academicYears()
    {
        return $this->hasMany(SchoolAcademicYear::class, 'school_id', 'id');
    }

    public function domains()
    {
        return $this->hasOne(Domain::class, 'tenant_id', 'tenant_id');
    }

    public function getNextId()
    {
        return ($this->max('id') + 1);
    }
}

