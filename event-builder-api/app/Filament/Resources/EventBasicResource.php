<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventBasicResource\Pages;
use App\Filament\Resources\EventBasicResource\RelationManagers;
use App\Models\EventBasic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class EventBasicResource extends Resource
{
    protected static ?string $model = EventBasic::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('client_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('event_title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('event_location')
                    ->required()
                    ->maxLength(255),
                Radio::make('venue_type')
                    ->options([
                        'hotel' => '호텔',
                        'convention_center' => '컨벤션 센터',
                        'unique_venue' => '독특한 장소',
                        'online' => '온라인',
                        'hybrid' => '하이브리드',
                    ])
                    ->required(),
                Repeater::make('zones')
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('type')->required()->maxLength(255),
                        TextInput::make('quantity')->numeric()->required(),
                    ])
                    ->columns(3)
                    ->defaultItems(1)
                    ->createItemButtonLabel('존 추가'),
                Forms\Components\Grid::make(2)
                    ->schema([
                        TextInput::make('total_budget')
                            ->numeric()
                            ->nullable()
                            ->prefix('₩')
                            ->suffix('원')
                            ->visible(fn (Forms\Get $get) => !$get('is_total_budget_undecided')),
                        Toggle::make('is_total_budget_undecided')
                            ->label('총 예산 미정')
                            ->reactive(),
                    ]),
                Forms\Components\Grid::make(2)
                    ->schema([
                        DatePicker::make('event_start_date_range_min')
                            ->label('행사 시작일 (최소)')
                            ->nullable(),
                        DatePicker::make('event_start_date_range_max')
                            ->label('행사 시작일 (최대)')
                            ->nullable(),
                    ]),
                Forms\Components\Grid::make(2)
                    ->schema([
                        DatePicker::make('event_end_date_range_min')
                            ->label('행사 종료일 (최소)')
                            ->nullable(),
                        DatePicker::make('event_end_date_range_max')
                            ->label('행사 종료일 (최대)')
                            ->nullable(),
                    ]),
                TextInput::make('event_duration_days')
                    ->numeric()
                    ->nullable()
                    ->label('행사 기간 (일)'),
                DatePicker::make('setup_start_date')
                    ->label('설치 시작일')
                    ->nullable(),
                DatePicker::make('teardown_end_date')
                    ->label('철거 종료일')
                    ->nullable(),
                DatePicker::make('project_kickoff_date')
                    ->label('프로젝트 시작일')
                    ->required(),
                DatePicker::make('settlement_close_date')
                    ->label('정산 마감일')
                    ->required(),
                Forms\Components\TextInput::make('contact_person_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('contact_person_contact')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('admin_person_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('admin_person_contact')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client_name')->searchable()->sortable(),
                TextColumn::make('event_title')->searchable()->sortable(),
                TextColumn::make('event_location')->searchable()->sortable(),
                TextColumn::make('venue_type')->searchable()->sortable(),
                TextColumn::make('total_budget')
                    ->money('KRW')
                    ->toggleable(isToggledHiddenByDefault: false),
                IconColumn::make('is_total_budget_undecided')
                    ->boolean()
                    ->label('예산 미정')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('event_start_date_range_min')
                    ->date()
                    ->label('시작일 (최소)')
                    ->sortable(),
                TextColumn::make('event_end_date_range_max')
                    ->date()
                    ->label('종료일 (최대)')
                    ->sortable(),
                TextColumn::make('project_kickoff_date')
                    ->date()
                    ->label('프로젝트 시작일')
                    ->sortable(),
                TextColumn::make('settlement_close_date')
                    ->date()
                    ->label('정산 마감일')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListEventBasics::route('/'),
            'create' => Pages\CreateEventBasic::route('/create'),
            'edit' => Pages\EditEventBasic::route('/{record}/edit'),
        ];
    }
}
