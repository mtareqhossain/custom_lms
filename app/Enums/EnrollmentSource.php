<?php
namespace App\Enums;

enum EnrollmentSource: string
{
    case Free = 'free';
    case Purchase = 'purchase';
    case Business = 'business';
    case Admin = 'admin';
}
