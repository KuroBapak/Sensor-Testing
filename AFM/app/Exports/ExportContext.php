<?php

namespace App\Exports;

use Carbon\CarbonImmutable;

/** What every tab of the full export shares: the period, who exported, and when. */
final class ExportContext
{
    /**
     * @param  CarbonImmutable  $from  first moment of the first day, site time zone
     * @param  CarbonImmutable  $to  first moment after the last day (exclusive)
     */
    public function __construct(
        public readonly CarbonImmutable $from,
        public readonly CarbonImmutable $to,
        public readonly CarbonImmutable $exportedAt,
        public readonly string $exportedBy,
        public readonly string $timezone,
        public readonly ?string $siteName,
    ) {}

    public static function forDates(
        string $fromDate,
        string $toDate,
        string $exportedBy,
        string $timezone,
        ?string $siteName,
    ): self {
        return new self(
            CarbonImmutable::parse($fromDate, $timezone)->startOfDay(),
            CarbonImmutable::parse($toDate, $timezone)->addDay()->startOfDay(),
            CarbonImmutable::now($timezone),
            $exportedBy,
            $timezone,
            $siteName ?: null,
        );
    }

    public function periodLabel(): string
    {
        return $this->from->format('d/m/Y').' – '.$this->to->subDay()->format('d/m/Y');
    }

    public function exportedAtLabel(): string
    {
        return $this->exportedAt->format('d/m/Y H:i').' oleh '.$this->exportedBy;
    }

    public function titleBlock(SheetBuilder $builder, string $title): SheetBuilder
    {
        $builder->titleBlock($title, $this->periodLabel(), $this->exportedAtLabel(), $this->siteName);

        return $builder;
    }
}
