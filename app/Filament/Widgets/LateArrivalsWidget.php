<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LateArrivalsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\User::query()
                    ->withCount(['attendance as late_count' => function ($query) {
                        $query->where('is_late', true)
                              ->whereMonth('date', now()->month)
                              ->whereYear('date', now()->year);
                    }])
                    ->orderByDesc('late_count')
                    ->having('late_count', '>', 0) // Only show if at least 1 late
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Employee'))
                    ->description(fn ($record) => $record->employee->department->name ?? '-'),
                Tables\Columns\TextColumn::make('late_count')
                    ->label(__('Late Arrivals (This Month)'))
                    ->badge()
                    ->color('danger'),
            ]);
    }
}
