<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\School;
use App\Models\Country;

class AddColumnCountryIdToSchool extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->unsignedInteger('country_id')->nullable()->default(1)->after('number_of_student')
                ->references('id')->on('countrys')->onDelete('cascade');
        });

        foreach (School::all() as $school) {
            $schoolCountryAvailable = Country::where('name', 'LIKE', '%'.$school->country.'%')->first();
            if (empty($schoolCountryAvailable)) {
                $country = new Country();
                $country->name = $school->country;
                $country->save();
                
                $school->country_id = $country->id;
                $school->update();
            } else {
                $school->country_id = $schoolCountryAvailable->id;
                $school->update();
            }
        }

        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('country');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('country')->after('country_id');
        });
        foreach (School::all() as $school) {
            $country = Country::find($school->country_id)->first();
            if (!empty($country)) {
                $school->country = $country->name;
                $school->update();
            }
        }

        Schema::table('schools', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
        });
    }
}
