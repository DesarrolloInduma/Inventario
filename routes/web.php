<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HardwareController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SoftwareLicenciadoController;
use App\Http\Controllers\SoftwareNoLicenciadoController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::get('/login', [LoginController::class, 'showLoginForm']);
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/hardware', [ReporteController::class, 'hardware'])->name('reportes.hardware');
    Route::get('/reportes/hardware.csv', [ReporteController::class, 'hardwareCsv'])->name('reportes.hardware.csv');
    Route::get('/reportes/software', [ReporteController::class, 'software'])->name('reportes.software');
    Route::get('/reportes/auditoria', [ReporteController::class, 'auditoria'])->name('reportes.auditoria');
    Route::get('/auditorias', [AuditoriaController::class, 'index'])->name('auditorias.index');
    Route::get('/hardware/{serial}/auditorias/crear', [AuditoriaController::class, 'create'])->name('auditorias.create');
    Route::post('/hardware/{serial}/auditorias', [AuditoriaController::class, 'store'])->name('auditorias.store');

    Route::get('/hardware', [HardwareController::class, 'index'])->name('hardware.index');
    Route::get('/hardware/crear', [HardwareController::class, 'create'])->name('hardware.create');
    Route::post('/hardware', [HardwareController::class, 'store'])->name('hardware.store');
    Route::get('/hardware/{serial}', [HardwareController::class, 'show'])->name('hardware.show');
    Route::get('/hardware/{serial}/acta-entrega', [HardwareController::class, 'deliveryAct'])->name('hardware.delivery-act');
    Route::get('/hardware/{serial}/editar', [HardwareController::class, 'edit'])->name('hardware.edit');
    Route::put('/hardware/{serial}', [HardwareController::class, 'update'])->name('hardware.update');
    Route::delete('/hardware/{serial}', [HardwareController::class, 'destroy'])->name('hardware.destroy');
    Route::patch('/hardware/{serial}/reactivar', [HardwareController::class, 'restore'])->name('hardware.restore');
    Route::post('/hardware/{serial}/observaciones', [HardwareController::class, 'storeObservation'])->name('hardware.observations.store');
    Route::delete('/hardware/{serial}/observaciones/{id}', [HardwareController::class, 'destroyObservation'])->name('hardware.observations.destroy');
    Route::post('/hardware/{serial}/actas', [HardwareController::class, 'storeActa'])->name('hardware.actas.store');
    Route::get('/hardware/{serial}/actas/{id}/descargar', [HardwareController::class, 'downloadActa'])->name('hardware.actas.download');
    Route::delete('/hardware/{serial}/actas/{id}', [HardwareController::class, 'destroyActa'])->name('hardware.actas.destroy');
    Route::put('/hardware/{serial}/dispositivos', [HardwareController::class, 'syncDevices'])->name('hardware.devices.sync');
    Route::post('/hardware/{serial}/cambios-usuario', [HardwareController::class, 'storeUserChange'])->name('hardware.user-changes.store');

    // Software licenciado
    Route::get('/software-licenciado', [SoftwareLicenciadoController::class, 'index'])->name('softlic.index');
    Route::get('/software-licenciado/crear', [SoftwareLicenciadoController::class, 'create'])->name('softlic.create');
    Route::post('/software-licenciado', [SoftwareLicenciadoController::class, 'store'])->name('softlic.store');
    Route::get('/software-licenciado/{id}', [SoftwareLicenciadoController::class, 'show'])->name('softlic.show');
    Route::get('/software-licenciado/{id}/editar', [SoftwareLicenciadoController::class, 'edit'])->name('softlic.edit');
    Route::put('/software-licenciado/{id}', [SoftwareLicenciadoController::class, 'update'])->name('softlic.update');
    Route::delete('/software-licenciado/{id}', [SoftwareLicenciadoController::class, 'destroy'])->name('softlic.destroy');
    Route::post('/software-licenciado/{id}/asignar', [SoftwareLicenciadoController::class, 'asignar'])->name('softlic.asignar');
    Route::delete('/software-licenciado/{id}/liberar/{serial}', [SoftwareLicenciadoController::class, 'liberar'])->name('softlic.liberar');

    // Software no licenciado
    Route::get('/software-nolicenciado', [SoftwareNoLicenciadoController::class, 'index'])->name('softnl.index');
    Route::get('/software-nolicenciado/crear', [SoftwareNoLicenciadoController::class, 'create'])->name('softnl.create');
    Route::post('/software-nolicenciado', [SoftwareNoLicenciadoController::class, 'store'])->name('softnl.store');
    Route::get('/software-nolicenciado/{id}', [SoftwareNoLicenciadoController::class, 'show'])->name('softnl.show');
    Route::get('/software-nolicenciado/{id}/editar', [SoftwareNoLicenciadoController::class, 'edit'])->name('softnl.edit');
    Route::put('/software-nolicenciado/{id}', [SoftwareNoLicenciadoController::class, 'update'])->name('softnl.update');
    Route::delete('/software-nolicenciado/{id}', [SoftwareNoLicenciadoController::class, 'destroy'])->name('softnl.destroy');
    Route::post('/software-nolicenciado/{id}/asignar', [SoftwareNoLicenciadoController::class, 'asignar'])->name('softnl.asignar');
    Route::delete('/software-nolicenciado/{id}/liberar/{serial}', [SoftwareNoLicenciadoController::class, 'liberar'])->name('softnl.liberar');

    // Mantenimientos
    Route::get('/mantenimientos', [MantenimientoController::class, 'index'])->name('mant.index');
    Route::get('/hardware/{serial}/mantenimientos/crear', [MantenimientoController::class, 'create'])->name('mant.create');
    Route::post('/hardware/{serial}/mantenimientos', [MantenimientoController::class, 'store'])->name('mant.store');
    Route::get('/mantenimientos/{id}', [MantenimientoController::class, 'show'])->name('mant.show');
    Route::delete('/mantenimientos/{id}', [MantenimientoController::class, 'destroy'])->name('mant.destroy');

    // Catálogos (escritura solo admin)
    Route::get('/catalogos/{tabla}', [CatalogoController::class, 'index'])->name('catalogo.index');
    Route::post('/catalogos/{tabla}', [CatalogoController::class, 'store'])->name('catalogo.store')->middleware('admin');
    Route::put('/catalogos/{tabla}/{id}', [CatalogoController::class, 'update'])->name('catalogo.update')->middleware('admin');
    Route::delete('/catalogos/{tabla}/{id}', [CatalogoController::class, 'destroy'])->name('catalogo.destroy')->middleware('admin');
});

