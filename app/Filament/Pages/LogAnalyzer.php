<?php

namespace App\Filament\Pages;

use App\Actions\AnalyzeLogFile;
use App\Exceptions\InvalidLogFileException;
use App\Filament\Resources\ScanResults\ScanResultResource;
use App\Models\ExpertRule;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use UnitEnum;

class LogAnalyzer extends Page
{
    protected string $view = 'filament.pages.log-analyzer';

    protected static string|UnitEnum|null $navigationGroup = 'Sistem Pakar';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Log Analyzer';

    protected static ?string $title = 'Log Analyzer';

    protected static ?int $navigationSort = 1;

    protected ?string $subheading = 'Unggah file log jaringan untuk dianalisis dengan mesin inferensi forward chaining.';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    private AnalyzeLogFile $analyzeLogFile;

    public function boot(AnalyzeLogFile $analyzeLogFile): void
    {
        $this->analyzeLogFile = $analyzeLogFile;
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Unggah File Log')
                    ->description('File CSV/JSON harus memiliki kolom source_ip, target_port, dan failed_attempts. File TXT/LOG dengan format SSH atau Apache access log juga didukung.')
                    ->schema([
                        FileUpload::make('log_file')
                            ->label('File Log')
                            ->helperText('Maksimal '.(int) (config('ids.uploads.max_size_kb') / 1024).' MB. Format: CSV, JSON, SSH/syslog, atau Apache access log.')
                            ->required()
                            ->acceptedFileTypes([
                                'text/csv',
                                'text/plain',
                                'text/x-log',
                                'application/csv',
                                'application/json',
                                'application/vnd.ms-excel',
                                'application/octet-stream',
                            ])
                            ->maxSize(config('ids.uploads.max_size_kb'))
                            ->storeFiles(false),
                    ]),
            ])
            ->statePath('data');
    }

    public function analyze(): void
    {
        if (! ExpertRule::query()->exists()) {
            Notification::make()
                ->warning()
                ->title('Knowledge base masih kosong')
                ->body('Tambahkan minimal satu aturan pakar sebelum menganalisis file log.')
                ->send();

            return;
        }

        $state = $this->form->getState();
        $file = $state['log_file'] ?? null;

        if (! $file instanceof TemporaryUploadedFile) {
            Notification::make()
                ->danger()
                ->title('File log tidak valid')
                ->body('Silakan unggah ulang file log yang akan dianalisis.')
                ->send();

            return;
        }

        try {
            $summary = $this->analyzeLogFile->handle(
                $file->getRealPath(),
                $file->getClientOriginalName(),
            );
        } catch (InvalidLogFileException $exception) {
            Notification::make()
                ->danger()
                ->title('Gagal menganalisis log')
                ->body($exception->getMessage())
                ->send();

            return;
        }

        $this->form->fill();
        $this->dispatch('scan-completed');

        Notification::make()
            ->success()
            ->title('Analisis log selesai')
            ->body("{$summary->total} baris dianalisis: {$summary->attacks} serangan terdeteksi dan {$summary->safe} aman.")
            ->send();
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewResults')
                ->label('Lihat Semua Hasil')
                ->icon(Heroicon::OutlinedShieldExclamation)
                ->url(ScanResultResource::getUrl('index')),
        ];
    }
}
