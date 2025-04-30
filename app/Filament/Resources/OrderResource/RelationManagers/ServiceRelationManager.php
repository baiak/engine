<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use AllowDynamicProperties;
use App\Enums\TypeOfLaborStatus;
use App\Enums\TypeOfServiceStatus;
use App\Livewire\ListLabor;
use App\Models\Client;
use App\Models\Department;
use App\Models\Labor;
use App\Models\LaborImpediment;
use App\Models\Order;
use App\Models\Part;
use App\Models\Service;
use App\Models\ServiceLabor;
use App\Models\ServiceLaborLog;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\StatusUpdatedNotification;
use App\Tables\Columns\listLaborWithStatus;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Livewire;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;

use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Alignment;
use Filament\Support\View\Components\Modal;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Layout\View;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Pages\Actions;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Database\Eloquent\Collection;
use app\livewire\LaborImpedimentForm;


#[AllowDynamicProperties] class ServiceRelationManager extends RelationManager
{
    public function getStatusOptionsProperty()
    {
        return TypeOfLaborStatus::cases();
    }


    protected static string $relationship = 'service';
    public $recordId;
    public $data = [];
    public $status;
    public $selectedStatus;
    public $ServiceLaborId;
    public $items;
    public $pivot;
    public $users;
    public $service_labor_id;
    public $reason;
    public string $complained_id = '';
    public $InputServiceLaborId;
    public $impediment_reason;
    public $responsible_user_id;
    public $serviceLaborId;
    public $showError;
    public $showSuccess;
    public $impedimento = [];
    public $total;
    public $impedimentId;
    public $selectedImpedimentStatus;
    public $observation;


    protected $rules = [ 'impediment_reason' => 'required|string|max:255', 'responsible_user_id' => 'required|exists:users,id', ];


    protected $listeners = ['statusUpdated', 'fetchServiceLaborLogs', 'submit', 'countImpediments', 'totalsUpdated'];

    public function __construct()
    {
        //Log::info("ServiceRelationManager carregado."); // Log para confirmar carregamento
    }
    public function getServiceLaborLogs($serviceLaborId)
    {
        return ServiceLaborLog::where('service_labor_id', $serviceLaborId)
            ->orderByDesc('created_at')
            ->get()

            ->map(function ($log) {
                try {
                    // Decodifica os JSONs
                    $newValues = $log->new_values ? json_decode($log->new_values, true) : null;
                    $oldValues = $log->old_values ? json_decode($log->old_values, true) : null;

                    // Formatação de datas em new_values
                    if (is_array($newValues)) {
                        foreach ($newValues as $key => $value) {
                            if (in_array($key, ['created_at', 'updated_at', 'deleted_at'])) {
                                if (is_string($value) && strtotime($value)) {
                                    $newValues[$key] = Carbon::parse($value)->format('d/m/Y H:i:s');
                                } else {
                                    Log::warning("ERRO Invalid date format for key {$key}: {$value}");
                                }
                            }
                        }

                        // Adiciona o avatar do usuário ao $newValues

                            $newValues['user_avatar'] = app('userAvatar')($log->user_id);
                            $oldValues['user_avatar'] = app('userAvatar')($log->user_id);
                        //Log::alert("log user_id: {$log->user_id}");

                    }

                    // Retorna uma nova estrutura de dados
                    return [
                        'id' => $log->id,
                        'created_at' => $log->created_at,
                        'new_values' => $newValues,
                        'old_values' => $oldValues,
                    ];

                } catch (\Exception $e) {
                    Log::error("Error processing log: {$e->getMessage()}");
                    return null; // Em caso de erro, retorna null
                }
            })->filter(); // Remove entradas nulas geradas por erros
    }
    public function setServiceLaborId($id)
    {
        $this->ServiceLaborId = $id;
        $this->updateStatus();
        $this->loadLaborDescription();

       // $this->countImpediments();
    }

    public function setRecordId($id)
    {
        $this->recordId = $id;
        // Log::info("setRecordId chamado com ID: " . $id); // Verificar se `setRecordId` é acionado
        $this->loadData();
    }


    public function loadData()
    {
        // Log::info("loadData iniciado."); // Log no início do método

        if ($this->recordId) {
            $labor = Labor::find($this->recordId);

            if ($labor) {
                $this->data = [
                    'id' => $labor->id,
                    'title' => $labor->title,
                ];
                //   Log::info("loadData carregado com dados: ", $this->data); // Confirmar dados carregados
                $this->dispatch('toggle-modal', $this->data); // Emite o evento
            } else {
                // Log::warning("Labor não encontrado com ID: " . $this->recordId); // Log caso o ID não seja encontrado
            }
        } else {
            //  Log::warning("loadData chamado sem um recordId válido.");
        }
    }


    public function updateStatus(): void
    {
        try {
            // Validar os dados
            if (!$this->ServiceLaborId || !$this->selectedStatus) {
                session()->flash('error', 'Dados inválidos. Não foi possível atualizar o status.');
                return;
            }

            // Buscar o registro original
            $serviceLabor = ServiceLabor::findOrFail($this->ServiceLaborId);
            $originalValues = $serviceLabor->getOriginal();

            // Atualizar o registro
            $serviceLabor->update(['status' => $this->selectedStatus]);

            // Registrar o log da atualização
            DB::table('service_labor_logs')->insert([
                'service_labor_id' => $serviceLabor->id,
                'event' => 'updated',
                'old_values' => json_encode($originalValues),
                'new_values' => json_encode($serviceLabor->getChanges()),
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);//TODO: refatorar para corrigir a responsabilidade de operacoes no DB que deve ser feito no model

            // Emite evento para interface
            $this->dispatch('statusUpdated', $this->selectedStatus);

            //Notificacao
            try {

                //busca os dados da ordem de servico para gerar o link para compor o texto da notificacao
                $getOrderDetails = Order::findOrFail($serviceLabor->order_id);
                //busca os dados do usuario que esta alterando o status
                $getUserDetails = User::findOrFail(Auth::id());

                //TODO COLOCAR ESTE CODIGO NO OBSERVER

                // Enviar a notificação usando Laravel Notifications (não Filament)
                $user = User::findOrFail(Auth::id());
                //mudar o id para os ids dos admin apenas

                $user->notify(new StatusUpdatedNotification($serviceLabor, $originalValues['status'], $this->selectedStatus, $serviceLabor->order_id, $getUserDetails->name, $getOrderDetails->order_number));


            } catch (\Exception $e) {

                Log::error("Erro ao enviar notificação: " . $e->getMessage());

            }


            session()->flash('success', 'Status atualizado com sucesso.');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar status:', ['exception' => $e]);
            session()->flash('error', 'Ocorreu um erro ao atualizar o status.');
        }
    }


    //SESSAO DE IMPEDIMENTO
    public function addServiceLaborIdImpediment($id){
        $this->ServiceLaborId = $id;
        $this->submitForm();
        //Log::info("addServiceLaborIdImpediment carregado. serviceLaborId: ".$this->ServiceLaborId);
           }


    public function submitForm()
    { //Log::info("submit carregado."); // Log para confirmar carregamento
        try {
            // Verifica se os campos obrigatórios estão preenchidos
            if (empty($this->impediment_reason) || empty($this->responsible_user_id) || empty($this->ServiceLaborId)) {
                throw new \Exception('Todos os campos devem ser preenchidos.');
            }

            $reason = $this->impediment_reason;
            $complained_id = $this->responsible_user_id;
            $setServiceLaborId = $this->ServiceLaborId;

            // Cria o impedimento
            DB::table('labor_impediments')->insert([
                'service_labor_id' => $setServiceLaborId,
                'complainant_id' => Auth::id(),
                'complained_id' => $complained_id,
                'reason' => $reason,
                'status' =>'em aberto',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Mensagem de sucesso
            session()->flash('success', 'Impedimento registrado com sucesso.');
            $this->reset(['responsible_user_id', 'impediment_reason']);
            $this->showSuccess = true;
            $this->showError = false;




        } catch (\Exception $e) {
            // Mensagem de erro
            session()->flash('error', 'Erro ao registrar impedimento: ' . $e->getMessage());
            $this->showSuccess = false;
            $this->showError = true;
        }
    }//submit


    //contar


    public array $totals = [];

    public function countImpediments($id): int
    {
        $setServiceLaborId = $id;

        $this->getTotalFromDB = DB::table('labor_impediments')
            ->where('service_labor_id',  $setServiceLaborId)
            ->count();
        $this->totals["total_{$setServiceLaborId}"] = $this->getTotalFromDB;

        return $this->totals["total_{$setServiceLaborId}"];

    }
    //contar

    //atualizar status e log com resposta
    public function setImpedimentFormId($id){
        $this->impedimentId = $id;
        $this->impedimentInsertResponse();
    }
    public function impedimentInsertResponse(): void
    {
        try {
            // Log::info("impedimentInsertResponse carregado. selected_status: ".$this->selectedStatus);
            $model = LaborImpediment::find($this->impedimentId);

            if (!$model) {
                throw new \Exception('Impedimento não encontrado.');
            }

            $logs = $model->logs ?? [];
            $newLog = [
                'user_id' => Auth::id(),
                'selected_status' => $this->selectedImpedimentStatus,
                'date' => date('d-m-Y - H:i:s'),
                'observation' => $this->observation,
            ];

            $logs[] = $newLog;
            $model->logs = $logs;
            $model->update(['logs' => $logs, 'status' => $this->selectedImpedimentStatus]);

            session()->flash('LogSuccess', 'Log salvo com sucesso!');
            $this->dispatch('close-modal', id: 'impedimentModal');
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Erro ao salvar log: ' . $e->getMessage());

            // Flash an error message to the session
            session()->flash('LogError', 'Ocorreu um erro ao salvar o log. Por favor, tente novamente.');
        }
    }
    //atualizar status e log com resposta


    //SESSAO DE IMPEDIMENTO fim

    public function loadLaborDescription(): void
    {
        $serviceLabor = ServiceLabor::select('description')->where('id', $this->ServiceLaborId)->first();
        $this->dispatch('showLaborDescription', $serviceLabor->description);
    }



    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')->label('ID'),
            TextColumn::make('title')->label('Título'),
            TextColumn::make('description')->label('Descrição'),
        ];
    }

    public static function getCleanOptionString(Model $model): string
    {
        return (
        view('Components.select-user-result')
            ->with('name', $model?->name)
            ->with('email', $model?->email)
            ->with('image', $model?->profileImg)
            ->render()
        );
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_number')
                    ->default(function (RelationManager $livewire) {
                        return ($livewire->getOwnerRecord()->order_number);
                    }),
                Forms\Components\Hidden::make('order_id')
                    ->default(function (RelationManager $livewire) {
                        return ($livewire->getOwnerRecord()->id);
                    })
                    //->disabled()
                    ->reactive()
                    ->required(),

                Select::make('part_id')
                    ->label('Peça')
                    ->reactive()
                    ->options(function (callable $get, $state) {
                        // Obtém o ID do pedido selecionado
                        $orderId = $get('order_id');

                        // Busca o veículo associado ao pedido
                        $vehicleIdFromOrder = Order::where('id', $orderId)->value('vehicle_id');

                        // Se o pedido e o veículo relacionados forem válidos, retorna as peças associadas ao veículo
                        if ($orderId && $vehicleIdFromOrder) {
                            return Part::where('vehicle_id', $vehicleIdFromOrder)->pluck('title', 'id');
                        }

                        // Se houver um estado (part_id) e a peça for encontrada, retorna a peça
                        if ($state) {
                            $part = Part::find($state);
                            if ($part) {
                                return Part::where('id', $state)->pluck('title', 'id');
                            }
                        }

                        // Caso contrário, retorna todas as peças ordenadas por título
                        return Part::orderBy('title')->pluck('title', 'id');
                    })
                    ->createOptionForm([
                        Forms\Components\TextInput::make('vehicle.model')
                            ->label('Veículo')
                            ->readOnly()
                            ->default(function (RelationManager $livewire) {
                                return (
                                    $livewire->getOwnerRecord()->vehicle->factory . '/' .
                                    $livewire->getOwnerRecord()->vehicle->model . '/' .
                                    $livewire->getOwnerRecord()->vehicle->motor . '/'
                                );
                            }),

                        Select::make('department_id')
                            ->label('Departamento responsável')
                            ->options(Department::orderBy('title')->pluck('title', 'id'))
                            ->searchable()
                            ->placeholder('Selecione um departamento'),

                        Forms\Components\Hidden::make('vehicle_id')
                            ->label('Veículo')
                            ->default(function (RelationManager $livewire) {
                                return ($livewire->getOwnerRecord()->vehicle->id);
                            }),

                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('parameters')

                    ])
                    ->createOptionUsing(function ($data): void {
                        Part::create($data);
                    })
                    ->searchable()
                    ->hidden(fn(string $operation): bool => $operation === 'edit')
                    ->required(),

                //placeholders exibidos apenas no modal de edição
                Section::make('Dados do serviço')
                    ->visible(fn($record) => $record !== null)
                    ->schema([
                        Placeholder::make('Peça')
                            ->content(fn($record) => $record ? $record->part->title : null)
                            ->visible(fn($record) => $record !== null),

                        Placeholder::make('Responsável  / Departamento')
                            ->content(fn($record) => $record ? $record->department->user->name . ' / ' . $record->department->title : null)
                            ->visible(fn($record) => $record !== null),
                    ]),

                    Select::make('department_id')
                    ->label('Departamento')
                    ->options(function (callable $get) {
                        $partId = $get('part_id');
                        $part = Part::select('department_id')->find($partId);
                        if ($part && $part->department_id) {
                            return Department::where('id', $part->department_id)->pluck('title', 'id');
                        }
                        return Department::orderBy('title')->pluck('title', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('user_id', null))
                    ->placeholder('Selecione um departamento')
                    ->hidden(fn(string $operation): bool => $operation === 'edit'),
                
                Select::make('user_id')
                    ->label('Responsável')
                    ->options(function (callable $get) {
                        $departmentId = $get('department_id');
                        
                        if (!$departmentId) {
                            return [];
                        }
                
                        return \App\Models\User::whereHas('departments', function($query) use ($departmentId) {
                            $query->where('department_id', $departmentId)
                                  ->where('is_active', true);
                        })->pluck('name', 'id');
                    })
                    ->searchable()
                    ->reactive()
                    ->live()
                    ->placeholder('Selecione o responsável')
                    ->hidden(fn(string $operation): bool => $operation === 'edit')
                    ->required()
                    ->disabled(fn(callable $get): bool => empty($get('department_id'))),
                
                // Para exibir informações em modo de visualização/edição (readonly)
                Section::make('Dados do serviço')
                    ->visible(fn($record) => $record !== null)
                    ->schema([
                        Placeholder::make('Peça')
                            ->content(fn($record) => $record ? $record->part->title : null),
                            
                        Placeholder::make('Departamento')
                            ->content(fn($record) => $record ? $record->department->title : null),
                            
                        Placeholder::make('Responsável')
                            ->content(fn($record) => $record ? $record->user->name : null),
                    ]),

                Radio::make('status')
                    ->required()
                    ->options(TypeOfServiceStatus::class),

                Forms\Components\DatePicker::make('deadline')
                    ->required(),

                Forms\Components\RichEditor::make('description')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('part_id')
            ->columns([
                Tables\Columns\Layout\Stack::make([

                    Tables\Columns\TextColumn::make('part_id')
                       // ->extraAttributes(['class' => 'bg-black'])
                        ->weight('bold')
                        ->size('lg')
                        ->getStateUsing(function ($record) {
                            return Part::where('id', $record->part_id)->first()->title;
                        }),

                    Tables\Columns\TextColumn::make('status')
                        ->label('Status'),

                    Tables\Columns\TextColumn::make('Responsável: ')
                        ->formatStateUsing(fn(Column $column, $state): string => $column->getLabel() . ': ' . $state)
                        ->weight('bold')
                        ->label('Responsável')
                        ->getStateUsing(function ($record) {
                            return $record->department->users->pluck('name')->first();                        
                        }),

                    Tables\Columns\TextColumn::make('deadline')
                        ->formatStateUsing(fn(string $state) => 'Prazo: ' . Carbon::parse($state)->format('d/m/y')),

                ]),

                        Tables\Columns\Layout\Stack::make([

                            Tables\Columns\ViewColumn::make('statusHistory')
                                ->view('livewire.service-status-history'),

                            Tables\Columns\ViewColumn::make('labor')
                                ->view('livewire.labor-list-on-service-relation-manager')
                        ])->collapsible()
            ])
            ->contentGrid(['sm' => 2])
                            ->filters([
                                //
                            ])
                            ->headerActions([
                                Tables\Actions\CreateAction::make()
                                    ->label('Adicionar peça/serviço'),
                                //notificacao ao criar o servico

                            ])
                            ->actions([
                                Tables\Actions\ViewAction::make()
                                    ->label('Mão de obras'),
                                Tables\Actions\EditAction::make()
                                    ->label('Status do serviço'),
                                Tables\Actions\DeleteAction::make(),
                            ]);

    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Fieldset::make('Dados da ordem')
                    ->schema([
                        TextEntry::make('order.order_number')
                            ->label('Número da ordem'),
                        TextEntry::make('order.client.name')
                            ->label('Cliente'),
                        TextEntry::make('order.vehicle')
                            ->label('Veículo')
                            ->formatStateUsing(function ($record) {
                                return ($record->order->vehicle->factory . '/' .
                                    $record->order->vehicle->model . '/' .
                                    $record->order->vehicle->motor);
                            })

                    ])->columns(3),
                Fieldset::make('Dados do serviço  /  Mão de obra')
                    ->schema([
                        TextEntry::make('part.title')
                            ->label('Peça:'),
                        Livewire::make(ListLabor::class)
                            ->columnSpan(3),
                    ])->columns(4)
            ]);
    }
}
