<?php

use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DriveController;
use App\Http\Controllers\Api\V1\EmailVerificationController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PasswordResetController;
use App\Http\Controllers\Api\V1\PublicShareController;
use App\Http\Controllers\Api\V1\SessionController;
use App\Http\Controllers\Api\V1\ShareController;
use App\Http\Controllers\Api\V1\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/v1/auth/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::prefix('auth')->name('auth.')->group(function (): void {
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth')->name('register');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth')->name('login');
        Route::post('/forgot-password', [PasswordResetController::class, 'store'])->middleware('throttle:auth')->name('forgot-password');
        Route::post('/reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:auth')->name('reset-password');
    });

    Route::get('/shares/public/{token}', [PublicShareController::class, 'show'])
        ->middleware('throttle:public-shares')
        ->name('public-shares.show');
    Route::get('/shares/public/{token}/preview', [PublicShareController::class, 'preview'])
        ->middleware('throttle:public-shares')
        ->name('public-shares.preview');
    Route::get('/shares/public/{token}/download', [PublicShareController::class, 'download'])
        ->middleware('throttle:public-shares')
        ->name('public-shares.download');

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
        Route::prefix('auth')->name('auth.')->group(function (): void {
            Route::get('/me', [AuthController::class, 'me'])->name('me');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])->name('verification.send');
        });

        Route::get('/sessions', [SessionController::class, 'index'])->name('sessions.index');
        Route::delete('/sessions/{tokenId}', [SessionController::class, 'destroy'])->name('sessions.destroy');

        Route::get('/drive', [DriveController::class, 'index'])->name('drive.index');
        Route::post('/drive/folders', [DriveController::class, 'storeFolder'])->name('drive.folders.store');
        Route::patch('/drive/items/{driveItem}/rename', [DriveController::class, 'rename'])->name('drive.rename');
        Route::patch('/drive/items/{driveItem}/move', [DriveController::class, 'move'])->name('drive.move');
        Route::patch('/drive/items/{driveItem}/favorite', [DriveController::class, 'favorite'])->name('drive.favorite');
        Route::delete('/drive/items/{driveItem}', [DriveController::class, 'destroy'])->name('drive.destroy');
        Route::post('/drive/items/{driveItemId}/restore', [DriveController::class, 'restore'])->name('drive.restore');
        Route::get('/drive/items/{driveItem}/download', [DriveController::class, 'download'])->name('drive.download');
        Route::get('/drive/items/{driveItem}/preview', [DriveController::class, 'preview'])->name('drive.preview');

        Route::post('/uploads', [UploadController::class, 'store'])->middleware('throttle:uploads')->name('uploads.store');
        Route::post('/uploads/{uploadSession}/chunks', [UploadController::class, 'chunk'])->middleware('throttle:uploads')->name('uploads.chunk');
        Route::post('/uploads/{uploadSession}/complete', [UploadController::class, 'complete'])->middleware('throttle:uploads')->name('uploads.complete');
        Route::delete('/uploads/{uploadSession}', [UploadController::class, 'destroy'])->name('uploads.destroy');

        Route::get('/drive/items/{driveItem}/shares', [ShareController::class, 'index'])->name('shares.index');
        Route::get('/shared-with-me', [ShareController::class, 'sharedWithMe'])->name('shares.shared-with-me');
        Route::post('/shares', [ShareController::class, 'store'])->name('shares.store');
        Route::delete('/shares/{shareLink}', [ShareController::class, 'destroy'])->name('shares.destroy');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

        Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->middleware('throttle:admin')->name('admin.dashboard');
    });
});
