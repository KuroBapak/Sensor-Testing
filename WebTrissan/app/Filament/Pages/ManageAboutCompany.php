<?php

namespace App\Filament\Pages;

use App\Models\AboutCompany;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

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
                FileUpload::make('hero_image')
                    ->image()
                    ->directory('about'),
                FileUpload::make('company_profile_pdf')
                    ->acceptedFileTypes(['application/pdf'])
                    ->directory('documents'),
                FileUpload::make('partners_image')
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
                        FileUpload::make('facility_image')
                            ->image()
                            ->directory('facilities'),
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
            ->description('Executive leadership and engineering division heads.')
            ->schema([
                TextInput::make('org_chart.chart_revision_tag')
                    ->label('Revision Tag')
                    ->required(),

                Section::make('Chief Executive Officer')
                    ->schema([
                        TextInput::make('org_chart.ceo.name')->label('Name')->required(),
                        TextInput::make('org_chart.ceo.position')->label('Position')->required(),
                        TextInput::make('org_chart.ceo.experience_tag')->label('Experience Tag'),
                        FileUpload::make('org_chart.ceo.photo')->image()->directory('team'),
                    ])->columns(2),

                Section::make('General Manager / COO')
                    ->schema([
                        TextInput::make('org_chart.general_manager.name')->label('Name')->required(),
                        TextInput::make('org_chart.general_manager.position')->label('Position')->required(),
                    ])->columns(2),

                Repeater::make('org_chart.division_heads')
                    ->label('Engineering Division Heads')
                    ->schema([
                        TextInput::make('division_tag')->required(),
                        TextInput::make('head_name')->required(),
                        TextInput::make('head_position')->required(),
                        TextInput::make('sort_order')->numeric()->default(0),
                        Repeater::make('teams')
                            ->simple(TextInput::make('team_name')->required())
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
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
