<?php

namespace App\Filament\Resources\Conveniados;

use App\Filament\Resources\Conveniados\ConveniadosResource\Pages;
use App\Filament\Resources\Conveniados\ConveniadosResource\RelationManagers;
use App\Models\Admin\Conveniado;
use App\Models\Admin\ConveniadoAutorizado;
use App\Models\Admin\Bairro;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Leandrocfe\FilamentPtbrFormFields\Money;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineAction;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;
	
class ConveniadosResource extends Resource
{
    protected static ?string $model = Conveniado::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

	protected static ?string $modelLabel = 'Conveniados';
	
	protected static ?string $slug = 'conveniados';

	protected static ?string $navigationGroup = 'Conveniados';

    public static function form(Form $form): Form
    {

		return $form
            ->schema([
                Forms\Components\Tabs::make()
					->tabs([
						Tabs\Tab::make('Cadastro')
							->schema([
								Group::make()->schema([
									Forms\Components\TextInput::make('CodigoConvenio'),
									Forms\Components\TextInput::make('NomeConveniado')
										->label('Conveniado')
										->required()
										->maxLength(255)
										->columnSpan(2)
										->dehydrateStateUsing(fn (string $state): string => strtoupper ($state)),
									Forms\Components\TextInput::make('CpfCnpj')
										->label('CPF/CNPJ')
										->required()
										->mask(RawJs::make(<<<'JS'
											$input.length > 14 ? '99.999.999/9999-99' : '999.999.999-99'
										JS))
										->rule('cpf_ou_cnpj')
										->maxLength(20),
									Forms\Components\DatePicker::make('DataNascimento')
										->label('Nascimento')
										->placeholder('dd/mm/aaaa')
										->timezone('America/Sao_Paulo')
										->prefixIcon('heroicon-s-calendar-days')
										->native(false)
										->displayFormat('d/m/Y')
										->firstDayOfWeek(7)
										->closeOnDateSelection(),
								])->columns(4),
								Group::make()->schema([
									Forms\Components\TextInput::make('RG')
										->label('RG')
										->maxLength(20),
									Forms\Components\Select::make('RGOrgao')
										->label('Orgão Expeditor')
										->searchable()
										->options([
											'CBM'=>'CBM','CFN'=>'CFN','CFP'=>'CFP','CORECON'=>'CORECON','COREN'=>'COREN','CRA'=>'CRA','CRB'=>'CRB',
											'CRCI'=>'CRCI','CRE'=>'CRE','CREA'=>'CREA','CREFITO'=>'CREFITO','CRF'=>'CRF','CRFA'=>'CRFA','CRM'=>'CRM',
											'CRMV'=>'CRMV','CRO'=>'CRO','DGPC'=>'DGPC','DIC'=>'DIC','DPF'=>'DPF','IDAMP'=>'IDAMP','IFP'=>'IFP','IN'=>'IN',
											'IPF'=>'IPF','ITB'=>'ITB','JUNTA'=>'JUNTA','MAER'=>'MAER','MEX'=>'MEX','MM'=>'MM','MRE'=>'MRE','MTE'=>'MTE',
											'OAB'=>'OAB','PM'=>'PM','SDS'=>'SDS','SEJSP'=>'SEJSP','SES'=>'SES','SESP'=>'SESP','SRF'=>'SRF','SJS'=>'SJS',
											'SJTC'=>'SJTC','SSIPT'=>'SSIPT','SSP" selected="selected'=>'SSP','VACIV'=>'VACIV','VAMEN'=>'VAMEN'
										]),
									Forms\Components\Select::make('RGOrgaoUF')
										->label('UF')
										->rule('uf')
										->searchable()
										->options([
											'AC'=>'AC','AL'=>'AL','AP'=>'AP','AM'=>'AM','BA'=>'BA','CE'=>'CE','DF'=>'DF','ES'=>'ES','GO'=>'GO',
											'MA'=>'MA','MT'=>'MT','MS'=>'MS','MG'=>'MG','PA'=>'PA','PB'=>'PB','PR'=>'PR','PE'=>'PE','PI'=>'PI',
											'RJ'=>'RJ','RN'=>'RN','RS'=>'RS','RO'=>'RO','RR'=>'RR','SC'=>'SC','SP'=>'SP','SE'=>'SE','TO'=>'TO'
										]),
									Forms\Components\Select::make('Sexo')
										->searchable()
										->options([
											'M' => 'MASCULINO',
											'F' => 'FEMININO',
										]),
								])->columns(4),
								Group::make()->schema([
									Forms\Components\TextInput::make('Celular')
										->rule('celular_com_ddd')
										->mask('(99)99999-9999')
										->maxLength(25),
									Forms\Components\TextInput::make('Telefone')
										->rule('telefone_com_ddd')
										->mask('(99)9999-9999')
										->maxLength(25),
									Forms\Components\TextInput::make('Email')
										//->required()
										->maxLength(255)
										->columnSpan(2)
										//->dehydrateStateUsing(fn (string $state): string => strtolower ($state))
										,
								])->columns(4),
								Group::make()->schema([
									Forms\Components\TextInput::make('Matricula')
										->label('Matrícula')
										->maxLength(40),
									Forms\Components\TextInput::make('NumeroCartao')
										->label('Número Cartão')
										->maxLength(60),
									Money::make('LimiteCredito')
										->label('Limite Crédito')
										->prefixIcon('heroicon-s-banknotes')
										->prefix(null),
									Forms\Components\Select::make('Situacao')
										->label('Status')
										->searchable()
										->reactive()
										->options([
											'0' => 'LIBERADO',
											'1' => 'DEMITIDO',
										])
										->afterStateUpdated(function ($state, callable $set, $get) {
											if ($get('Situacao') === '1') {
												return $set('DataDesligamento', now());
											}
											return $set('DataDesligamento', '');
										})										
										,
								])->columns(4),
								Group::make()->schema([
									Forms\Components\DatePicker::make('DataDesligamento')
										->label('Data Desligamento')
										->placeholder('dd/mm/aaaa')
										->timezone('America/Sao_Paulo')
										->prefixIcon('heroicon-s-calendar-days')
										->native(false)
										->displayFormat('d/m/Y')
										->firstDayOfWeek(7)
										->closeOnDateSelection()
										->reactive()
										->required(fn(Forms\Get $get) => $get('Situacao') == '1')
										->disabled(fn(Forms\Get $get) => $get('Situacao') == '0'),
									Forms\Components\TextInput::make('Aviso')
										->maxLength(255)
										->columnSpan(3)
										//->dehydrateStateUsing(fn (string $state): string => strtoupper ($state))
										,
								])->columns(4),
							]),
						Tabs\Tab::make('Endereço')
							->schema([
								Group::make()->schema([						
									Forms\Components\TextInput::make('Endereco')
										->label('Endereço')
										->maxLength(255)
										->columnSpan(4)
										//->dehydrateStateUsing(fn (string $state): string => strtoupper ($state))
										,
									Forms\Components\TextInput::make('Numero')
										->label('Número')
										->maxLength(8),
									Forms\Components\TextInput::make('Cep')
										->mask('99999-999')
										->rule('formato_cep')
										->maxLength(18),
								])->columns(6),
								Group::make()->schema([						
									Forms\Components\TextInput::make('Complemento')
										->maxLength(200)
										->columnSpan(2)
										//->dehydrateStateUsing(fn (string $state): string => strtoupper ($state))
										,
									Forms\Components\Select::make('CodigoCidade')
										->label('Cidade')
										->searchable()
										->live()
										->relationship(
											name: 'cidade', 
											titleAttribute: 'NomeCidade',
											modifyQueryUsing: fn (Builder $query) => $query->where('UF','PR'))
										->preload(),
									Forms\Components\Select::make('CodigoBairro')
										->label('Bairro')
										->options(fn(Forms\Get $get) => Bairro::
											  where('CodigoCidade',$get('CodigoCidade'))
											->where('Exibir',1)
											->pluck('NomeBairro','CodigoBairro'))
										->disabled(fn(Forms\Get $get): bool => !filled($get('CodigoCidade'))),
								])->columns(4),
								Forms\Components\TextInput::make('Referencia')
									->label('Referência')
									->maxLength(255)
									->columns(2)
									//->dehydrateStateUsing(fn (string $state): string => strtoupper ($state))
									,
								Textarea::make('observacao')
									->label('Observação')
									->maxLength(255)
									->columns(2)
									//->dehydrateStateUsing(fn (string $state): string => strtoupper ($state))
									,
							]),
						/*Tabs\Tab::make('Autorizados')
							->schema([
								Group::make()->schema([	
									Forms\Components\Placeholder::make('')
									//->content(view('filament.components.tables.conveniados-autorizados',compact('data')))
								])
							]),*/
					])
            ])->columns('full');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('NomeConveniado', 'asc')
			->modifyQueryUsing(fn() => Conveniado::query()
				->whereHas('convenio', function($query) {
					$query->where('LiberarWeb',true);
				})
			) 
			->columns([
                Tables\Columns\TextColumn::make('CodigoConveniadoID')
                    ->label('Código')
					->searchable()
					->sortable(),
                Tables\Columns\TextColumn::make('NomeConveniado')
                    ->label('Conveniado')
					->searchable()
					->sortable()
					->limit(25),
                Tables\Columns\TextColumn::make('CpfCnpj')
                    ->label('Cpf')
					->searchable()
					->sortable(),
                Tables\Columns\TextColumn::make('convenio.NomeConvenio')
                    ->label('Convênio')
					->searchable()
					->sortable()
					->limit(25),
                Tables\Columns\TextColumn::make('aprovacao.StatusAprovacao')
                    ->label('Status')
					->searchable()
					->sortable(),
                Tables\Columns\TextColumn::make('LimiteCredito')
                    ->label('Limite')
					->formatStateUsing(fn ($state): string => 'R$ ' . number_format((float) $state, 2, ',', '.')),
				Tables\Columns\TextColumn::make('convenio.farmacia.NomeFarmacia')
					->label('Farmácia')
                    ->searchable()
					->sortable()
					->visible(fn (): bool => !\Auth::user()->isFarmacia()),
            ])
            ->filters([
                TrashedFilter::make(),
				SelectFilter::make('NomeFarmacia')
					->label('Farmácia')
					->relationship('convenio.farmacia','NomeFarmacia')
					->searchable()
					->multiple()
					->preload()
					->visible(fn (): bool => !\Auth::user()->isFarmacia()),
            ])
            ->actions([
				Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
				ActivityLogTimelineAction::make('Logs')
					->authorize(fn (Conveniado $record): bool => auth()->user()->can('log', $record))
					->translateLabel()
					->withRelations(['user', 'config'])
					->timelineIcons([
						'restored'=> 'heroicon-m-arrow-uturn-left',
						'created' => 'heroicon-m-check-badge',
						'updated' => 'heroicon-m-pencil-square',
						'deleted' => 'heroicon-m-trash',
					])
					->timelineIconColors([
					    'restored'=> 'gray',
						'created' => 'info',
					    'updated' => 'warning',
					    'deleted' => 'danger',
					])
					->limit(30),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
						->authorize(fn (Conveniado $record): bool => auth()->user()->can('delete', $record)),
                    Tables\Actions\ForceDeleteBulkAction::make()
						->authorize(fn (Conveniado $record): bool => auth()->user()->can('forceDelete', $record)),
                    Tables\Actions\RestoreBulkAction::make()
						->authorize(fn (Conveniado $record): bool => auth()->user()->can('restore', $record)),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TitulosRelationManager::class,
			//ActivitylogRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConveniados::route('/'),
            'create' => Pages\CreateConveniados::route('/create'),
            'edit' => Pages\EditConveniados::route('/{record}/edit'),
        ];
    }
}
