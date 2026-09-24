<?php

namespace App\Services;

use App\Events\Backend\UserCreated;
use App\Mail\CreatedSchoolMail;
use App\Models\Domain;
use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SchoolOnboardingService
{
    /**
     * Create a live school using the same onboarding steps as the admin form.
     *
     * @param array $payload
     * @param string|null $password
     * @param bool $sendSchoolWelcomeMail
     * @return array
     * @throws \Throwable
     */
    public function createSchool(array $payload, ?string $password = null, bool $sendSchoolWelcomeMail = true): array
    {
        return DB::transaction(function () use ($payload, $password, $sendSchoolWelcomeMail) {
            $fullDomain = null;
            $password = $password ?: Str::random(10);
            $countryId = $payload['country'];

            if (!empty($payload['school_domain'])) {
                $fullDomain = trim((string) $payload['school_domain']) . config('tenancy.sub_domain');
                if (Domain::where('domain', $fullDomain)->exists()) {
                    throw new \RuntimeException('This school domain is already in use.');
                }
            }

            $user = User::where('email', $payload['email'])->first();

            if (!empty($user)) {
                throw new \RuntimeException('Sorry Email Already Exits.');
            }

            // Explicit whitelist rather than Arr::except($payload, ...): Eloquent's
            // fillable filtering normally strips the school-only keys (school_name,
            // city, course_start_date, ...) before they reach the users table, but
            // that filtering is disabled while running inside `php artisan db:seed`
            // (Laravel wraps seeders in Model::unguarded()), which let those keys
            // reach the INSERT and fail with "Unknown column". Building the array
            // explicitly makes this correct regardless of guard state.
            $dataArray = [
                'email' => $payload['email'],
                'name' => $payload['school_name'],
                'group' => 2,
                'password' => Hash::make($password),
                'country_id' => $countryId,
            ];

            if (($payload['confirmed'] ?? null) == 1) {
                $dataArray = Arr::add($dataArray, 'email_verified_at', Carbon::now());
            } else {
                $dataArray = Arr::add($dataArray, 'email_verified_at', null);
            }

            $user = User::create($dataArray);

            $roles = Role::where('id', 7)->pluck('name')->toArray();
            $permissions = Permission::whereIn('id', [1, 40])->pluck('name')->toArray();

            if (!empty($roles)) {
                $user->syncRoles($roles);
            }

            if (!empty($permissions)) {
                $user->syncPermissions($permissions);
            }

            $user->username = config('app.initial_username') + $user->id;
            $user->save();

            safeEventAction('school onboarding user created', [
                'user_id' => $user->id,
                'email' => $user->email ?? null,
            ], function () use ($user) {
                event(new UserCreated($user));
            });

            $school = new School();
            $school->id = $school->getNextId();
            $school->created_by = $payload['created_by'] ?? auth()->id();
            $school->created_type = $payload['created_type'] ?? 'admin';
            $school->user_id = $user->id;
            $school->school_name = $payload['school_name'];
            $school->principle_name = $payload['principle_name'];
            $school->official_email_id = $payload['email'];
            $school->contact_number = $payload['contact_number'] ?? '';
            $school->country_id = $countryId;
            $school->city = $payload['city'];
            $school->currency_type = $payload['currency_type'];
            $school->fee_per_student = $payload['fee_per_student'] ?? 0;
            $school->number_of_student = $payload['number_of_student'];
            $school->course_start_date = $payload['course_start_date'];
            $school->course_end_date = $payload['course_end_date'];
            $school->logout_redirect_url = $payload['logout_redirect_url'] ?? null;
            $school->save();

            if (!empty($payload['school_domain'])) {
                $domainPrefix = trim((string) $payload['school_domain']);
                if ($domainPrefix !== '') {
                    Domain::create([
                        'domain' => $fullDomain,
                        'tenant_id' => $school->tenant_id,
                    ]);
                }
            }

            $batches = $payload['batch_name'] ?? [];
            if (!empty($batches)) {
                foreach ($batches as $batch) {
                    if (!empty($batch)) {
                        $batchInsert = new SchoolBatch();
                        $batchInsert->school_id = $school->id;
                        $batchInsert->batch_name = $batch;
                        $batchInsert->save();
                    }
                }
            } else {
                $batchInsert = new SchoolBatch();
                $batchInsert->school_id = $school->id;
                $batchInsert->batch_name = 'Batch 1';
                $batchInsert->save();
            }

            clear_cache_manually();

            if ($sendSchoolWelcomeMail) {
                safeMailAction('school onboarding welcome mail', [
                    'school_id' => $school->id,
                    'recipient' => $school->official_email_id,
                    'school_name' => $school->school_name,
                ], function () use ($school, $payload, $password, $fullDomain) {
                    Mail::to($school->official_email_id)
                        ->bcc(env('MAIL_BCC'))
                        ->send(new CreatedSchoolMail(
                            $school->principle_name,
                            $school->school_name,
                            $payload['email'],
                            $password,
                            date('d-m-Y', strtotime($payload['course_start_date'])),
                            date('d-m-Y', strtotime($payload['course_end_date'])),
                            $fullDomain
                        ));
                });
            }

            return [
                'user' => $user,
                'school' => $school,
                'password' => $password,
                'full_domain' => $fullDomain,
            ];
        });
    }
}
