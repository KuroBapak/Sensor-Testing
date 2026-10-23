<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * One worksheet. Subclasses describe their rows in build(); the formatting
 * standards live in SheetBuilder.
 */
abstract class BuilderSheet implements FromArray, WithEvents, WithTitle
{
    private ?SheetBuilder $builder = null;

    abstract public function title(): string;

    abstract protected function build(SheetBuilder $builder): void;

    public function builder(): SheetBuilder
    {
        if ($this->builder === null) {
            $this->builder = new SheetBuilder;
            $this->build($this->builder);
        }

        return $this->builder;
    }

    /** @return list<list<mixed>> */
    public function array(): array
    {
        return $this->builder()->rows();
    }

    /** @return array<class-string, callable> */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => fn (AfterSheet $event) => $this->builder()->apply($event->sheet->getDelegate()),
        ];
    }
}
