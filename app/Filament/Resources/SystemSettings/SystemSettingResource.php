<?php

namespace App\Filament\Resources\SystemSettings;

use App\Filament\Resources\SystemSettings\Pages\CreateSystemSetting;
use App\Filament\Resources\SystemSettings\Pages\EditSystemSetting;
use App\Filament\Resources\SystemSettings\Pages\ListSystemSettings;
use App\Filament\Resources\SystemSettings\Schemas\SystemSettingForm;
use App\Filament\Resources\SystemSettings\Tables\SystemSettingsTable;
use App\Models\SystemSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SystemSettingResource extends Resource
{
    protected static ?string $model = SystemSetting::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static string | \UnitEnum | null $navigationGroup = 'Settings';
    
    protected static ?int $navigationSort = 100;

    protected static ?string $recordTitleAttribute = 'label';

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return SystemSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SystemSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSystemSettings::route('/'),
            'create' => CreateSystemSetting::route('/create'),
            'edit' => EditSystemSetting::route('/{record}/edit'),
        ];
    }
}
