<?php

namespace App\Services;

use App\Models\Invoice as ModelsInvoice;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Invoice;

class InvoiceService
{
    public function downloadInvoice(ModelsInvoice $invoice)
    {
        $project = new Party([
            'name' => $invoice->project->name,
        ]);

        $invoice_by = new Party([
            'name' => $invoice->invoicable->name,
            'custom_fields' => [
                'email' => $invoice->invoicable->email,
            ],
        ]);

        $bill = Invoice::make()
            ->seller($invoice_by)
            ->buyer($project)
            ->addItem(
                InvoiceItem::make("Invoice #$invoice->id")
                    ->pricePerUnit($invoice->amount)
            )
            ->date($invoice->invoiced_at)
            ->dateFormat('d-m-Y')
            ->currencyCode('DZD')
            ->filename($invoice->project->name.'-invoice-'.$invoice->id)
            ->save('public');
        dd($bill->url());
    }

    public function downloadSupplierInvoice(ModelsInvoice $invoice) {}

    public function downloadBill(ModelsInvoice $invoice) {}
}
