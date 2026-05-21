<?php

use App\Http\Controllers\Admin\RepairTicketInvoiceController;
use App\Http\Controllers\Admin\ServiceInvoiceController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
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
    Route::get('repair-tickets/{repairTicket}/invoice/create', [RepairTicketInvoiceController::class, 'create'])
        ->name('repair-tickets.invoice.create');
    Route::post('repair-tickets/invoice', [RepairTicketInvoiceController::class, 'store'])
        ->name('repair-tickets.invoice.store');
});
