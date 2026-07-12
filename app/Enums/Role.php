<?php

namespace App\Enums;

enum Role: string
{
    case USER = 'USER';
    case ADMIN = 'ADMIN';

    public function homeUrl(): string
    {
        return '/'.strtolower($this->value).'/dashboard';
    }
}
