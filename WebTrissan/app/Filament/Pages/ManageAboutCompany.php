<?php

namespace App\Filament\Pages;

use App\Filament\Support\PhotoUpload;
use App\Models\AboutCompany;
use App\Support\OrgChart;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class ManageAboutCompany extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'About Page';

    protected static ?string $navigationLabel = 'About Company';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.manage-about-company';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $about = AboutCompany::current();
        $attributes = $about->attributesToArray();

        if (! empty($attributes['vision_mission_values']) && is_array($attributes['vision_mission_values'])) {
            foreach ($attributes['vision_mission_values'] as &$card) {
                $type = $card['card_type'] ?? 'vision';
                $body = $card['body'] ?? null;

                $card['vision_body'] = ($type === 'vision' && is_string($body)) ? $body : '';
                $card['mission_body'] = ($type === 'mission' && is_array($body)) ? $body : [];
                $card['values_body'] = ($type === 'values' && is_array($body)) ? $body : [];
            }
        }

        $this->form->fill($attributes);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getHeroSection(),
                $this->getVisionMissionValuesSection(),
                $this->getFacilitiesSection(),
                $this->getMilestonesSection(),
                $this->getOrgChartSection(),
            ])
            ->statePath('data');
    }

    protected function getHeroSection(): Section
    {
        return Section::make('Hero Profile & Branding')
            ->description('Executive header, overview copy, and facility imagery.')
            ->schema([
                TextInput::make('hero_badge')
                    ->required()
                    ->maxLength(255),
                TextInput::make('hero_title')
                    ->required()
                    ->maxLength(255),
                Textarea::make('hero_description')
                    ->rows(4)
                    ->columnSpanFull(),
                PhotoUpload::make('hero_image', 'about'),
                FileUpload::make('company_profile_pdf')
                    ->disk('public')
                    ->acceptedFileTypes(['application/pdf'])
                    ->directory('documents'),
                FileUpload::make('partners_image')
                    ->disk('public')
                    ->image()
                    ->directory('about'),
                Section::make('Hero Image Overlay Badges')
                    ->schema([
                        TextInput::make('image_overlay.plant_badge')
                            ->label('Plant Badge'),
                        TextInput::make('image_overlay.caption_title')
                            ->label('Caption Title'),
                        TextInput::make('image_overlay.caption_subtitle')
                            ->label('Caption Subtitle'),
                        TextInput::make('image_overlay.tolerance_chip')
                            ->label('Tolerance Chip'),
                    ])->columns(2),
            ])->columns(2);
    }

    protected function getVisionMissionValuesSection(): Section
    {
        return Section::make('Vision, Mission & Core Values')
            ->description('Corporate guiding principles (strictly 3 strategic cards).')
            ->schema([
                Repeater::make('vision_mission_values')
                    ->schema([
                        Select::make('card_type')
                            ->options([
                                'vision' => 'Vision',
                                'mission' => 'Mission',
                                'values' => 'Core Values',
                            ])
                            ->required()
                            ->reactive(),
                        TextInput::make('badge')->required(),
                        TextInput::make('title')->required(),
                        TextInput::make('footer_label'),
                        TextInput::make('footer_value'),

                        Textarea::make('vision_body')
                            ->label('Vision Narrative')
                            ->rows(3)
                            ->visible(fn ($get) => $get('card_type') === 'vision')
                            ->columnSpanFull(),

                        Repeater::make('mission_body')
                            ->label('Mission Strategic Points')
                            ->simple(TextInput::make('point')->required())
                            ->visible(fn ($get) => $get('card_type') === 'mission')
                            ->columnSpanFull(),

                        Repeater::make('values_body')
                            ->label('Core Value Metrics')
                            ->schema([
                                TextInput::make('name')->required(),
                                TextInput::make('value')->required(),
                            ])
                            ->columns(2)
                            ->visible(fn ($get) => $get('card_type') === 'values')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->minItems(3)
                    ->maxItems(3)
                    ->columnSpanFull(),
            ]);
    }

    protected function getFacilitiesSection(): Section
    {
        return Section::make('Plant & Laboratory Facilities')
            ->description('Advanced manufacturing facilities and equipment.')
            ->schema([
                Repeater::make('facilities')
                    ->schema([
                        PhotoUpload::make('facility_image', 'facilities'),
                        TextInput::make('facility_tag')->required(),
                        TextInput::make('facility_title')->required(),
                        TextInput::make('spec_tag_1')->required(),
                        TextInput::make('spec_tag_2')->required(),
                        TextInput::make('sort_order')->numeric()->default(0),
                        Textarea::make('facility_description')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    protected function getMilestonesSection(): Section
    {
        return Section::make('Historical Milestones & Roadmap')
            ->description('Corporate timeline and achievements.')
            ->schema([
                Repeater::make('milestones')
                    ->schema([
                        TextInput::make('year')->required(),
                        TextInput::make('title')->required(),
                        Toggle::make('is_current')->label('Current Ongoing'),
                        TextInput::make('sort_order')->numeric()->default(0),
                        Textarea::make('description')->rows(2)->columnSpanFull(),
                        Repeater::make('highlights')
                            ->simple(TextInput::make('highlight')->required())
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    protected function getOrgChartSection(): Section
    {
        return Section::make('Organization Structure')
            ->description('Everyone in the organization chart. Choose who each person reports to; leave "Reports to" empty for the top of the chart.')
            ->schema([
                TextInput::make('org_chart.chart_revision_tag')
                    ->label('Revision Tag')
                    ->required(),

                Repeater::make('org_chart.nodes')
                    ->label('People')
                    ->itemLabel(fn (array $state): ?string => filled($state['name'] ?? null)
                        ? $state['name'].(filled($state['position'] ?? null) ? ' - '.$state['position'] : '')
                        : null)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Hidden::make('id')->default(fn (): string => (string) Str::uuid()),
                        TextInput::make('name')->required()->live(onBlur: true),
                        TextInput::make('position')->required(),
                        Select::make('parent_id')
                            ->label('Reports to')
                            ->placeholder('Nobody (top of the chart)')
                            ->options(fn (Get $get): array => $this->orgChartParentOptions($get('../../nodes') ?? [], $get('id'))),
                        Select::make('placement')
                            ->label('Shown')
                            ->options([
                                OrgChart::PLACEMENT_BELOW => 'Below the person they report to',
                                OrgChart::PLACEMENT_SIDE => 'Beside the person they report to (staff function)',
                            ])
                            ->default(OrgChart::PLACEMENT_BELOW)
                            ->required()
                            ->helperText('People shown beside someone cannot have anyone reporting to them.'),
                        TextInput::make('sort_order')
                            ->label('Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first among people with the same manager.'),
                        PhotoUpload::make('photo', 'team'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    /**
     * People a node can report to: everyone else who has a name and is not shown beside their own manager.
     *
     * @param  array<string, array<string, mixed>>  $nodes
     * @return array<string, string>
     */
    private function orgChartParentOptions(array $nodes, ?string $currentId): array
    {
        return collect($nodes)
            ->filter(fn (array $node): bool => filled($node['id'] ?? null)
                && filled($node['name'] ?? null)
                && $node['id'] !== $currentId
                && ($node['placement'] ?? OrgChart::PLACEMENT_BELOW) !== OrgChart::PLACEMENT_SIDE)
            ->mapWithKeys(fn (array $node): array => [$node['id'] => $node['name']])
            ->all();
    }

    public function save(): void
    {
        $state = $this->form->getState();

        if (! empty($state['vision_mission_values']) && is_array($state['vision_mission_values'])) {
            foreach ($state['vision_mission_values'] as &$card) {
                $type = $card['card_type'] ?? 'vision';
                if ($type === 'vision') {
                    $card['body'] = $card['vision_body'] ?? '';
                } elseif ($type === 'mission') {
                    $card['body'] = $card['mission_body'] ?? [];
                } elseif ($type === 'values') {
                    $card['body'] = $card['values_body'] ?? [];
                }

                unset($card['vision_body'], $card['mission_body'], $card['values_body']);
            }
        }

        $about = AboutCompany::current();
        $about->update($state);

        Notification::make()
            ->title('About company settings updated successfully.')
            ->success()
            ->send();
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Changes')
                ->submit('save'),
        ];
    }
}
