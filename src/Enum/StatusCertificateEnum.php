<?php

namespace App\Enum;

enum StatusCertificateEnum: string
{
    case PENDING = 'Pending';
    case APPROVED = 'Approved';
    case REJECTED = 'Rejected';
    case EXPIRED = 'Expired';
}
