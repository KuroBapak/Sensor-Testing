<?php

namespace App\Filament\Pages;

use App\Models\GlobalSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageGlobalSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Global Data Settings';

    protected static ?string $navigationLabel = 'Global Settings';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.manage-global-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $settings = GlobalSetting::current();
        $this->form->fill($settings->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Brand Identity')
                ->description('Company branding, logos, and footer information.')
                ->schema([
                    TextInput::make('company_name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('company_tagline')
                        ->required()
                        ->maxLength(255),
                    FileUpload::make('navbar_logo')
                        ->disk('public')
                        ->image()
                        ->directory('branding'),
                    FileUpload::make('footer_logo')
                        ->disk('public')
                        ->image()
                        ->directory('branding'),
                    FileUpload::make('footer_partner_image')
                        ->disk('public')
                        ->image()
                        ->directory('branding'),
                    TextInput::make('copyright_text')
                        ->maxLength(255),
                    Textarea::make('footer_description')
                        ->rows(3)
                        ->columnSpanFull(),
                    Repeater::make('footer_legal_links')
                        ->schema([
                            TextInput::make('label')->required(),
                            TextInput::make('url')->helperText('Optional. Without a URL the link is shown as plain text.'),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('Company Statistics')
                ->description('High-level statistics displayed across Landing and About pages (strictly 4 items).')
                ->schema([
                    Repeater::make('company_stats')
                        ->schema([
                            TextInput::make('value')
                                ->required()
                                ->placeholder('e.g. 25+'),
                            TextInput::make('label_landing')
                                ->required()
                                ->placeholder('e.g. Tahun Pengalaman'),
                            TextInput::make('label_about')
                                ->required()
                                ->placeholder('e.g. Tahun Dedikasi'),
                        ])
                        ->columns(3)
                        ->minItems(4)
                        ->maxItems(4)
                        ->columnSpanFull(),
                ]),

            Section::make('Contact Information')
                ->description('Contact numbers and notification email addresses.')
                ->schema([
                    Repeater::make('phones')
                        ->schema([
                            TextInput::make('display_text')
                                ->required()
                                ->placeholder('+62 (21) ...'),
                            TextInput::make('tel_url')
                                ->required()
                                ->placeholder('tel:+62...'),
                            Toggle::make('is_whatsapp')
                                ->label('WhatsApp')
                                ->default(false),
                        ])
                        ->columns(3)
                        ->minItems(1)
                        ->maxItems(2)
                        ->columnSpanFull(),
                    TextInput::make('whatsapp_number')
                        ->label('WhatsApp number')
                        ->tel()
                        ->placeholder('+62 812-3456-7890')
                        ->helperText('The contact form sends visitors to WhatsApp with their message prefilled, to this number. Leave empty to only save the message.')
                        ->maxLength(30)
                        ->columnSpanFull(),
                    TextInput::make('primary_email')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('corporate_email')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('notification_email')
                        ->email()
                        ->helperText('Inquiry submissions are delivered to this address.')
                        ->maxLength(255),
                ])->columns(3),

            Section::make('Location & Plant Facilities')
                ->description('Factory location, zones, and Google Maps embed configuration.')
                ->schema([
                    Textarea::make('factory_address')
                        ->rows(2)
                        ->columnSpanFull(),
                    TextInput::make('facility_zone')
                        ->maxLength(255),
                    TextInput::make('maps_button_url')
                        ->url()
                        ->maxLength(255),
                    TextInput::make('overlay_title')
                        ->maxLength(255),
                    TextInput::make('overlay_coordinate')
                        ->maxLength(255),
                    Textarea::make('maps_iframe_embed_url')
                        ->helperText('Isi hanya URL src dari Google Maps (Share > Embed a map), atau format sederhana: https://maps.google.com/maps?q=LAT,LNG&z=15&output=embed. Harus diawali https://.')
                        ->rows(2)
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('Social Media Channels')
                ->description('Official corporate social media profiles.')
                ->schema([
                    Repeater::make('social_media')
                        ->schema([
                            TextInput::make('platform_name')->required(),
                            TextInput::make('platform_icon'),
                            TextInput::make('handle'),
                            TextInput::make('description'),
                            TextInput::make('profile_url')->url()->required(),
                            TextInput::make('action_text'),
                            TextInput::make('sort_order')->numeric()->default(0),
                        ])
                        ->columns(4)
                        ->columnSpanFull(),
                ]),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $settings = GlobalSetting::current();
        $settings->update($state);

        Notification::make()
            ->title('Global settings updated successfully.')
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
