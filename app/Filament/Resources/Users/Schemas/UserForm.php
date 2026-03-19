<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                ->schema([
                    TextInput::make('name')
                        ->required(),

                    TextInput::make('email')
                        ->label('Email address')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true),
                    Select::make('roles')
					->relationship('roles', 'name')
					->preload()
					->searchable(),
                ])->columns(3)->columnSpanFull(),
                Section::make('Password')
                    ->schema([
                        // Checkbox to toggle password update
                        Checkbox::make('change_password')
                            ->label('Change Password?')
                            ->reactive(),

                        // Only show when editing and checkbox is true
                        TextInput::make('current_password')
                            ->label('Current Password')
                            ->password()
                            ->required(fn (Get $get, $record) => $record && $get('change_password'))
                            ->visible(fn (Get $get, $record) => $record && $get('change_password'))
                            ->rule(function (Get $get, $record) {
                                return function (string $attribute, $value, \Closure $fail) use ($record) {
                                    if ($record && $get('change_password')) {
                                        if (!Hash::check($value, $record->password)) {
                                            $fail('The current password is incorrect.');
                                        }
                                    }
                                };
                            }),

                        // New password field
                        TextInput::make('password')
                            ->label(fn ($record) => $record ? 'New Password' : 'Password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->required(fn ($record, Get $get) => !$record || $get('change_password'))
                            ->visible(fn ($record, Get $get) => !$record || $get('change_password')),

                        // Confirm password
                        TextInput::make('password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->same('password')
                            ->required(fn ($record, Get $get) => !$record || $get('change_password'))
                            ->visible(fn ($record, Get $get) => !$record || $get('change_password')),
                ])->columns(4)->columnSpanFull(),
            ]);
    }
}
