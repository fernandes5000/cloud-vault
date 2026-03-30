<?php

namespace App\Enums;

enum UploadSessionStatus: string
{
    case Pending = 'pending';
    case Uploading = 'uploading';
    case Assembling = 'assembling';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}
