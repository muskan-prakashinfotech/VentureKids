<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Trainer;
use App\Models\Country;

class AddColumnCountryIdToTrainers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trainers', function (Blueprint $table) {
            $table->unsignedInteger('country_id')->nullable()->default(1)->after('city')
                ->references('id')->on('countrys')->onDelete('cascade');
        });

        foreach (Trainer::all() as $trainer) {
            $trainerCountryAvailable = Country::where('name', 'LIKE', '%'.$trainer->country.'%')->first();
            if (empty($trainerCountryAvailable)) {
                $country = new Country();
                $country->name = $trainer->country;
                $country->save();
                
                $trainer->country_id = $country->id;
                $trainer->update();
            } else {
                $trainer->country_id = $trainerCountryAvailable->id;
                $trainer->update();
            }
        }

        Schema::table('trainers', function (Blueprint $table) {
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
        Schema::table('trainers', function (Blueprint $table) {
            $table->string('country')->after('country_id');
        });
        foreach (Trainer::all() as $trainer) {
            $country = Country::find($trainer->country_id)->first();
            if (!empty($country)) {
                $trainer->country = $country->name;
                $trainer->update();
            }
        }

        Schema::table('trainers', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
        });
    }
}
