<?php

namespace App\Filament\Resources\PromotionResource\Pages;

use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;

class CustomDelete extends DeleteAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('custom_delete'); // Unique name for the action

        // Add a custom URL for the delete action if needed
        $this->url(fn ($record) => route('promotions.destroy', ['promotion' => $record->id]));

        // Perform actions before deletion
        $this->before(function ($record) {
            // Add any pre-deletion logic here
        });

        // Perform actions after deletion
        $this->after(function ($record) {
            // Add your custom redirect logic here
            Notification::make()
                ->success()
                ->title('Deleted');
        });
    }
}
