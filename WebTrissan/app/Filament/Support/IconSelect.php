<?php

namespace App\Filament\Support;

use App\Support\MaterialIcons;
use Filament\Forms\Components\Select;

/**
 * A dropdown of icons for the public pages, so editors pick from a list instead of typing an icon name.
 *
 * Each choice shows the icon, its name and an Indonesian description, and the list can be searched in either
 * language (for example "listrik" finds the bolt). An icon name already saved in the database stays selected
 * even if it is not in the list, so older content never loses its icon.
 */
class IconSelect
{
    /**
     * A searchable icon dropdown for the given field.
     */
    public static function make(string $name): Select
    {
        return Select::make($name)
            ->options(fn (?string $state): array => self::optionsWith($state))
            ->getSearchResultsUsing(fn (string $search, ?string $state): array => self::optionsWith($state, MaterialIcons::search($search)))
            ->getOptionLabelUsing(fn (?string $value): string => MaterialIcons::label((string) $value))
            ->searchable()
            ->allowHtml()
            ->native(false)
            ->placeholder('Pilih ikon')
            ->searchPrompt('Ketik nama ikon atau artinya, misalnya "listrik" atau "pabrik"')
            ->helperText('Cari dalam bahasa Indonesia atau Inggris. Ikon tampil persis seperti di website.');
    }

    /**
     * The choices, with the currently saved value added when it is not part of the curated list.
     *
     * @param  array<string, string>|null  $choices
     * @return array<string, string>
     */
    private static function optionsWith(?string $current, ?array $choices = null): array
    {
        $choices ??= MaterialIcons::options();

        if (filled($current) && ! array_key_exists($current, MaterialIcons::ICONS)) {
            $choices = [$current => MaterialIcons::label($current)] + $choices;
        }

        return $choices;
    }
}
