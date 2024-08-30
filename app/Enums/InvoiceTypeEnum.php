<?php

namespace App\Enums;

enum InvoiceTypeEnum: string
{
    case PROJECT = 'project';
    case SUPPLIER = 'supplier';
    case BILL = 'bill';
}
