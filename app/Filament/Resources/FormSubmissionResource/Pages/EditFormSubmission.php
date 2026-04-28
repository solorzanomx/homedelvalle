<?php

namespace App\Filament\Resources\FormSubmissionResource\Pages;

use App\Filament\Resources\FormSubmissionResource;
use Filament\Resources\Pages\EditRecord;

class EditFormSubmission extends EditRecord
{
    protected static string $resource = FormSubmissionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
