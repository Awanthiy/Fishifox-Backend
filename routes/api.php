<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\QuotationController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ExpirationController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\RecurringInvoiceController;
use App\Http\Controllers\Api\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index']);


// Customers
Route::get('/customers', [CustomerController::class, 'index']);
Route::post('/customers', [CustomerController::class, 'store']);
Route::put('/customers/{customer}', [CustomerController::class, 'update']);
Route::delete('/customers/{customer}', [CustomerController::class, 'destroy']);


// Services
Route::get('/services', [ServiceController::class, 'index']);
Route::post('/services', [ServiceController::class, 'store']);
Route::put('/services/{service}', [ServiceController::class, 'update']);
Route::delete('/services/{service}', [ServiceController::class, 'destroy']);


// Quotations
Route::get('/quotations', [QuotationController::class, 'index']);
Route::post('/quotations', [QuotationController::class, 'store']);
Route::put('/quotations/{quotation}', [QuotationController::class, 'update']);
Route::delete('/quotations/{quotation}', [QuotationController::class, 'destroy']);
Route::post('/quotations/{quotation}/convert', [QuotationController::class, 'convert']);


// Invoices
Route::get('/invoices', [InvoiceController::class, 'index']);
Route::post('/invoices', [InvoiceController::class, 'store']);
Route::put('/invoices/{invoice}', [InvoiceController::class, 'update']);
Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy']);


// Projects
Route::get('/projects', [ProjectController::class, 'index']);
Route::post('/projects', [ProjectController::class, 'store']);
Route::put('/projects/{project}', [ProjectController::class, 'update']);
Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);


// Expirations
Route::get('/expirations', [ExpirationController::class, 'index']);
Route::post('/expirations', [ExpirationController::class, 'store']);
Route::put('/expirations/{expiration}', [ExpirationController::class, 'update']);
Route::delete('/expirations/{expiration}', [ExpirationController::class, 'destroy']);


// Recurring Invoices
Route::get('/recurring-invoices', [RecurringInvoiceController::class, 'index']);
Route::post('/recurring-invoices', [RecurringInvoiceController::class, 'store']);
Route::put('/recurring-invoices/{recurringInvoice}', [RecurringInvoiceController::class, 'update']);
Route::delete('/recurring-invoices/{recurringInvoice}', [RecurringInvoiceController::class, 'destroy']);


// Settings
Route::get('/settings', [SettingsController::class, 'index']);
Route::put('/settings/profile', [SettingsController::class, 'updateProfile']);
Route::post('/settings/profile/avatar', [SettingsController::class, 'uploadAvatar']);
Route::put('/settings/appearance', [SettingsController::class, 'updateAppearance']);