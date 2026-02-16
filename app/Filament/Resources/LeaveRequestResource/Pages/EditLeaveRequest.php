<?php

namespace App\Filament\Resources\LeaveRequestResource\Pages;

use App\Filament\Resources\LeaveRequestResource;
use App\Jobs\SendWhatsAppMessage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLeaveRequest extends EditRecord
{
    protected static string $resource = LeaveRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();
        
        // Only send if status is not pending
        if ($record->status !== 'pending' && $record->wasChanged('status')) {
            $phone = $record->user->phone_number;
            
            if ($phone) {
                $statusUpper = strtoupper($record->status);
                $message = "Halo {$record->user->name}, pengajuan cuti Anda ({$record->leaveType->name}) tanggal {$record->start_date->format('d/m/Y')} s/d {$record->end_date->format('d/m/Y')} telah *{$statusUpper}*.";
                
                if ($record->status === 'rejected' && $record->rejection_reason) {
                    $message .= "\nAlasan: {$record->rejection_reason}";
                }

                SendWhatsAppMessage::dispatch($phone, $message);
            }
        }
    }
}
