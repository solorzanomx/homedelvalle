<?php

namespace App\Filament\Resources\FormSubmissionResource\Widgets;

use App\Models\FormSubmission;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FormSubmissionStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Leads Nuevos Hoy', FormSubmission::where('created_at', '>=', now()->startOfDay())->count())
                ->description('Desde hoy')
                ->color('danger'),
            Stat::make('Sin Contactar', FormSubmission::where('status', 'new')->count())
                ->description('En espera')
                ->color('warning'),
            Stat::make('Contactados', FormSubmission::where('status', '!=', 'new')->count())
                ->description('En seguimiento')
                ->color('success'),
            Stat::make('Ganados', FormSubmission::where('status', 'won')->count())
                ->description('Conversiones')
                ->color('info'),
        ];
    }
}
