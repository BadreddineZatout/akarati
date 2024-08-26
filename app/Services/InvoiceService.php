<?php

namespace App\Services;

use App\Models\Invoice as ModelsInvoice;
use Illuminate\Support\Facades\Storage;
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

        $items = $invoice->items->map(function ($item) {
            return InvoiceItem::make($item->name)
                ->pricePerUnit($item->price);
        })->toArray();

        $file_name = $invoice->project->name."-invoice-$invoice->id";
        Invoice::make()
            ->seller($invoice_by)
            ->buyer($project)
            ->addItems($items)
            ->date($invoice->invoiced_at)
            ->dateFormat('d-m-Y')
            ->currencySymbol('DA')
            ->currencyCode('DZD')
            ->filename($file_name)
            ->save('public');

        return Storage::disk('public')->download("$file_name.pdf");
    }

    public function downloadSupplierInvoice(ModelsInvoice $invoice)
    {
        $project = new Party([
            'name' => $invoice->project->name,
        ]);

        $supplier = new Party([
            'name' => $invoice->invoicable->name,
            'custom_fields' => [
                'phone' => $invoice->invoicable->phone,
            ],
        ]);

        $items = $invoice->items->map(function ($item) {
            return InvoiceItem::make($item->name)
                ->pricePerUnit($item->price);
        })->toArray();

        $file_name = $invoice->project->name.'-invoice-'.$invoice->invoicable->name."-$invoice->id";
        Invoice::make()
            ->seller($supplier)
            ->buyer($project)
            ->addItems($items)
            ->date($invoice->invoiced_at)
            ->dateFormat('d-m-Y')
            ->currencySymbol('DA')
            ->currencyCode('DZD')
            ->filename($file_name)
            ->template('supplier')
            ->save('public');

        return Storage::disk('public')->download("$file_name.pdf");
    }

    public function downloadBill(ModelsInvoice $invoice)
    {
        $project = new Party([
            'name' => $invoice->project->name,
        ]);

        $items = $invoice->items->map(function ($item) {
            return InvoiceItem::make($item->name)
                ->pricePerUnit($item->price);
        })->toArray();

        $file_name = $invoice->project->name."-invoice-$invoice->id";
        Invoice::make()
            ->seller($project)
            ->buyer($project)
            ->sequence($invoice->id)
            ->addItems($items)
            ->date($invoice->invoiced_at)
            ->dateFormat('d-m-Y')
            ->currencySymbol('DA')
            ->currencyCode('DZD')
            ->filename($file_name)
            ->template('bill')
            ->save('public');

        return Storage::disk('public')->download("$file_name.pdf");
    }
}
