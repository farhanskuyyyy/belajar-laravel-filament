<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Set;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\CategoryResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CategoryResource\RelationManagers;
use Filament\Forms\Components\Select;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag'; // custom icon menu
    protected static ?string $navigationLabel = 'Categories'; // custom name menu
    protected static ?string $navigationGroup = 'Shop'; // for grouping menu
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Information')->schema([
                        TextInput::make('name')->required()->live(onBlur: false)->unique()->afterStateUpdated(function (string $operation, $state, Set $set) {
                            // if ($operation !== 'create') {
                            //     return;
                            // }

                            $set('slug', Str::slug($state));
                        }),
                        TextInput::make('slug')->disabled()->dehydrated()->required()->unique(Category::class, 'slug', ignoreRecord: true),
                        MarkdownEditor::make('description')->columnSpan('full'),
                    ])->columns(2),
                ]),
                Group::make()->schema([
                    Section::make('Status')->schema([
                        Toggle::make('is_visible')->label("Visibility")->helperText('Enable or disable product visibility')->default(true),
                        Select::make('parent_id')->relationship('parent','name')
                    ]),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('parent.name')->label('Parent')->sortable()->searchable(),
                IconColumn::make('is_visible')->boolean()->sortable()->toggleable()->label('Visibility'),
                TextColumn::make('updated_at')->label('Updated Date')->date()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
