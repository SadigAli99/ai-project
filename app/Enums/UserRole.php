<?php

namespace App\Enums;

enum UserRole : string{
    case USER = 'user';
    case AI = 'ai';
    case SYSTEM = 'system';
}
