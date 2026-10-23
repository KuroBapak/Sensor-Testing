<?php

namespace App\Exports;

/**
 * The "Petunjuk" sheet of the Batch Add example: numbered steps, then for each
 * data sheet one row per column saying whether it is required and what it accepts.
 */
class InstructionsSheet extends BuilderSheet
{
    /**
     * @param  list<string>  $steps
     * @param  list<array{title: string, columns: list<array{0: string, 1: string, 2: string, 3: string}>}>  $tables  column rows are: name, required, rule, example
     */
    public function __construct(
        private readonly string $sheetTitle,
        private readonly string $heading,
        private readonly array $steps,
        private readonly array $tables,
    ) {}

    public function title(): string
    {
        return $this->sheetTitle;
    }

    protected function build(SheetBuilder $builder): void
    {
        $builder->section($this->heading);

        foreach ($this->steps as $number => $step) {
            $builder->paragraph(($number + 1).'. '.$step);
        }

        foreach ($this->tables as $table) {
            $builder->blank()
                ->section($table['title'])
                ->header(['Kolom', 'Wajib', 'Aturan isian', 'Contoh']);

            foreach ($table['columns'] as $column) {
                $builder->row($column);
            }
        }

        $builder->withoutAutoFilter()->wrapTable();
    }
}
