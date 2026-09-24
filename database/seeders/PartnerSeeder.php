<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds 3 partner accounts (group = 5), mirroring the exact fields
 * PartnerController@store sets when a partner is created from the admin UI.
 */
class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $countryId = DB::table('countrys')->orderBy('id')->value('id');
        $currencyId = DB::table('currencies')->orderBy('id')->value('id');

        $partners = [
            ['name' => 'Aarav Mehta', 'email' => 'partner1@venturekids.test', 'username' => 'partner_aarav'],
            ['name' => 'Priya Nair', 'email' => 'partner2@venturekids.test', 'username' => 'partner_priya'],
            ['name' => 'Rohan Kapoor', 'email' => 'partner3@venturekids.test', 'username' => 'partner_rohan'],
        ];

        foreach ($partners as $partner) {
            User::firstOrCreate(
                ['email' => $partner['email']],
                [
                    'name' => $partner['name'],
                    'username' => $partner['username'],
                    'mobile' => '9' . random_int(100000000, 999999999),
                    'country_id' => $countryId,
                    'password' => Hash::make('secret'),
                    'group' => 5,
                    'allow_add_trainers' => 1,
                    'no_of_license_purchased' => 10,
                    'partnership_start_date' => now()->format('Y-m-d'),
                    'partnership_end_date' => now()->addYear()->format('Y-m-d'),
                    'currency_id' => $currencyId,
                    'status' => 1,
                    'suspend' => 2,
                    'avatar' => 'img/default_image.png',
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
