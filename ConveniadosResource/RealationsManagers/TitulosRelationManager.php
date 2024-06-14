<?php

namespace App\Filament\Resources\Conveniados\ConveniadosResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Card;
//use Filament\Forms\Components\Group;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TitulosRelationManager extends RelationManager
{
    protected static string $relationship = 'titulos';

	protected static ?string $modelLabel = 'Titulos';
	
	protected static ?string $pluralModelLabel = 'Titulos';

    public function table(Table $table): Table
    {
        return $table
			
			->defaultGroup('DataVencimento')
			->groupsOnly(false)
			->groupingSettingsHidden()	
			->groupingDirectionSettingHidden(false)
			->groups([
				Group::make('DataVencimento')
					->label('Vencimento')
					->date()
					->collapsible(true)
					->titlePrefixedWithLabel(true)
					,
			])			
			->configure([
				Table::$defaultDateDisplayFormat = 'F Y'
			])
			->defaultSort('DataVencimento', 'asc')			
			->columns([
                Tables\Columns\TextColumn::make('CodigoVendaID')
					->label('Venda'),
                Tables\Columns\TextColumn::make('loja.NomeLoja')
					->label('Farmácia'),
                Tables\Columns\TextColumn::make('DataLancamento')
					->label('Lançamento')
					->searchable()
					->datetime('d/m/Y')
					->sortable(),
				Tables\Columns\TextColumn::make('Parcela'),
				Tables\Columns\TextColumn::make('DataVencimento')
					->label('Vencimento')
					->searchable()
					->datetime('d/m/Y')
					->sortable(),
                Tables\Columns\TextColumn::make('Saldo')
					->label('Saldo')
					->formatStateUsing(fn ($state): string => 'R$ ' . number_format((float) $state, 2, ',', '.'))
					->summarize(
						Sum::make()
						->label('Total:')
						->extraAttributes(['class' => 'font-bold'])
						->formatStateUsing(fn ($state): string => 'R$ ' . number_format((float) $state, 2, ',', '.')),
					)
					,
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ]);
    }
}
