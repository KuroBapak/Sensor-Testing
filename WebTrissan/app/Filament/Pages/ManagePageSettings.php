<?php

namespace App\Filament\Pages;

use App\Models\PageSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManagePageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Landing Page';

    protected static ?string $navigationLabel = 'Page Settings';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.manage-page-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $data = [];
        foreach (['landing', 'works', 'portfolio', 'licenses', 'about'] as $pageKey) {
            $setting = PageSetting::forPage($pageKey);
            $data[$pageKey] = $setting ? $setting->attributesToArray() : [];
        }

        if (isset($data['landing']['about_teaser']) && is_array($data['landing']['about_teaser'])) {
            if (empty($data['landing']['about_teaser']['description'])) {
                $p1 = $data['landing']['about_teaser']['paragraph_1'] ?? '';
                $p2 = $data['landing']['about_teaser']['paragraph_2'] ?? '';
                $combined = trim($p1.($p1 && $p2 ? "\n\n" : '').$p2);
                if (! empty($combined)) {
                    $data['landing']['about_teaser']['description'] = $combined;
                }
            }
        }

        $this->form->fill($data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Pages')
                    ->tabs([
                        $this->getLandingTab(),
                        $this->getWorksTab(),
                        $this->getPortfolioTab(),
                        $this->getLicensesTab(),
                        $this->getAboutTab(),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    protected function getLandingTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Landing Page')
            ->statePath('landing')
            ->schema([
                Section::make('SEO Settings')
                    ->schema([
                        TextInput::make('seo_title')
                            ->maxLength(255),
                        Textarea::make('seo_description')
                            ->rows(3),
                    ]),

                Section::make('Hero Header & Call to Action')
                    ->schema([
                        TextInput::make('hero_title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('hero_subtitle')
                            ->rows(3),
                        TextInput::make('hero_cta_text')
                            ->maxLength(255)
                            ->placeholder('Lihat Portofolio'),
                        TextInput::make('hero_cta_url')
                            ->maxLength(255)
                            ->placeholder('#works'),
                        Repeater::make('hero_slides')
                            ->label('Hero Background Carousel Slides')
                            ->schema([
                                FileUpload::make('image')
                                    ->disk('public')
                                    ->image()
                                    ->directory('hero'),
                                TextInput::make('alt_text')
                                    ->required(),
                            ])
                            ->columns(2)
                            ->minItems(1)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Trust Card')
                    ->description('Social proof card floating in the hero section.')
                    ->schema([
                        TextInput::make('trust_card.label')
                            ->label('Card Badge / Label')
                            ->placeholder('TRUSTED BY 50+ LEADERS'),
                        TextInput::make('trust_card.rating')
                            ->label('Rating Score')
                            ->placeholder('★ 4.9/5.0'),
                        Textarea::make('trust_card.description')
                            ->label('Description')
                            ->rows(2)
                            ->columnSpanFull(),
                        Repeater::make('trust_card.clients')
                            ->label('Client Logo Tags')
                            ->simple(TextInput::make('client')->required())
                            ->maxItems(4)
                            ->columnSpanFull(),
                        TextInput::make('trust_card.footer_left')
                            ->label('Footer Left Note')
                            ->placeholder('AS9100D & ISO 9001'),
                        TextInput::make('trust_card.footer_right')
                            ->label('Footer Right Note')
                            ->placeholder('Global Audited'),
                    ])
                    ->columns(2),

                Section::make('About Teaser Section')
                    ->description('Company introduction preview section on the landing page.')
                    ->schema([
                        FileUpload::make('about_teaser.image')
                            ->disk('public')
                            ->label('Teaser Image')
                            ->image()
                            ->directory('about'),
                        TextInput::make('about_teaser.image_badge')
                            ->label('Image Badge')
                            ->placeholder('EST. 1998 • 25+ THN'),
                        TextInput::make('about_teaser.eyebrow')
                            ->label('Section Eyebrow')
                            ->placeholder('TENTANG PERUSAHAAN'),
                        TextInput::make('about_teaser.title')
                            ->label('Section Title')
                            ->placeholder('Dedikasi Presisi Sejak 1998'),
                        Textarea::make('about_teaser.description')
                            ->label('Description')
                            ->rows(4)
                            ->columnSpanFull(),
                        TextInput::make('about_teaser.cta_text')
                            ->label('CTA Button Text')
                            ->placeholder('Pelajari Profil Lengkap'),
                        TextInput::make('about_teaser.cta_url')
                            ->label('CTA Button URL')
                            ->placeholder('/about'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getWorksTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Works Page')
            ->statePath('works')
            ->schema([
                Section::make('SEO Settings')
                    ->schema([
                        TextInput::make('seo_title')
                            ->maxLength(255),
                        Textarea::make('seo_description')
                            ->rows(3),
                    ]),

                Section::make('Hero Header')
                    ->schema([
                        TextInput::make('hero_title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('hero_subtitle')
                            ->label('Hero Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Key Performance Metrics (Stats)')
                    ->description('Strictly 4 stat metrics displayed in the hero section.')
                    ->schema([
                        Repeater::make('stats')
                            ->schema([
                                TextInput::make('icon')
                                    ->label('Icon (Material Symbol)')
                                    ->placeholder('e.g. local_shipping'),
                                TextInput::make('value')
                                    ->required()
                                    ->placeholder('e.g. 120+'),
                                TextInput::make('label')
                                    ->required()
                                    ->placeholder('e.g. Total Deliveries'),
                                TextInput::make('caption')
                                    ->placeholder('e.g. Pengiriman On-Time 99.4%'),
                            ])
                            ->columns(4)
                            ->minItems(4)
                            ->maxItems(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function getPortfolioTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Portfolio Page')
            ->statePath('portfolio')
            ->schema([
                Section::make('SEO Settings')
                    ->schema([
                        TextInput::make('seo_title')
                            ->maxLength(255),
                        Textarea::make('seo_description')
                            ->rows(3),
                    ]),

                Section::make('Hero Header & Registry')
                    ->schema([
                        TextInput::make('hero_title')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('registry_revision_tag')
                            ->placeholder('e.g. APEX REGISTRY: REV-2024.Q2-ACTIVE')
                            ->maxLength(255),
                        Textarea::make('hero_subtitle')
                            ->label('Hero Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Engineering Metrics (Stats)')
                    ->description('Strictly 5 stat metrics displayed in the portfolio hero section.')
                    ->schema([
                        Repeater::make('stats')
                            ->schema([
                                TextInput::make('icon')
                                    ->label('Icon (Material Symbol)')
                                    ->placeholder('e.g. engineering'),
                                TextInput::make('value')
                                    ->required()
                                    ->placeholder('e.g. 15'),
                                TextInput::make('label')
                                    ->required()
                                    ->placeholder('e.g. Tenaga Ahli'),
                                TextInput::make('caption')
                                    ->placeholder('e.g. Lead Engineers'),
                            ])
                            ->columns(4)
                            ->minItems(5)
                            ->maxItems(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function getLicensesTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Licenses Page')
            ->statePath('licenses')
            ->schema([
                Section::make('SEO Settings')
                    ->schema([
                        TextInput::make('seo_title')
                            ->maxLength(255),
                        Textarea::make('seo_description')
                            ->rows(3),
                    ]),

                Section::make('Hero Header')
                    ->schema([
                        TextInput::make('hero_title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('hero_subtitle')
                            ->label('Hero Subtitle / Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Hero Badge Card')
                    ->description('Quality assurance badge card shown alongside the hero title.')
                    ->schema([
                        TextInput::make('hero_badge_card.eyebrow')
                            ->label('Eyebrow')
                            ->placeholder('e.g. Integritas Mutu Pabrik'),
                        TextInput::make('hero_badge_card.title')
                            ->label('Title')
                            ->placeholder('e.g. Fasilitas Terakreditasi Penuh'),
                        TextInput::make('hero_badge_card.caption')
                            ->label('Caption')
                            ->placeholder('e.g. Standar Internasional ISO & ASME'),
                    ])
                    ->columns(3),

                Section::make('Accreditation Statistics')
                    ->description('Strictly 4 summary stat metrics in the licenses hero section.')
                    ->schema([
                        Repeater::make('stats')
                            ->schema([
                                TextInput::make('icon')
                                    ->label('Icon (Material Symbol)')
                                    ->placeholder('e.g. verified'),
                                TextInput::make('value')
                                    ->required()
                                    ->placeholder('e.g. 4+'),
                                TextInput::make('label')
                                    ->required()
                                    ->placeholder('e.g. Sertifikasi Produk'),
                                TextInput::make('caption')
                                    ->placeholder('e.g. Tervalidasi uji lab independen'),
                            ])
                            ->columns(4)
                            ->minItems(4)
                            ->maxItems(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Corporate Highlight Cards')
                    ->description('Strictly 4 major international compliance standards cards.')
                    ->schema([
                        Repeater::make('corporate_highlight_cards')
                            ->schema([
                                TextInput::make('icon')
                                    ->label('Icon (Material Symbol)')
                                    ->required()
                                    ->placeholder('e.g. verified_user'),
                                TextInput::make('title')
                                    ->label('Standard Title')
                                    ->required()
                                    ->placeholder('e.g. ISO 9001:2015'),
                                TextInput::make('subtitle')
                                    ->label('Certification Scope')
                                    ->placeholder('e.g. QMS Fabrikasi'),
                                TextInput::make('caption')
                                    ->label('Auditing Body & Validity')
                                    ->placeholder('e.g. TÜV SÜD · Audit 2025'),
                            ])
                            ->columns(4)
                            ->minItems(4)
                            ->maxItems(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function getAboutTab(): Tabs\Tab
    {
        return Tabs\Tab::make('About Page')
            ->statePath('about')
            ->schema([
                Section::make('SEO Settings')
                    ->description('Hero and content of the About page are managed in About Company.')
                    ->schema([
                        TextInput::make('seo_title')
                            ->maxLength(255),
                        Textarea::make('seo_description')
                            ->rows(3),
                    ]),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach (['landing', 'works', 'portfolio', 'licenses', 'about'] as $pageKey) {
            if (isset($state[$pageKey])) {
                PageSetting::updateOrCreate(
                    ['page' => $pageKey],
                    $state[$pageKey]
                );
            }
        }

        Notification::make()
            ->title('Page settings updated successfully.')
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
