<?php

namespace App\Filament\Resources;

use App\Enums\ProductTypeEnum;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack'; // custom icon menu
    protected static ?string $navigationLabel = 'Products'; // custom name menu
    protected static ?string $navigationGroup = 'Shop'; // for grouping menu

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Information')->schema([
                        TextInput::make('name'),
                        TextInput::make('slug'),
                        MarkdownEditor::make('description')->columnSpan('full'),
                    ])->columns(2),
                    Section::make('Pricing & Inventory')->schema([
                        TextInput::make('sku'),
                        TextInput::make('price'),
                        TextInput::make('quantity'),
                        Select::make('type')->options([
                            'downloadable' => ProductTypeEnum::DOWNLOADABLE->value,
                            'deliverable' => ProductTypeEnum::DELIVERATBLE->value,
                        ]),
                    ])->columns(2)
                ]),
                Group::make()->schema([
                    Section::make('Status')->schema([
                        Toggle::make('is_visible'),
                        Toggle::make('is_featured'),
                        DatePicker::make('published_at')
                    ]),
                    Section::make('Image')->schema([
                        FileUpload::make('image'),
                    ])->collapsible(),
                    Section::make('Associations')->schema([
                        Select::make('brand_id')->relationship('brand','name'),
                    ])->collapsible(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('brand.name'),
                Tables\Columns\IconColumn::make('is_visible')->boolean(),
                Tables\Columns\TextColumn::make('price'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('published_at'),
                Tables\Columns\TextColumn::make('type'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
