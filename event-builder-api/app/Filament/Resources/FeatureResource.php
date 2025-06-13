<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeatureResource\Pages;
use App\Filament\Resources\FeatureResource\RelationManagers;
use App\Models\Feature;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FeatureResource extends Resource
{
    protected static ?string $model = Feature::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    
    protected static ?string $navigationLabel = '기능 관리';
    
    protected static ?string $modelLabel = '기능';
    
    protected static ?string $pluralModelLabel = '기능';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('기본 정보')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('기능명')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                            
                        Forms\Components\Select::make('category_id')
                            ->label('카테고리')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('카테고리명')
                                    ->required(),
                                Forms\Components\TextInput::make('slug')
                                    ->label('슬러그')
                                    ->required(),
                                Forms\Components\Textarea::make('description')
                                    ->label('설명'),
                            ])
                            ->columnSpan(1),
                            
                        Forms\Components\TextInput::make('icon')
                            ->label('아이콘')
                            ->helperText('Heroicon 아이콘명 또는 이미지 URL')
                            ->columnSpan(1),
                            
                        Forms\Components\TextInput::make('sort_order')
                            ->label('정렬 순서')
                            ->numeric()
                            ->default(0)
                            ->helperText('숫자가 작을수록 먼저 표시됩니다.')
                            ->columnSpan(1),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('설명')
                            ->rows(3)
                            ->columnSpanFull(),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('활성화')
                            ->default(true)
                            ->helperText('비활성화하면 사용자에게 표시되지 않습니다.')
                            ->columnSpan(1),
                            
                        Forms\Components\Toggle::make('is_premium')
                            ->label('프리미엄')
                            ->default(false)
                            ->helperText('프리미엄 기능으로 설정하면 별도 표시됩니다.')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('budget_allocation')
                            ->label('예산 배정')
                            ->default(false)
                            ->helperText('이 기능에 예산을 배정할지 여부입니다.')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('internal_resource_flag')
                            ->label('내부 리소스')
                            ->default(false)
                            ->helperText('이 기능이 내부 리소스를 포함하는지 여부입니다.')
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('추천 설정')
                    ->schema([
                        Forms\Components\Repeater::make('recommendations')
                            ->label('추천 기능')
                            ->relationship('recommendations')
                            ->schema([
                                Forms\Components\Select::make('recommended_feature_id')
                                    ->label('추천 기능')
                                    ->options(Feature::where('is_active', true)->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('level')
                                    ->label('추천 레벨')
                                    ->options([
                                        'R1' => '1차 추천 (필수)',
                                        'R2' => '2차 추천 (선택)',
                                    ])
                                    ->default('R1')
                                    ->required(),
                                Forms\Components\TextInput::make('priority')
                                    ->label('우선순위')
                                    ->numeric()
                                    ->nullable()
                                    ->default(0)
                                    ->helperText('숫자가 작을수록 먼저 표시됩니다.'),
                            ])
                            ->columns(3)
                            ->addActionLabel('추천 기능 추가')
                            ->itemLabel(fn (array $state): ?string => Feature::find($state['recommended_feature_id'])?->name ?? null)
                            ->collapsible()
                            ->helperText('이 기능과 함께 추천할 다른 기능들을 선택하세요. R1은 기본 선택, R2는 추가 추천으로 표시됩니다.'),
                    ])
                    ->collapsible(),
                    
                Forms\Components\Section::make('입력 필드 설정')
                    ->schema([
                        Forms\Components\Placeholder::make('field_guide')
                            ->label('')
                            ->content('💡 **필드 구조 가이드:**
                            
• **상위 질문**: 설정 방식을 선택하는 라디오/셀렉트 필드 (예: "참석자 규모 설정방식")
• **하위 질문**: 상위 선택에 따라 조건부로 표시되는 세부 입력 필드들
• **필드 순서**: 상위 질문 → 관련 하위 질문들을 순서대로 배치'),
                            
                        Forms\Components\Repeater::make('config.fields')
                            ->label('입력 필드')
                            ->schema([
                                Forms\Components\Select::make('field_level')
                                    ->label('필드 레벨')
                                    ->options([
                                        'parent' => '🔵 상위 질문 (설정 방식 선택)',
                                        'child' => '🔸 하위 질문 (조건부 표시)',
                                        'independent' => '⚪ 독립 필드'
                                    ])
                                    ->default('independent')
                                    ->live()
                                    ->columnSpan(1),
                                    
                                Forms\Components\TextInput::make('parent_field')
                                    ->label('상위 필드 키')
                                    ->placeholder('예: participants_type')
                                    ->helperText('이 필드가 의존하는 상위 필드의 key 값')
                                    ->visible(fn (Forms\Get $get) => $get('field_level') === 'child')
                                    ->columnSpan(1),
                                    
                                Forms\Components\TextInput::make('show_when_value')
                                    ->label('표시 조건 값')
                                    ->placeholder('예: exact, range')
                                    ->helperText('상위 필드가 이 값일 때만 표시')
                                    ->visible(fn (Forms\Get $get) => $get('field_level') === 'child')
                                    ->columnSpan(1),
                                    
                                Forms\Components\TextInput::make('name')
                                    ->label('항목명')
                                    ->required()
                                    ->placeholder('예: 참석자수')
                                    ->columnSpan(1),
                                    
                                Forms\Components\TextInput::make('key')
                                    ->label('영문 항목명')
                                    ->required()
                                    ->placeholder('예: participants')
                                    ->helperText('영문, 숫자, 언더스코어만 사용')
                                    ->regex('/^[a-zA-Z0-9_]+$/')
                                    ->columnSpan(1),
                                    
                                Forms\Components\TextInput::make('unit')
                                    ->label('단위')
                                    ->placeholder('예: 명, 시간, 개')
                                    ->columnSpan(1),
                                    
                                Forms\Components\Select::make('type')
                                    ->label('입력 타입')
                                    ->options([
                                        'text' => '텍스트',
                                        'number' => '숫자',
                                        'textarea' => '긴 텍스트',
                                        'select' => '드롭다운 선택',
                                        'radio' => '단일 선택 (라디오)',
                                        'checkbox' => '복수 선택 (체크박스)',
                                        'date' => '날짜',
                                        'time' => '시간',
                                        'datetime' => '날짜시간',
                                    ])
                                    ->default('text')
                                    ->required()
                                    ->live()
                                    ->columnSpan(1),
                                    
                                Forms\Components\TextInput::make('placeholder')
                                    ->label('힌트 텍스트')
                                    ->placeholder('사용자에게 보여줄 힌트')
                                    ->columnSpan(2),
                                    
                                // 선택형 필드일 때만 표시되는 옵션 설정
                                Forms\Components\Section::make('선택 옵션 설정')
                                    ->schema([
                                        Forms\Components\Repeater::make('options')
                                            ->label('선택 옵션')
                                            ->schema([
                                                Forms\Components\TextInput::make('label')
                                                    ->label('표시명')
                                                    ->required()
                                                    ->placeholder('사용자에게 보여질 텍스트')
                                                    ->columnSpan(1),
                                                    
                                                Forms\Components\TextInput::make('value')
                                                    ->label('값')
                                                    ->required()
                                                    ->placeholder('실제 저장될 값')
                                                    ->columnSpan(1),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('옵션 추가')
                                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                                            ->collapsible()
                                            ->helperText('사용자가 선택할 수 있는 옵션들을 정의하세요'),
                                            
                                        Forms\Components\Toggle::make('multiple')
                                            ->label('복수 선택 허용')
                                            ->default(false)
                                            ->helperText('체크박스 타입에서만 적용됩니다')
                                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['checkbox'])),
                                    ])
                                    ->visible(fn (Forms\Get $get) => in_array($get('type'), ['select', 'radio', 'checkbox']))
                                    ->columnSpanFull(),
                                    
                                Forms\Components\Toggle::make('required')
                                    ->label('필수 입력')
                                    ->default(false)
                                    ->columnSpan(1),
                                    
                                Forms\Components\Toggle::make('show_unit')
                                    ->label('단위 표시')
                                    ->default(true)
                                    ->helperText('입력 필드 옆에 단위를 표시할지 선택')
                                    ->columnSpan(1),
                                Forms\Components\Toggle::make('allow_undecided')
                                    ->label('미정 허용')
                                    ->default(false)
                                    ->helperText('이 필드에 "미정" 옵션을 허용할지 여부입니다.')
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->addActionLabel('필드 추가')
                            ->helperText('사용자가 RFP 작성 시 입력할 수 있는 필드들을 정의하세요.'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('기능명')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('category.name')
                    ->label('카테고리')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\ImageColumn::make('icon')
                    ->label('아이콘')
                    ->defaultImageUrl(function ($record) {
                        if (str_starts_with($record->icon ?? '', 'heroicon-')) {
                            return null;
                        }
                        return $record->icon;
                    })
                    ->size(40)
                    ->circular(),
                    
                Tables\Columns\TextColumn::make('description')
                    ->label('설명')
                    ->limit(30)
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
                    
                Tables\Columns\IconColumn::make('is_premium')
                    ->label('프리미엄')
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
                Tables\Columns\TextColumn::make('recommendations_count')
                    ->label('추천 수')
                    ->counts('recommendations')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success'),
                    
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
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('카테고리')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('활성화 상태')
                    ->boolean()
                    ->trueLabel('활성화된 기능만')
                    ->falseLabel('비활성화된 기능만')
                    ->native(false),
                    
                Tables\Filters\TernaryFilter::make('is_premium')
                    ->label('프리미엄 여부')
                    ->boolean()
                    ->trueLabel('프리미엄 기능만')
                    ->falseLabel('일반 기능만')
                    ->native(false),
                Tables\Filters\TernaryFilter::make('budget_allocation')
                    ->label('예산 배정 여부')
                    ->boolean()
                    ->trueLabel('예산 배정 기능만')
                    ->falseLabel('예산 미배정 기능만')
                    ->native(false),
                Tables\Filters\TernaryFilter::make('internal_resource_flag')
                    ->label('내부 리소스 여부')
                    ->boolean()
                    ->trueLabel('내부 리소스 기능만')
                    ->falseLabel('외부 리소스 기능만')
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

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['category'])
            ->withCount(['recommendations'])
            ->select('features.*');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeatures::route('/'),
            'create' => Pages\CreateFeature::route('/create'),
            'edit' => Pages\EditFeature::route('/{record}/edit'),
        ];
    }
}
