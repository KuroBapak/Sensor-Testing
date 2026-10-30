<?php

namespace Database\Seeders;

use App\Models\GlobalSetting;
use App\Models\PageSetting;
use Illuminate\Database\Seeder;

/**
 * Fills the contact details that are still empty with clearly fake demo values, for the development deployment.
 *
 * Not part of DatabaseSeeder. Run it on purpose, e.g. `php artisan db:seed --class=DemoContactDetailsSeeder --force`.
 * It only fills what is blank, a bare site root such as https://www.facebook.com/, or the known template headline,
 * so real values entered in the admin panel are never overwritten, and running it twice changes nothing.
 *
 * Replace these values in the admin panel before launch.
 */
class DemoContactDetailsSeeder extends Seeder
{
    /**
     * Not a valid WhatsApp number on purpose (country code 62 followed by a leading 0): wa.me shows an
     * "invalid number" page instead of opening a chat with a stranger.
     */
    public const WHATSAPP_NUMBER = '620000000000';

    /**
     * Demo profile URLs per social platform icon.
     *
     * @var array<string, array{handle: string, profile_url: string}>
     */
    public const SOCIAL_PROFILES = [
        'facebook' => ['handle' => '@pt.trissan.demo', 'profile_url' => 'https://www.facebook.com/pt.trissan.demo'],
        'instagram' => ['handle' => '@pt.trissan.demo', 'profile_url' => 'https://www.instagram.com/pt.trissan.demo'],
    ];

    /**
     * The English template headline that ended up on the landing page, and the Indonesian one from PageSettingSeeder.
     */
    public const PLACEHOLDER_HERO_TITLE = 'We Provide For User experience';

    public const HERO_TITLE = 'Solusi Kelistrikan & Mekanikal untuk Industri Anda';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = GlobalSetting::current();

        $settings->update(array_filter([
            'whatsapp_number' => blank($settings->whatsapp_number) ? self::WHATSAPP_NUMBER : null,
            'social_media' => $this->socialMedia($settings->social_media ?? []),
        ], fn (mixed $value): bool => $value !== null));

        $landing = PageSetting::forPage('landing');

        if ($landing !== null && $landing->hero_title === self::PLACEHOLDER_HERO_TITLE) {
            $landing->update(['hero_title' => self::HERO_TITLE]);
        }
    }

    /**
     * The social entries with demo profile URLs filled in where the URL is missing or only a site root.
     *
     * @param  array<int, array<string, mixed>>  $entries
     * @return array<int, array<string, mixed>>|null null when nothing needs to change
     */
    private function socialMedia(array $entries): ?array
    {
        $changed = false;

        $filled = array_map(function (array $entry) use (&$changed): array {
            $demo = self::SOCIAL_PROFILES[$entry['platform_icon'] ?? ''] ?? null;
            $url = (string) ($entry['profile_url'] ?? '');

            if ($demo === null || trim((string) parse_url($url, PHP_URL_PATH), '/') !== '') {
                return $entry;
            }

            $changed = true;

            return [...$entry, 'handle' => $entry['handle'] ?: $demo['handle'], 'profile_url' => $demo['profile_url']];
        }, $entries);

        return $changed ? $filled : null;
    }
}
