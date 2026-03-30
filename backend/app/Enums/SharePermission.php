<?php

namespace App\Enums;

enum SharePermission: string
{
    case View = 'view';
    case Download = 'download';
}
