<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Stancl\Tenancy\Facades\Tenancy;
use Illuminate\Support\Facades\File;

/*
 * Global helpers file with misc functions.
 */
if (!function_exists('asset_v')) {
    /**
     * asset() with a filemtime-based cache-busting query string, so
     * browsers pick up edited static files (e.g. brand logos) instead
     * of serving a stale cached copy indefinitely.
     */
    function asset_v($path)
    {
        $absolute = public_path($path);
        $version = File::exists($absolute) ? filemtime($absolute) : '1';

        return asset($path) . '?v=' . $version;
    }
}

if (!function_exists('app_name')) {
    /**
     * Helper to grab the application name.
     *
     * @return mixed
     */
    function app_name()
    {
        return config('app.name');
    }
}

/*
 * Global helpers file with misc functions.
 */
if (!function_exists('user_registration')) {
    /**
     * Helper to grab the application name.
     *
     * @return mixed
     */
    function user_registration()
    {
        $user_registration = false;

        if (env('USER_REGISTRATION') == 'true') {
            $user_registration = true;
        }

        return $user_registration;
    }
}

/*
 *
 * label_case
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('label_case')) {

    /**
     * Prepare the Column Name for Lables.
     */
    function label_case($text)
    {
        $order = ['_', '-'];
        $replace = ' ';

        $new_text = trim(\Illuminate\Support\Str::title(str_replace('"', '', $text)));
        $new_text = trim(\Illuminate\Support\Str::title(str_replace($order, $replace, $text)));
        $new_text = preg_replace('!\s+!', ' ', $new_text);

        return $new_text;
    }
}

/*
 *
 * show_column_value
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('show_column_value')) {
    /**
     * Return Column values as Raw and formatted.
     *
     * @param string $valueObject   Model Object
     * @param string $column        Column Name
     * @param string $return_format Return Type
     *
     * @return string Raw/Formatted Column Value
     */
    function show_column_value($valueObject, $column, $return_format = '')
    {
        $column_name = $column->Field;
        $column_type = $column->Type;

        $value = $valueObject->$column_name;

        if ($return_format == 'raw') {
            return $value;
        }

        if (($column_type == 'date') && $value != '') {
            $datetime = \Carbon\Carbon::parse($value);

            return $datetime->isoFormat('LL');
        } elseif (($column_type == 'datetime' || $column_type == 'timestamp') && $value != '') {
            $datetime = \Carbon\Carbon::parse($value);

            return $datetime->isoFormat('LLLL');
        } elseif ($column_type == 'json') {
            $return_text = json_encode($value);
        } elseif ($column_type != 'json' && \Illuminate\Support\Str::endsWith(strtolower($value), ['png', 'jpg', 'jpeg', 'gif', 'svg'])) {
            $img_path = asset($value);

            $return_text = '<figure class="figure">
                                <a href="'.$img_path.'" data-lightbox="image-set" data-title="Path: '.$value.'">
                                    <img src="'.$img_path.'" style="max-width:200px;" class="figure-img img-fluid rounded img-thumbnail" alt="">
                                </a>
                                <figcaption class="figure-caption">Path: '.$value.'</figcaption>
                            </figure>';
        } else {
            $return_text = $value;
        }

        return $return_text;
    }
}

/*
 *
 * fielf_required
 * Show a * if field is required
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('fielf_required')) {

    /**
     * Prepare the Column Name for Lables.
     */
    function fielf_required($required)
    {
        $return_text = '';

        if ($required != '') {
            $return_text = '<span class="text-danger">*</span>';
        }

        return $return_text;
    }
}

/*
 * Get or Set the Settings Values
 *
 * @var [type]
 */
if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        if (is_null($key)) {
            return new App\Models\Setting();
        }

        if (is_array($key)) {
            return App\Models\Setting::set($key[0], $key[1]);
        }

        $value = App\Models\Setting::get($key);

        return is_null($value) ? value($default) : $value;
    }
}

/*
 * Show Human readable file size
 *
 * @var [type]
 */
if (!function_exists('humanFilesize')) {
    function humanFilesize($size, $precision = 2)
    {
        $units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $step = 1024;
        $i = 0;

        while (($size / $step) > 0.9) {
            $size = $size / $step;
            $i++;
        }

        return round($size, $precision).$units[$i];
    }
}

/*
 *
 * Encode Id to a Hashids\Hashids
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('encode_id')) {

    /**
     * Prepare the Column Name for Lables.
     */
    function encode_id($id)
    {
        $hashids = new Hashids\Hashids(config('app.salt'), 3, 'abcdefghijklmnopqrstuvwxyz1234567890');
        $hashid = $hashids->encode($id);

        return $hashid;
    }
}

/*
 *
 * Decode Id to a Hashids\Hashids
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('decode_id')) {

    /**
     * Prepare the Column Name for Lables.
     */
    function decode_id($hashid)
    {
        $hashids = new Hashids\Hashids(config('app.salt'), 3, 'abcdefghijklmnopqrstuvwxyz1234567890');
        $id = $hashids->decode($hashid);

        if (count($id)) {
            return $id[0];
        } else {
            abort(404);
        }
    }
}

/*
 *
 * Prepare a Slug for a given string
 * Laravel default str_slug does not work for Unicode
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('slug_format')) {

    /**
     * Format a string to Slug.
     */
    function slug_format($string)
    {
        $base_string = $string;

        $string = preg_replace('/\s+/u', '-', trim($string));
        $string = str_replace('/', '-', $string);
        $string = str_replace('\\', '-', $string);
        $string = strtolower($string);

        $slug_string = $string;

        return $slug_string;
    }
}

/*
 *
 * icon
 * A short and easy way to show icon fornts
 * Default value will be check icon from FontAwesome
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('icon')) {

    /**
     * Format a string to Slug.
     */
    function icon($string = 'fas fa-check')
    {
        $return_string = "<i class='".$string."'></i>";

        return $return_string;
    }
}

/*
 *
 * logUserAccess
 * Get current user's `name` and `id` and
 * log as debug data. Additional text can be added too.
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('logUserAccess')) {

    /**
     * Format a string to Slug.
     */
    function logUserAccess($text = '')
    {
        $auth_text = '';

        if (\Auth::check()) {
            $auth_text = 'User:'.\Auth::user()->name.' (ID:'.\Auth::user()->id.')';
        }

        \Log::debug(label_case($text)." | $auth_text");
    }
}

/*
 *
 * bn2enNumber
 * Convert a Bengali number to English
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('bn2enNumber')) {

    /**
     * Prepare the Column Name for Lables.
     */
    function bn2enNumber($number)
    {
        $search_array = ['১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '০'];
        $replace_array = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];

        $en_number = str_replace($search_array, $replace_array, $number);

        return $en_number;
    }
}

/*
 *
 * bn2enNumber
 * Convert a English number to Bengali
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('en2bnNumber')) {

    /**
     * Prepare the Column Name for Lables.
     */
    function en2bnNumber($number)
    {
        $search_array = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
        $replace_array = ['১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '০'];

        $bn_number = str_replace($search_array, $replace_array, $number);

        return $bn_number;
    }
}

/*
 *
 * bn2enNumber
 * Convert a English number to Bengali
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('en2bnDate')) {

    /**
     * Convert a English number to Bengali.
     */
    function en2bnDate($date)
    {
        // Convert numbers
        $search_array = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
        $replace_array = ['১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '০'];
        $bn_date = str_replace($search_array, $replace_array, $date);

        // Convert Short Week Day Names
        $search_array = ['Fri', 'Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu'];
        $replace_array = ['শুক্র', 'শনি', 'রবি', 'সোম', 'মঙ্গল', 'বুধ', 'বৃহঃ'];
        $bn_date = str_replace($search_array, $replace_array, $bn_date);

        // Convert Month Names
        $search_array = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $replace_array = ['জানুয়ারী', 'ফেব্রুয়ারী', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগষ্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];
        $bn_date = str_replace($search_array, $replace_array, $bn_date);

        // Convert Short Month Names
        $search_array = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $replace_array = ['জানুয়ারী', 'ফেব্রুয়ারী', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগষ্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];
        $bn_date = str_replace($search_array, $replace_array, $bn_date);

        // Convert AM-PM
        $search_array = ['am', 'pm', 'AM', 'PM'];
        $replace_array = ['পূর্বাহ্ন', 'অপরাহ্ন', 'পূর্বাহ্ন', 'অপরাহ্ন'];
        $bn_date = str_replace($search_array, $replace_array, $bn_date);

        return $bn_date;
    }
}

/*
 *
 * banglaDate
 * Get the Date of Bengali Calendar from the Gregorian Calendar
 * By default is will return the Today's Date
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('banglaDate')) {
    function banglaDate($date_input = '')
    {
        if ($date_input == '') {
            $date_input = date('Y-m-d');
        }

        $date_input = strtotime($date_input);

        $en_day = date('d', $date_input);
        $en_month = date('m', $date_input);
        $en_year = date('Y', $date_input);

        $bn_month_days = [30, 30, 30, 30, 31, 31, 31, 31, 31, 31, 29, 30];
        $bn_month_middate = [13, 12, 14, 13, 14, 14, 15, 15, 15, 16, 14, 14];
        $bn_months = ['পৌষ', 'মাঘ', 'ফাল্গুন', 'চৈত্র', 'বৈশাখ', 'জ্যৈষ্ঠ', 'আষাঢ়', 'শ্রাবণ', 'ভাদ্র', 'আশ্বিন', 'কার্তিক', 'অগ্রহায়ণ'];

        // Day & Month
        if ($en_day <= $bn_month_middate[$en_month - 1]) {
            $bn_day = $en_day + $bn_month_days[$en_month - 1] - $bn_month_middate[$en_month - 1];
            $bn_month = $bn_months[$en_month - 1];

            // Leap Year
            if (($en_year % 400 == 0 || ($en_year % 100 != 0 && $en_year % 4 == 0)) && $en_month == 3) {
                $bn_day += 1;
            }
        } else {
            $bn_day = $en_day - $bn_month_middate[$en_month - 1];
            $bn_month = $bn_months[$en_month % 12];
        }

        // Year
        $bn_year = $en_year - 593;
        if (($en_year < 4) || (($en_year == 4) && (($en_day < 14) || ($en_day == 14)))) {
            $bn_year -= 1;
        }

        $return_bn_date = $bn_day.' '.$bn_month.' '.$bn_year;
        $return_bn_date = en2bnNumber($return_bn_date);

        return $return_bn_date;
    }
}

/*
 *
 * Decode Id to a Hashids\Hashids
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('generate_rgb_code')) {

    /**
     * Prepare the Column Name for Lables.
     */
    function generate_rgb_code($opacity = '0.9')
    {
        $str = '';
        for ($i = 1; $i <= 3; $i++) {
            $num = mt_rand(0, 255);
            $str .= "$num,";
        }
        $str .= "$opacity,";
        $str = substr($str, 0, -1);

        return $str;
    }
}

/*
 *
 * Return Date with weekday
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('date_today')) {

    /**
     * Return Date with weekday.
     *
     * Carbon Locale will be considered here
     * Example:
     * শুক্রবার, ২৪ জুলাই ২০২০
     * Friday, July 24, 2020
     */
    function date_today()
    {
        $str = \Carbon\Carbon::now()->isoFormat('dddd, LL');

        return $str;
    }
}

if (!function_exists('clear_cache_manually')) {

    function clear_cache_manually()
    {
        Artisan::call('optimize:clear');
    }
}

/**
 * Return common | tenant logo, cover & title
 */
if (!function_exists('school_logo_cover_name')) {
    function school_logo_cover_name()
    {
        /* START - TENANT WISE LOGO & COVER IMAGE APPLY */
        $schoolLogoPath = asset_v('asset/images/logo.png');
        $schoolSidebarLogoPath = asset_v('asset/images/logo-sidebar.png');
        $schoolCoverPicturePath = asset('asset/dist/img/Login-Final.jpg');
        $schoolName = "VentureKids";
        if (tenant()) {

            $logoPath = \Storage::disk('tenant_uploads')->path(tenant()->school_logo);
            if (isset(tenant()->school_logo) && File::exists($logoPath)) {
                $schoolLogoPath = url('tenants/'.tenant()->school_logo);
                $schoolSidebarLogoPath = '';
            }

            $coverPath = \Storage::disk('tenant_uploads')->path(tenant()->school_cover_image);
            if (isset(tenant()->school_cover_image) && File::exists($coverPath)) {
                $schoolCoverPicturePath = url('tenants/'.tenant()->school_cover_image);
            }

            if (isset(tenant()->school_name)) {
                $schoolName = tenant()->school_name;
            }
        }

        return [
            'schoolLogoPath' => $schoolLogoPath,
            'schoolSidebarLogoPath' => $schoolSidebarLogoPath,
            'schoolCoverPicturePath' => $schoolCoverPicturePath,
            'schoolName' => $schoolName,
        ];
        /* END - TENANT WISE LOGO & COVER IMAGE APPLY */
    }
}

if (!function_exists('applyCountryScope')) {
    /**
     * Apply partner country scoping to a query.
     *
     * Super Admins get full access.
     * Sub Admins are limited to the country/countries stored in session.
     *
     * @param mixed $query
     * @param string $column
     * @param string $sessionKey
     * @return mixed
     */
    function applyCountryScope($query, $column = 'country_id', $sessionKey = 'country_id')
    {
        if (!\Auth::check()) {
            return $query;
        }

        $group = (int) \Auth::user()->group;

        if ($group === 1) {
            return $query;
        }

        if ($group !== 5) {
            return $query;
        }

        $countryIds = session($sessionKey, null);

        if (empty($countryIds) && !empty(\Auth::user()->country_id)) {
            $countryIds = \Auth::user()->country_id;
        }

        if (is_string($countryIds)) {
            $countryIds = explode(',', $countryIds);
        }

        if (!is_array($countryIds)) {
            $countryIds = [$countryIds];
        }

        $countryIds = array_values(array_filter(array_map('intval', $countryIds), function ($value) {
            return $value > 0;
        }));

        if (empty($countryIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($column, $countryIds);
    }
}

if (!function_exists('isPartnerUser')) {
    function isPartnerUser()
    {
        return auth()->check() && (int) auth()->user()->group === 5;
    }
}

if (!function_exists('isSuperAdminUser')) {
    function isSuperAdminUser()
    {
        return auth()->check() && (int) auth()->user()->group === 1;
    }
}

if (!function_exists('partnerCountryId')) {
    function partnerCountryId()
    {
        if (!isPartnerUser()) {
            return null;
        }

        $countryId = session('country_id', auth()->user()->country_id ?? null);

        return !empty($countryId) ? (int) $countryId : null;
    }
}

if (!function_exists('partnerSchoolStudentLicenseCount')) {
    function partnerSchoolStudentLicenseCount($schoolId): int
    {
        return \App\Models\StudentLicense::where('school_id', $schoolId)->count();
    }
}

if (!function_exists('partnerSchoolStudentLicenseCountForPartner')) {
    function partnerSchoolStudentLicenseCountForPartner($partnerId): int
    {
        if (empty($partnerId)) {
            return 0;
        }

        return \App\Models\StudentLicense::whereHas('school', function ($query) use ($partnerId) {
            $query->where('created_by', $partnerId)->where('created_type', 'partner');
        })->count();
    }
}

if (!function_exists('partnerSchoolStudentCapacityState')) {
    function partnerSchoolStudentCapacityState(\App\Models\School $school): array
    {
        $isPartnerSchool = $school && (($school->created_type ?? 'admin') === 'partner');
        $schoolLimit = (int) ($school->number_of_student ?? 0);
        $schoolUsed = $isPartnerSchool
            ? partnerSchoolStudentLicenseCount($school->id)
            : (int) \App\Models\Students::where('school_id', $school->id)->count();

        $partnerLimit = null;
        $partnerUsed = null;
        if ($isPartnerSchool) {
            $partnerId = (int) ($school->created_by ?? 0);
            $partnerLimit = (int) (\App\Models\User::whereKey($partnerId)->value('no_of_license_purchased') ?? 0);
            $partnerUsed = partnerSchoolStudentLicenseCountForPartner($partnerId);
        }

        return [
            'is_partner_school' => $isPartnerSchool,
            'school_limit' => $schoolLimit,
            'school_used' => $schoolUsed,
            'school_at_capacity' => $schoolLimit > 0 && $schoolUsed >= $schoolLimit,
            'partner_limit' => $partnerLimit,
            'partner_used' => $partnerUsed,
            'partner_at_capacity' => $isPartnerSchool && $partnerLimit !== null && $partnerLimit > 0 && $partnerUsed >= $partnerLimit,
        ];
    }
}

if (!function_exists('partnerSchoolIsPartnerRecord')) {
    function partnerSchoolIsPartnerRecord(?\App\Models\School $school): bool
    {
        return $school && (($school->created_type ?? 'admin') === 'partner');
    }
}

if (!function_exists('partnerSchoolStudentLevelIdsForStudent')) {
    function partnerSchoolStudentLevelIdsForStudent($schoolId, $studentId): array
    {
        return \App\Models\StudentLicense::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->pluck('level_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();
    }
}

if (!function_exists('partnerSchoolParseStudentLevelCodes')) {
    function partnerSchoolParseStudentLevelCodes(?string $levelCodesRaw): array
    {
        if (empty($levelCodesRaw)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('trim', explode(',', $levelCodesRaw)), function ($value) {
            return $value !== '';
        })));
    }
}

if (!function_exists('partnerSchoolValidateStudentLevelCodes')) {
    function partnerSchoolValidateStudentLevelCodes(array $codes): bool
    {
        if (empty($codes)) {
            return false;
        }

        return \App\Models\Grade::whereIn('unique_code', $codes)->count() === count($codes);
    }
}

if (!function_exists('partnerSchoolNormalizeStudentGradeIds')) {
    function partnerSchoolNormalizeStudentGradeIds($gradeIds): array
    {
        if (empty($gradeIds)) {
            return [];
        }

        if (is_string($gradeIds)) {
            $gradeIds = explode(',', $gradeIds);
        }

        return array_values(array_unique(array_filter(array_map('intval', (array) $gradeIds), function ($value) {
            return $value > 0;
        })));
    }
}

if (!function_exists('partnerSchoolRecordStudentLicense')) {
    function partnerSchoolRecordStudentLicense(\App\Models\Students $student, array $levelIds, $createdBy = null): void
    {
        if (empty($levelIds)) {
            return;
        }

        foreach ($levelIds as $levelId) {
            \App\Models\StudentLicense::updateOrCreate(
                [
                    'school_id' => $student->school_id,
                    'student_id' => $student->id,
                    'level_id' => $levelId,
                ],
                [
                    'license_key' => \App\Models\StudentLicense::where([
                        'school_id' => $student->school_id,
                        'student_id' => $student->id,
                        'level_id' => $levelId,
                    ])->value('license_key') ?: \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(16)),
                    'status' => 1,
                    'created_by' => $createdBy ?? auth()->id(),
                ]
            );
        }
    }
}

if (!function_exists('partnerSchoolMarkRemovedStudentLicensesInactive')) {
    function partnerSchoolMarkRemovedStudentLicensesInactive(\App\Models\Students $student, array $levelIds): void
    {
        if (empty($levelIds)) {
            return;
        }

        \App\Models\StudentLicense::where('school_id', $student->school_id)
            ->where('student_id', $student->id)
            ->whereIn('level_id', $levelIds)
            ->update(['status' => 0]);
    }
}

if (!function_exists('safeMailAction')) {
    function safeMailAction(string $action, array $context, callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::error('Mail action failed', array_merge($context, [
                'action' => $action,
                'exception' => $e->getMessage(),
            ]));
        }
    }
}

if (!function_exists('safeDispatchAction')) {
    function safeDispatchAction(string $action, array $context, callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::error('Dispatch action failed', array_merge($context, [
                'action' => $action,
                'exception' => $e->getMessage(),
            ]));
        }
    }
}

if (!function_exists('safeEventAction')) {
    function safeEventAction(string $action, array $context, callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::error('Event action failed', array_merge($context, [
                'action' => $action,
                'exception' => $e->getMessage(),
            ]));
        }
    }
}

if (!function_exists('partnerRestrictedRoutePatterns')) {
    function partnerRestrictedRoutePatterns(): array
    {
        return [
            'route_names' => [
                'backend.school-notificationbox',
                'backend.school-compose',
                'backend.school-notification-send',
                'backend.addcontent.addContent',
                'backend.contentlist.contentList',
                'backend.addcontent.addContentStudents',
                'backend.addcontent.contentListStudents',
                'backend.addcontent.contentAddStudent',
                'backend.contentdelete.contentDelete',
                'backend.contentedit.contentEdit',
                'backend.contentview.contentView',
                'backend.storecontent.storeContent',
                'backend.addstream.addStream',
                'backend.addstream.destoryStream',
                'backend.editTrainerStream.editTrainerStream',
                'backend.deleteTrainerStream.deleteTrainerStream',
                'backend.destorytrainerstream.destoryTrainerStream',
                'backend.changetrainerstream.changeTrainerStream',
                'backend.updateTrainerstream.updateOrderTrainerstream',
                'backend.updateTrainercontent.updateOrderTrainercontent',
                'backend.trainerstream.index',
                'backend.trainerdelete.trainerDelete',
                'backend.trainer-suspend',
                'backend.trainer-unsuspend',
                'backend.observation_list',
                'backend.observation.create',
                'backend.observation.store',
                'backend.observation.edit',
                'backend.observation.update',
                'backend.observation.delete',
                'backend.assignmentlist',
                'backend.create-assignment',
                'backend.save-assignment',
                'backend.multi-assignment',
                'backend.edit-assignment',
                'backend.update-assignment',
                'backend.delete-assignment',
                'backend.assignment',
                'backend.level.index',
                'backend.level.create',
                'backend.level.store',
                'backend.level.delete',
                'backend.level.edit',
                'backend.level.update',
                'backend.trainerlevel.index',
                'backend.trainerlevel.create',
                'backend.trainerlevel.store',
                'backend.trainerlevel.delete',
                'backend.trainerlevel.edit',
                'backend.trainerlevel.update',
                'backend.stream.index',
                'backend.stream.create',
                'backend.stream.store',
                'backend.stream.edit',
                'backend.stream.update',
                'backend.stream.delete',
                'backend.student.playaffirmation',
                'backend.aiToollist.aiToolList',
                'backend.aiToolcreate.aiToolCreate',
                'backend.aiToolstore.aiToolStore',
                'backend.aiTooldelete.aiToolDelete',
                'backend.aiTooledit.aiToolEdit',
                'backend.aiToolupdate.aiToolUpdate',
                'backend.getAiTool',
                'backend.projectmanagement.index',
                'backend.projectSectioncreate.projectSectionCreate',
                'backend.projectSectionstore.projectSectionStore',
                'backend.getProjectSection',
                'backend.projectSectionlist.projectSectionList',
                'backend.projectSectiondelete.projectSectionDelete',
                'backend.projectSectionedit.projectSectionEdit',
                'backend.projectSectionupdate.projectSectionUpdate',
                'backend.projectQuestionlist.projectQuestionList',
                'backend.getProjectQuestion',
                'backend.projectQuestioncreate.projectQuestionCreate',
                'backend.projectQuestionstore.projectQuestionStore',
                'backend.projectQuestionAttachmentstore.projectQuestionAttachmentStore',
                'backend.projectQuestionToggleattachments.projectQuestionToggleAttachments',
                'backend.projectQuestiondelete.projectQuestionDelete',
                'backend.projectQuestionedit.projectQuestionEdit',
                'backend.projectQuestionupdate.projectQuestionUpdate',
                'backend.createvideo.createVideo',
                'backend.storevideo.storeVideo',
                'backend.resources.resources',
                'backend.get_resource_value',
                'backend.save_resource_value',
                'backend.trainerContent.uploadFile',
                'backend.weeklyChallengeList',
                'backend.weeklyChallengeAdd',
                'backend.weeklyChallengeStore',
                'backend.weeklyChallengeEdit',
                'backend.weeklyChallengeUpdate',
                'backend.deleteWeeklyChallengeImage',
                'backend.weeklyChallengeDelete',
                'backend.weekly.deleteQuestion',
                'backend.weekly.disableQuestion',
                'backend.weekly.enableQuestion',
                'backend.student-add',
                'backend.student-store',
                'backend.student-delete',
                'backend.studentDeleteRequest.studentDeleteRequest',
                'backend.delete_existing_student',
                'student-add',
                'add-student',
                'student-store',
                'student-new',
                'student-delete',
                'studentDeleteRequest.studentDeleteRequest',
                'student_delete_list_datatable',
                'delete_existing_student',
            ],
            'route_prefixes' => [
                'backend.standard_assessment.categories.',
                'backend.standard_assessment.questions.',
                'backend.realqassessment.grades.',
                'backend.realqassessment.subjects.',
                'backend.realqassessment.parameters.',
                'backend.realqassessment.scales.',
                'backend.realqassessment.rubrics.',
                'backend.realqassessment.boards.',
                'backend.realqassessment.topics.',
                'backend.realqassessment.questions.',
                'backend.realqassessment.accuracy-dashboard.',
            ],
        ];
    }
}

if (!function_exists('isPartnerRestrictedRoute')) {
    function isPartnerRestrictedRoute($routeName = null): bool
    {
        $patterns = partnerRestrictedRoutePatterns();
        $routeName = strtolower((string) $routeName);

        foreach ($patterns['route_names'] as $pattern) {
            if (!empty($routeName) && $routeName === strtolower($pattern)) {
                return true;
            }
        }

        foreach ($patterns['route_prefixes'] ?? [] as $prefix) {
            if (!empty($routeName) && str_starts_with($routeName, strtolower($prefix))) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('tenant_css')) {
    function tenant_css()
    {
        if (tenant()) {
            $cssPath = \Storage::disk('tenant_uploads')->path(tenant()->tenant_id.'/school/css/custom.css');
            if (File::exists($cssPath)) {
                $school_css_path = 'tenants/'.tenant()->tenant_id.'/school/css/custom.css';

                return $school_css_path;
            }
        }

        return null;
    }
}

if (!function_exists('tenant_loader')) {
    function tenant_loader()
    {
        if (tenant()) {
            $loaderPath = \Storage::disk('tenant_uploads')->path(tenant()->tenant_id.'/school/loader/school-loader.png');
            if (File::exists($loaderPath)) {
                $school_loader_path = 'tenants/'.tenant()->tenant_id.'/school/loader/school-loader.png';

                return $school_loader_path;
            }
        }

        return null;
    }
}
