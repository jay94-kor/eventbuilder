<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeatureCategoryResource\Pages;
use App\Filament\Resources\FeatureCategoryResource\RelationManagers;
use App\Models\FeatureCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FeatureCategoryResource extends Resource
{
    protected static ?string $model = FeatureCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';
    
    protected static ?string $navigationLabel = '카테고리 관리';
    
    protected static ?string $modelLabel = '카테고리';
    
    protected static ?string $pluralModelLabel = '카테고리';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('카테고리 기본 정보')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('카테고리명')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $context, $state, Forms\Set $set) {
                                if ($context === 'create') {
                                    $set('slug', \Illuminate\Support\Str::slug($state));
                                }
                            }),
                            
                        Forms\Components\TextInput::make('slug')
                            ->label('슬러그')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL에 사용되는 고유 식별자입니다.'),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('설명')
                            ->rows(3)
                            ->columnSpanFull(),
                            
                        Forms\Components\TextInput::make('sort_order')
                            ->label('정렬 순서')
                            ->numeric()
                            ->default(0)
                            ->helperText('숫자가 작을수록 먼저 표시됩니다.'),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('활성화')
                            ->default(true)
                            ->helperText('비활성화하면 사용자에게 표시되지 않습니다.'),
                        Forms\Components\Toggle::make('budget_allocation')
                            ->label('예산 배정')
                            ->default(false)
                            ->helperText('이 카테고리에 예산을 배정할지 여부입니다.'),
                        Forms\Components\Toggle::make('internal_resource_flag')
                            ->label('내부 리소스')
                            ->default(false)
                            ->helperText('이 카테고리가 내부 리소스를 포함하는지 여부입니다.'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('카테고리명')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('slug')
                    ->label('슬러그')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('슬러그가 복사되었습니다!')
                    ->badge(),
                    
                Tables\Columns\TextColumn::make('description')
                    ->label('설명')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        
                        return $state;
                    }),
                    
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('정렬순서')
                    ->sortable()
                    ->alignCenter(),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('활성화')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('budget_allocation')
                    ->label('예산 배정')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('internal_resource_flag')
                    ->label('내부 리소스')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('features_count')
                    ->label('기능 수')
                    ->counts('features')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('생성일')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('수정일')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('활성화 상태')
                    ->boolean()
                    ->trueLabel('활성화된 카테고리만')
                    ->falseLabel('비활성화된 카테고리만')
                    ->native(false),
                Tables\Filters\TernaryFilter::make('budget_allocation')
                    ->label('예산 배정 여부')
                    ->boolean()
                    ->trueLabel('예산 배정 카테고리만')
                    ->falseLabel('예산 미배정 카테고리만')
                    ->native(false),
                Tables\Filters\TernaryFilter::make('internal_resource_flag')
                    ->label('내부 리소스 여부')
                    ->boolean()
                    ->trueLabel('내부 리소스 카테고리만')
                    ->falseLabel('외부 리소스 카테고리만')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('보기'),
                Tables\Actions\EditAction::make()->label('수정'),
                Tables\Actions\DeleteAction::make()->label('삭제'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('선택 삭제'),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
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
            'index' => Pages\ListFeatureCategories::route('/'),
            'create' => Pages\CreateFeatureCategory::route('/create'),
            'edit' => Pages\EditFeatureCategory::route('/{record}/edit'),
        ];
    }
}
