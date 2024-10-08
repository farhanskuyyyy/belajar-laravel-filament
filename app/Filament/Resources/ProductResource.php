<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Enums\ProductTypeEnum;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductResource\RelationManagers;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Model;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge'; // custom icon menu
    protected static ?string $navigationLabel = 'Products'; // custom name menu
    protected static ?string $navigationGroup = 'Shop'; // for grouping menu
    protected static ?int $navigationSort = 2;
    // protected static ?string $recordTitleAttribute = 'name'; // for setup global search
    protected static int $globalSearchResultsLimit = 20; // limit global search


    // for setup global search
    public static function getGloballySearchableAttributes(): array
    {
        return ['name','slug','description'];
    }

    // for setup global search detail
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Brand' => $record->brand->name
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return 'NEW';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Information')->schema([
                        TextInput::make('name')->required()->live()->debounce(1000)->unique()->afterStateUpdated(function (string $operation, $state, Set $set) {
                            // if ($operation !== 'create') {
                            //     return;
                            // }

                            $set('slug', Str::slug($state));
                        }),
                        TextInput::make('slug')->disabled()->dehydrated()->required()->unique(Product::class, 'slug', ignoreRecord: true),
                        MarkdownEditor::make('description')->columnSpan('full'),
                    ])->columns(2),
                    Section::make('Pricing & Inventory')->schema([
                        TextInput::make('sku')->label('SKU (Stock Keeping Unit)')->unique()->required(),
                        TextInput::make('price')->numeric()->rules('regex:/^\d{1,6}(\.\d{0,2})?$/')->required(),
                        TextInput::make('quantity')->numeric()->minValue(0)->required(),
                        Select::make('type')->options([
                            'downloadable' => ProductTypeEnum::DOWNLOADABLE->value,
                            'deliverable' => ProductTypeEnum::DELIVERATBLE->value,
                        ]),
                    ])->columns(2)
                ]),
                Group::make()->schema([
                    Section::make('Status')->schema([
                        Toggle::make('is_visible')->label("Visibility")->helperText('Enable or disable product visibility')->default(true),
                        Toggle::make('is_featured')->label("Featured")->helperText('Enable or disable featured status'),
                        DatePicker::make('published_at')->label('Availability')->default(now())
                    ]),
                    Section::make('Image')->schema([
                        FileUpload::make('image')->directory('form-attachment')->preserveFilenames()->image()->imageEditor()->required(),
                    ])->collapsible(),
                    Section::make('Associations')->schema([
                        Select::make('brand_id')->relationship('brand', 'name')->required(),
                        Select::make('categories')->relationship('categories','name')->multiple()->required()
                    ])->collapsible(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('brand.name')->searchable()->sortable()->toggleable(),
                Tables\Columns\IconColumn::make('is_visible')->boolean()->sortable()->toggleable()->label('Visibility'),
                Tables\Columns\TextColumn::make('price')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('quantity')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('published_at')->date()->sortable(),
                Tables\Columns\TextColumn::make('type'),
            ])
            ->filters([
                TernaryFilter::make('is_visible')->label('Visibility')->boolean()
                    ->trueLabel('Only Visible Products')
                    ->falseLabel('Only Hidden Products')
                    ->native(false),

                SelectFilter::make('brand')->relationship('brand', 'name')
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
