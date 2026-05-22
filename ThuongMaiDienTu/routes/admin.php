<?php

use App\Http\Controllers\Admin\RepairTicketInvoiceController;
use App\Http\Controllers\Admin\ServiceInvoiceController;
use Illuminate\Support\Facades\Route;

Route::resource('service-invoices', ServiceInvoiceController::class)->only(['index', 'create', 'store', 'show']);
Route::get('service-invoices/{serviceInvoice}/print', [ServiceInvoiceController::class, 'print'])
    ->name('service-invoices.print');
Route::get('service-invoices/{serviceInvoice}/pdf', [ServiceInvoiceController::class, 'pdf'])
    ->name('service-invoices.pdf');
Route::get('service-invoices/{serviceInvoice}/pdf/open', [ServiceInvoiceController::class, 'openPdf'])
    ->name('service-invoices.pdf.open');
Route::get('service-invoices/{serviceInvoice}/pdf/save', [ServiceInvoiceController::class, 'savePdf'])
    ->name('service-invoices.pdf.save');
Route::get('service-invoices/{serviceInvoice}/pdf/download', [ServiceInvoiceController::class, 'downloadSavedPdf'])
    ->name('service-invoices.pdf.download');

// Repair Tickets CRUD
Route::get('repair-tickets', [RepairTicketInvoiceController::class, 'index'])->name('repair-tickets.index');
Route::get('repair-tickets/create', [RepairTicketInvoiceController::class, 'createTicket'])->name('repair-tickets.create');
Route::post('repair-tickets', [RepairTicketInvoiceController::class, 'storeTicket'])->name('repair-tickets.store');
Route::get('repair-tickets/{repairTicket}/edit', [RepairTicketInvoiceController::class, 'editTicket'])->name('repair-tickets.edit');
Route::put('repair-tickets/{repairTicket}', [RepairTicketInvoiceController::class, 'updateTicket'])->name('repair-tickets.update');
Route::delete('repair-tickets/{repairTicket}', [RepairTicketInvoiceController::class, 'destroyTicket'])->name('repair-tickets.destroy');
Route::get('repair-tickets/search-phone', [RepairTicketInvoiceController::class, 'searchByPhone'])->name('repair-tickets.search-phone');

// Invoice for Repair Tickets
Route::get('repair-tickets/{repairTicket}/invoice/create', [RepairTicketInvoiceController::class, 'create'])
    ->name('repair-tickets.invoice.create');
Route::post('repair-tickets/invoice', [RepairTicketInvoiceController::class, 'store'])
    ->name('repair-tickets.invoice.store');
