<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DatabaseController extends Controller
{
    public function clearCache(){

        $output2 = shell_exec('php core/artisan config:clear');
        $output3 = shell_exec('php core/artisan cache:clear');
        $output3 = shell_exec('php core/artisan route:clear');
        $output3 = shell_exec('php core/artisan view:clear');
        $output1 = shell_exec('php core/artisan config:cache');
        $output1 = shell_exec('php core/artisan route:cache');
        $output1 = shell_exec('php core/artisan view:cache');
        $output4 = shell_exec('php core/artisan optimize:clear');

        dd([$output1,$output2,$output3,$output4]);
    }
    public function sessionTable(){
        // Artisan::call('config:cache');
        // Artisan::call('config:clear');
        // Artisan::call('cache:clear');

        // Artisan::call('session:table --force');


        $output1 = shell_exec('php core/artisan config:cache');
        $output2 = shell_exec('php core/artisan config:clear');
        $output3 = shell_exec('php core/artisan cache:clear');
        $output4 = shell_exec('php core/artisan session:table --force');

        dd([$output1,$output2,$output3,$output4]);
    }
    public function migrate()
    {
        // Run the migrate:fresh command
        // Artisan::call('migrate --force');

        $output = shell_exec('php core/artisan migrate --force');
        dd($output);
    }

    public function migrateFresh()
    {
        // Run the migrate:fresh command
        // Artisan::call('migrate:fresh --force');

        $output = shell_exec('php core/artisan migrate:fresh --force');
        dd($output);
    }

    public function migrateRollback()
    {
        // Run the migrate:fresh command
        // Artisan::call('migrate:rollback --force');

        $output = shell_exec('php core/artisan migrate:rollback --force');
        dd($output);
    }

    public function dbSeed(){
        // Artisan::call('db:seed --force');

        $output = shell_exec('php core/artisan db:seed --force');
        dd($output);
    }

    public function optimizeClear(){
        // Artisan::call('optimize:clear --force');

        $output = shell_exec('php core/artisan optimize:clear');
        dd($output);
    }
}
