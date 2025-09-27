<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/create-storage-folder', function () {
    try {
        // Path untuk direktori storage
        $publicPath = public_path('storage');
        $storagePath = storage_path('app/public');

        // Buat direktori storage/app/public jika belum ada
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
            echo "Created directory: {$storagePath}<br>";
        }

        // Hapus folder public/storage jika sudah ada (untuk mencegah error)
        if (is_dir($publicPath)) {
            // Hapus symlink yang sudah ada
            if (is_link($publicPath)) {
                unlink($publicPath);
                echo "Removed existing symlink<br>";
            }
            // Atau hapus folder jika bukan symlink
            else {
                rmdir($publicPath);
                echo "Removed existing directory<br>";
            }
        }

        // Coba gunakan Artisan command untuk membuat symlink
        Artisan::call('storage:link');
        echo "Artisan command executed: " . Artisan::output() . "<br>";

        // Verifikasi apakah link berhasil dibuat
        if (is_link($publicPath) || is_dir($publicPath)) {
            return "Storage link created successfully!";
        } else {
            return "Failed to create storage link. Please check server permissions.";
        }
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});
Route::get('/', function () {
    return view('pages.auth.login');
});
Route::middleware(['auth'])->group(function () {
    Route::get('home', function () {
        return view('pages.dashboard');
    })->name('home');

    Route::resource('user', UserController::class);
    Route::resource('product', ProductController::class);
    Route::resource('order', OrderController::class);
    Route::resource('categories', CategoryController::class);
});
