<?php

namespace Database\Seeders\Auth;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Class PermissionRoleTableSeeder.
 */
class PermissionRoleTableSeeder extends Seeder
{
    /**
     * Run the database seed.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        // Create Roles
        $super_admin = Role::create(['name' => 'super admin']);
        $admin = Role::create(['name' => 'administrator']);
        $manager = Role::create(['name' => 'manager']);
        $executive = Role::create(['name' => 'executive']);
        $user = Role::create(['name' => 'user']);
        // IDs 6/7/8 are hardcoded by SchoolOnboardingService, TrainerController,
        // and StudentService when a school/trainer/student account is created
        // (Role::where('id', 6|7|8)), so these must exist in this exact order.
        $trainer = Role::create(['name' => 'trainer']);
        $school = Role::create(['name' => 'school']);
        $student = Role::create(['name' => 'student']);

        // Create Permissions
        Permission::firstOrCreate(['name' => 'view_backend']);
        Permission::firstOrCreate(['name' => 'edit_settings']);
        Permission::firstOrCreate(['name' => 'view_logs']);
        Permission::firstOrCreate(['name' => 'trainer_edit']);
        Permission::firstOrCreate(['name' => 'school_edit']);
        Permission::firstOrCreate(['name' => 'student_edit']);

        $permissions = Permission::defaultPermissions();

        foreach ($permissions as $perms) {
            Permission::firstOrCreate(['name' => $perms]);
        }

        \Artisan::call('auth:permission', [
            'name' => 'posts',
        ]);
        echo "\n _Posts_ Permissions Created.";

        \Artisan::call('auth:permission', [
            'name' => 'categories',
        ]);
        echo "\n _Categories_ Permissions Created.";

        \Artisan::call('auth:permission', [
            'name' => 'tags',
        ]);
        echo "\n _Tags_ Permissions Created.";

        \Artisan::call('auth:permission', [
            'name' => 'comments',
        ]);
        echo "\n _Comments_ Permissions Created.";

        echo "\n\n";

        // Assign Permissions to Roles
        $admin->givePermissionTo(Permission::all());
        $manager->givePermissionTo('view_backend');
        $executive->givePermissionTo('view_backend');
        $trainer->givePermissionTo(['view_backend', 'trainer_edit']);
        $school->givePermissionTo(['view_backend', 'school_edit']);
        $student->givePermissionTo(['view_backend', 'student_edit']);

        Schema::enableForeignKeyConstraints();
    }
}
