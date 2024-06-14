<?php

namespace App\Filament\Resources\Conveniados\ConveniadosResource\Pages;

use App\Filament\Resources\Conveniados\ConveniadosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConveniados extends EditRecord
{
    protected static string $resource = ConveniadosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
	
}
