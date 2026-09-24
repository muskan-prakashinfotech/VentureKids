<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Artisan;

class RouteActionController extends Controller
{
    public function clear()
    {
        try {
            return Artisan::call('optimize:clear');
        } catch (Exception $e) {
            $e->getMessage();

            return $e;
        }
    }

    public function migration()
    {
        try {
            return Artisan::call('migrate');
        } catch (Exception $e) {
            $e->getMessage();

            return $e;
        }
    }

    public function migrationRefresh()
    {
        try {
            return Artisan::call('migrate:refresh');
        } catch (Exception $e) {
            $e->getMessage();

            return $e;
        }
    }

    public function migrationRollback()
    {
        try {
            return Artisan::call('migrate:rollback');
        } catch (Exception $e) {
            $e->getMessage();

            return $e;
        }
    }

    public function seedRun()
    {
        try {
            return Artisan::call('db:seed');
        } catch (Exception $e) {
            $e->getMessage();

            return $e;
        }
    }

    public function runSeedWithClass($class)
    {
        try {
            return Artisan::call('db:seed --class=' . $class);
        } catch (Exception $e) {
            $e->getMessage();

            return $e;
        }
    }

    public function artisanCall($action)
    {
        try {
            return Artisan::call($action);
        } catch (Exception $e) {
            $e->getMessage();

            return $e;
        }
    }
}
