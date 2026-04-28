<?php

namespace App\Filament\Resources\FormSubmissionResource\Pages;

use App\Filament\Resources\FormSubmissionResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListFormSubmissions extends ListRecords
{
    protected static string $resource = FormSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FormSubmissionResource\Widgets\FormSubmissionStats::class,
        ];
    }
}
