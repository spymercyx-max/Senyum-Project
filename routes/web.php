<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Developer\DistributorController as DevDistributorController;
use App\Http\Controllers\Developer\DashboardController as DevDashboardController;
use App\Http\Controllers\Developer\InventoryController as DevInventoryController;
use App\Http\Controllers\Developer\NotificationController as DevNotificationController;
use App\Http\Controllers\Developer\OutletController as DevOutletController;
use App\Http\Controllers\Developer\ProductController as DevProductController;
use App\Http\Controllers\Developer\ProductImageController as DevProductImageController;
use App\Http\Controllers\Developer\ProfileController as DevProfileController;
use App\Http\Controllers\Developer\PurchaseOrderController as DevPurchaseOrderController;
use App\Http\Controllers\Developer\ReportController as DevReportController;
use App\Http\Controllers\Developer\TerritoryController as DevTerritoryController;
use App\Http\Controllers\Distributor\DashboardController as DistDashboardController;
use App\Http\Controllers\Distributor\LedgerController as DistLedgerController;
use App\Http\Controllers\Distributor\MapController as DistMapController;
use App\Http\Controllers\Distributor\NotificationController as DistNotificationController;
use App\Http\Controllers\Distributor\OutletController as DistOutletController;
use App\Http\Controllers\Distributor\OutletImageController as DistOutletImageController;
use App\Http\Controllers\Distributor\ProfileController as DistProfileController;
use App\Http\Controllers\Distributor\PurchaseOrderController as DistPurchaseOrderController;
use App\Http\Controllers\Distributor\StatusController as DistStatusController;
use App\Http\Controllers\Distributor\TransactionController as DistTransactionController;
use App\Http\Controllers\Distributor\VisitController as DistVisitController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PartnershipController;
use App\Http\Controllers\Public\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC — SENYUM BRAND EXPERIENCE (guest, identitas KUNING)
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tentang', [HomeController::class, 'about'])->name('about');
Route::get('/produk', [ProductController::class, 'index'])->name('products.index');
Route::get('/produk/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/kemitraan', [PartnershipController::class, 'index'])->name('partnership');

/*
|--------------------------------------------------------------------------
| AUTH — SATU HALAMAN LOGIN, TANPA ROLE SELECTOR
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:10,1')->name('login.store');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware('throttle:10,1')->name('register.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| ROLE REDIRECT
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $user = auth()->user();
    if (! $user) {
        return redirect()->route('login');
    }
    if ($user->isDeveloper()) {
        return redirect()->route('developer.dashboard');
    }
    $status = $user->distributorStatus() ?? 'pending';
    return match ($status) {
        'approved' => redirect()->route('distributor.dashboard'),
        'rejected' => redirect()->route('distributor.rejected'),
        'suspended' => redirect()->route('distributor.suspended'),
        default => redirect()->route('distributor.pending'),
    };
})->middleware('auth')->name('dashboard');

/*
|--------------------------------------------------------------------------
| DEVELOPER — CONTROL CENTER (identitas TOSCA GREEN)
|--------------------------------------------------------------------------
*/
Route::prefix('developer')->name('developer.')->middleware(['auth', 'role:developer'])->group(function () {
    Route::get('/', fn () => redirect()->route('developer.dashboard'));
    Route::get('/dashboard', [DevDashboardController::class, 'index'])->name('dashboard');

    Route::get('/wilayah', [DevTerritoryController::class, 'index'])->name('wilayah');
    Route::get('/wilayah/{territory}', [DevTerritoryController::class, 'show'])->name('wilayah.show');

    Route::get('/distributor', [DevDistributorController::class, 'index'])->name('distributor');
    Route::get('/distributor/{user}', [DevDistributorController::class, 'show'])->name('distributor.show');
    Route::post('/distributor/{user}/approve', [DevDistributorController::class, 'approve'])->name('distributor.approve');
    Route::post('/distributor/{user}/reject', [DevDistributorController::class, 'reject'])->name('distributor.reject');
    Route::post('/distributor/{user}/suspend', [DevDistributorController::class, 'suspend'])->name('distributor.suspend');

    Route::get('/outlet', [DevOutletController::class, 'index'])->name('outlet');

    Route::get('/produk', [DevProductController::class, 'index'])->name('produk');
    Route::get('/produk/create', [DevProductController::class, 'create'])->name('produk.create');
    Route::post('/produk', [DevProductController::class, 'store'])->name('produk.store');
    Route::get('/produk/{product}/edit', [DevProductController::class, 'edit'])->name('produk.edit');
    Route::put('/produk/{product}', [DevProductController::class, 'update'])->name('produk.update');
    Route::post('/produk/{product}/featured', [DevProductController::class, 'featured'])->name('produk.featured');
    Route::post('/produk/{product}/archive', [DevProductController::class, 'archive'])->name('produk.archive');
    Route::delete('/produk/{product}', [DevProductController::class, 'destroy'])->name('produk.destroy');
    Route::post('/produk/{product}/images', [DevProductImageController::class, 'store'])->name('produk.images.store');
    Route::delete('/produk/{product}/images/{image}', [DevProductImageController::class, 'destroy'])->name('produk.images.destroy');
    Route::post('/produk/{product}/images/{image}/move', [DevProductImageController::class, 'move'])->name('produk.images.move');

    Route::get('/inventory', [DevInventoryController::class, 'index'])->name('inventory');
    Route::post('/inventory/{inventory}/adjust', [DevInventoryController::class, 'adjust'])->name('inventory.adjust');

    Route::get('/po', [DevPurchaseOrderController::class, 'index'])->name('po');
    Route::get('/po/{order}', [DevPurchaseOrderController::class, 'show'])->name('po.show');
    Route::post('/po/{order}/transition', [DevPurchaseOrderController::class, 'transition'])->name('po.transition');

    Route::get('/laporan', [DevReportController::class, 'index'])->name('laporan');
    Route::get('/peta', [DevReportController::class, 'map'])->name('peta');
    Route::get('/notifikasi', [DevNotificationController::class, 'index'])->name('notifikasi');
    Route::get('/profil', [DevProfileController::class, 'index'])->name('profil');
});

/*
|--------------------------------------------------------------------------
| DISTRIBUTOR — FIELD WORKSPACE (identitas CYAN, sidebar + drawer mobile)
| Catatan: fitur Titip Jual DIHAPUS dari workflow aktif (§65/§89).
|--------------------------------------------------------------------------
*/
Route::prefix('distributor')->name('distributor.')->middleware('auth')->group(function () {
    Route::get('/pending', [DistStatusController::class, 'pending'])->name('pending');
    Route::get('/rejected', [DistStatusController::class, 'rejected'])->name('rejected');
    Route::get('/suspended', [DistStatusController::class, 'suspended'])->name('suspended');

    Route::middleware(['role:distributor', 'distributor.status:approved'])->group(function () {
        Route::get('/', fn () => redirect()->route('distributor.dashboard'));
        Route::get('/dashboard', [DistDashboardController::class, 'index'])->name('dashboard');

        // SALES
        Route::get('/pemesanan', [DistTransactionController::class, 'order'])->name('pemesanan');
        Route::post('/pemesanan', [DistTransactionController::class, 'storeOrder'])->name('order');
        Route::get('/transaksi', [DistTransactionController::class, 'index'])->name('transaksi');
        Route::post('/transaksi', [DistTransactionController::class, 'store'])->name('transaksi.store');
        Route::get('/transaksi/{transaction}', [DistTransactionController::class, 'show'])->name('transaksi.show');
        Route::get('/transaksi/{transaction}/edit', [DistTransactionController::class, 'edit'])->name('transaksi.edit');
        Route::put('/transaksi/{transaction}', [DistTransactionController::class, 'update'])->name('transaksi.update');
        Route::post('/transaksi/{transaction}/void', [DistTransactionController::class, 'void'])->name('transaksi.void');
        Route::get('/jual-ecer', [DistTransactionController::class, 'retail'])->name('jual-ecer');
        Route::post('/jual-ecer', [DistTransactionController::class, 'storeRetail'])->name('jual-ecer.store');

        // NETWORK
        Route::get('/outlet', [DistOutletController::class, 'index'])->name('outlet');
        Route::get('/outlet/create', [DistOutletController::class, 'create'])->name('outlet.create');
        Route::post('/outlet', [DistOutletController::class, 'store'])->name('outlet.store');
        Route::get('/outlet/{outlet}', [DistOutletController::class, 'show'])->name('outlet.show');
        Route::get('/outlet/{outlet}/edit', [DistOutletController::class, 'edit'])->name('outlet.edit');
        Route::put('/outlet/{outlet}', [DistOutletController::class, 'update'])->name('outlet.update');
        Route::delete('/outlet/{outlet}', [DistOutletController::class, 'destroy'])->name('outlet.destroy');
        Route::post('/outlet/{outlet}/images', [DistOutletImageController::class, 'store'])->name('outlet.images.store');
        Route::delete('/outlet/{outlet}/images/{image}', [DistOutletImageController::class, 'destroy'])->name('outlet.images.destroy');
        Route::get('/visit', [DistVisitController::class, 'index'])->name('visit');
        Route::post('/visit', [DistVisitController::class, 'store'])->name('visit.store');
        Route::get('/peta', [DistMapController::class, 'index'])->name('peta');

        // OPERATIONS (tanpa Titip Jual)
        Route::get('/po', [DistPurchaseOrderController::class, 'index'])->name('po');
        Route::post('/po', [DistPurchaseOrderController::class, 'store'])->name('po.store');
        Route::get('/po/{order}', [DistPurchaseOrderController::class, 'show'])->name('po.show');
        Route::get('/pembukuan', [DistLedgerController::class, 'index'])->name('pembukuan');

        // SYSTEM
        Route::get('/notifikasi', [DistNotificationController::class, 'index'])->name('notifikasi');
        Route::get('/profil', [DistProfileController::class, 'index'])->name('profil');
        Route::get('/bantuan', [DistProfileController::class, 'help'])->name('bantuan');
    });
});
