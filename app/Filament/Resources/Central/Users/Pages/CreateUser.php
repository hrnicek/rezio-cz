<?php

namespace App\Filament\Resources\Central\Users\Pages;

use App\Filament\Resources\Central\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
