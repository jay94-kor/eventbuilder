<?php

namespace App\Filament\Resources\EventBasicResource\Pages;

use App\Filament\Resources\EventBasicResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEventBasics extends ListRecords
{
    protected static string $resource = EventBasicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
