<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Placeholder;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $pluralModelLabel = 'Clientes';
    protected static ?string $navigationGroup = 'Administração';
    protected static ?string $title = 'Cliente';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Cliente') // Título da seção
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome do Cliente')
                            ->required()
                            ->maxLength(255),
                        // Repeater para Endereços
                        Forms\Components\Repeater::make('addresses') // 'addresses' será o nome da relação no modelo Client
                            ->label('Endereços')
                            ->relationship('addresses') // Relacionamento com o modelo Address
                            ->schema([
                                Forms\Components\TextInput::make('cep')
                                    ->label('CEP')
                                    ->mask('99999-999')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('estado')
                                    ->label('Estado')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('cidade')
                                    ->label('Cidade')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('bairro')
                                    ->label('Bairro')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('rua')
                                    ->label('Rua')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('numero')
                                    ->label('Número')
                                    ->numeric() 
                                    ->maxLength(10),
                                Forms\Components\TextInput::make('complemento')
                                    ->label('Complemento')
                                    ->maxLength(255)
                                    ->nullable(),
                            ])
                            ->itemLabel(
                                fn(array $state): string => 'Novo Endereço'
                            )
                            ->itemLabel(function (array $state): ?string {
                                
                                $parts = [];
                                $line1 = implode(', ', array_filter([
                                    $state['rua'] ?? null,
                                    $state['numero'] ?? null,
                                    $state['complemento'] ?? null,
                                    $state['bairro'] ?? null,
                                ]));

                                if ($line1) {
                                    $parts[] = $line1;
                                }

                                $line2 = implode(', ', array_filter([
                                    $state['cidade'] ?? null,
                                    $state['cep'] ?? null,
                                ]));

                                if ($line2) {
                                    $parts[] = $line2;
                                }

                                // Retorna o rótulo formatado. Se estiver vazio, retorna um padrão.
                                return implode('  -  ', $parts) ?: 'Novo Endereço';
                            })
                            ->columns(1) // Dois campos por linha no repeater
                            ->collapsed() // Começa recolhido
                            ->cloneable(false) // Nao Permite duplicar entradas
                            ->defaultItems(1) // Começa com um item
                            ->addActionLabel('Novo endereço'), // Rótulo do botão de adicionar

                        // Repeater para Telefones
                        Forms\Components\Repeater::make('phones') // 'phones' é o nome da relação no modelo Client
                            ->label('Telefones')
                            ->relationship('phones') // Relacionamento com o modelo Phone
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Título (Ex: Residencial, Celular)')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('number')
                                    ->label('Número de Telefone')
                                    ->tel()
                                    ->mask('(99)99999-9999')
                                    ->required()
                                    ->maxLength(20),
                            ])
                             ->itemLabel(function (array $state): ?string {
                                return $state['title'].':  '.$state['number'] ?? 'Novo Telefone';
                            })
                            ->columns(1) // Dois campos por linha no repeater (Título e Número)
                            ->collapsed() // Começa recolhido
                            ->defaultItems(1) // Começa com um item
                            ->maxItems(5) // Limite de 5 telefones
                            ->addActionLabel('Novo telefone'), // Rótulo do botão de adicionar

                    ])
                    ->columns(1), // Define que os campos dentro da seção terão 1 coluna
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Adicionar Cliente')
                    ->icon('heroicon-o-plus')
                    ->modalWidth('md')
                    ->modalHeading('Adicionar Cliente'),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome do Cliente')
                    ->searchable(),
                
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


     /* public static function getRelations(): array
    {
        return [
            RelationManagers\OrderRelationManager::class
        ];
    }*/

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            //'create' => Pages\CreateClient::route('/create'),
            //'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
