<?php

namespace App\Services;

use App\Models\Invoice as ModelsInvoice;
use App\Models\Project;
use App\Models\Promotion;
use App\Models\Supplier;
use Illuminate\Support\Facades\Storage;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Invoice;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function downloadGlobalSupplierInvoice(Supplier $supplier): StreamedResponse
    {
        $supplierParty = new Party([
            'name' => $supplier->name,
            'custom_fields' => [
                'phone' => $supplier->phone,
                'email' => $supplier->email,
            ],
        ]);

        $items = collect();
        foreach ($supplier->invoices as $invoice) {
            foreach ($invoice->items as $item) {
                $description = "Project: {$invoice->project->name} ,Promotion: {$invoice->promotion->name}";

                $items->push(
                    InvoiceItem::make($item->name)
                        ->description($description)
                        ->pricePerUnit($item->price)
                );
            }
        }

        $file_name = "global-invoice-supplier-{$supplier->id}";

        Invoice::make()
            ->seller($supplierParty)
            ->buyer(new Party(['name' => 'Global']))
            ->addItems($items->toArray())
            ->date(now())
            ->dateFormat('d-m-Y')
            ->currencySymbol('DA')
            ->currencyCode('DZD')
            ->filename($file_name)
            ->template('supplier')
            ->save('public');

        return Storage::disk('public')->download("$file_name.pdf");
    }

    public function downloadGlobalProjectInvoice(Project $project): StreamedResponse
    {

        $projectParty = new Party([
            'name' => $project->name,
        ]);

        $items = collect();
        $project->load(['invoices' => function ($query) {
            $query->where('type', '!=', 'bill');
        }]);
        foreach ($project->invoices as $invoice) {
            foreach ($invoice->items as $item) {
                $description = ($invoice->type == 'supplier' ? "Supplier: {$invoice->invoicable?->name}" : "Client: {$invoice->invoicable?->name}")." ,Promotion: {$invoice->promotion->name}";

                $items->push(
                    InvoiceItem::make($item->name)
                        ->description($description)
                        ->pricePerUnit($item->price)
                );
            }
        }

        $file_name = "global-invoice-project-{$project->id}";

        Invoice::make()
            ->seller(new Party(['name' => 'Global']))
            ->buyer($projectParty)
            ->addItems($items->toArray())
            ->date(now())
            ->dateFormat('d-m-Y')
            ->currencySymbol('DA')
            ->currencyCode('DZD')
            ->filename($file_name)
            ->save('public');

        return Storage::disk('public')->download("$file_name.pdf");
    }

    public function downloadGlobalPromotionInvoice(Promotion $promotion): StreamedResponse
    {

        $projectParty = new Party([
            'name' => $promotion->name,
        ]);

        $items = collect();
        $promotion->load(['invoices' => function ($query) {
            $query->where('type', '!=', 'bill');
        }]);
        foreach ($promotion->invoices as $invoice) {
            foreach ($invoice->items as $item) {
                $description = ($invoice->type == 'supplier' ? "Supplier: {$invoice->invoicable?->name}" : "Client: {$invoice->invoicable?->name}")." ,Block: {$invoice->block->name} ,Project: {$invoice->block->project->name}";

                $items->push(
                    InvoiceItem::make($item->name)
                        ->description($description)
                        ->pricePerUnit($item->price)
                );
            }
        }

        $file_name = "global-invoice-promotion-{$promotion->id}";

        Invoice::make()
            ->seller(new Party(['name' => 'Global']))
            ->buyer($projectParty)
            ->addItems($items->toArray())
            ->date(now())
            ->dateFormat('d-m-Y')
            ->currencySymbol('DA')
            ->currencyCode('DZD')
            ->filename($file_name)
            ->save('public');

        return Storage::disk('public')->download("$file_name.pdf");
    }
}
