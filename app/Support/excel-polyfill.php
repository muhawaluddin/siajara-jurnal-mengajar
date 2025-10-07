<?php

namespace Maatwebsite\Excel;

if (class_exists(Excel::class)) {
    return;
}

use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

interface Downloadable
{
    public function download(string $fileName);
}

class Excel
{
    public function import($import, UploadedFile|string $file): void
    {
        if (! $import instanceof Concerns\ToCollection) {
            throw new RuntimeException('Import harus mengimplementasikan ToCollection.');
        }

        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if ($path === false || $path === null) {
            throw new RuntimeException('File tidak dapat diakses.');
        }

        $extension = strtolower(pathinfo($file instanceof UploadedFile ? $file->getClientOriginalName() : (string) $path, PATHINFO_EXTENSION));

        $rows = match ($extension) {
            'csv' => $this->importCsv($path),
            'xlsx' => $this->importXlsx($path),
            default => throw new RuntimeException('Format file tidak didukung. Gunakan CSV atau XLSX.'),
        };

        if ($rows->isEmpty()) {
            $import->collection(collect());

            return;
        }

        $preparedRows = $this->prepareRows($rows, $import instanceof Concerns\WithHeadingRow);

        $import->collection($preparedRows);
    }

    public function download(object $export, string $fileName)
    {
        if ($export instanceof Concerns\FromView) {
            $view = $export->view();

            if (! $view instanceof ViewContract) {
                throw new RuntimeException('Export view harus mengembalikan instance View.');
            }

            $content = $view->render();

            return Response::make($content, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
        }

        if ($export instanceof Downloadable) {
            return $export->download($fileName);
        }

        throw new RuntimeException('Tipe export tidak didukung pada polyfill Excel.');
    }

    private function importCsv(string $path): Collection
    {
        $rows = collect();

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('File CSV tidak dapat dibuka.');
        }

        while (($data = fgetcsv($handle)) !== false) {
            $rows->push(array_map(fn ($value) => $this->normalizeCellValue($value), $data));
        }

        fclose($handle);

        return $rows;
    }

    private function importXlsx(string $path): Collection
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('File XLSX tidak dapat dibuka.');
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');

        if ($sheetXml === false) {
            $zip->close();

            throw new RuntimeException('Sheet utama tidak ditemukan pada file XLSX.');
        }

        $sharedStrings = $this->parseSharedStrings($zip->getFromName('xl/sharedStrings.xml'));

        $sheet = simplexml_load_string($sheetXml);

        if ($sheet === false) {
            $zip->close();

            throw new RuntimeException('Sheet XLSX tidak valid.');
        }

        $rows = collect();

        foreach ($sheet->sheetData->row as $row) {
            $cells = [];
            $maxIndex = -1;

            foreach ($row->c as $cell) {
                $reference = (string) $cell['r'];
                $columnLetters = preg_replace('/\d+/', '', $reference);
                $columnIndex = $this->columnIndexFromString($columnLetters ?? 'A');

                $maxIndex = max($maxIndex, $columnIndex);

                $type = (string) $cell['t'];
                $rawValue = (string) ($cell->v ?? '');

                if ($type === 's') {
                    $value = $sharedStrings[(int) $rawValue] ?? '';
                } else {
                    $value = $rawValue;
                }

                $cells[$columnIndex] = $this->normalizeCellValue($value);
            }

            $normalizedRow = [];

            for ($i = 0; $i <= $maxIndex; $i++) {
                $normalizedRow[] = $cells[$i] ?? null;
            }

            $rows->push($normalizedRow);
        }

        $zip->close();

        return $rows;
    }

    /**
     * @param Collection<int, array<int, string|null>> $rows
     * @return Collection<int, Collection<int|string, mixed>>
     */
    private function prepareRows(Collection $rows, bool $withHeadingRow): Collection
    {
        if (! $withHeadingRow) {
            return $rows->map(fn (array $row) => collect($row));
        }

        $heading = collect($rows->shift() ?? []);

        $keys = $heading->map(function ($value) {
            if ($value === null || $value === '') {
                return null;
            }

            return Str::slug((string) $value, '_');
        })->all();

        return $rows->map(function (array $row) use ($keys) {
            $assoc = [];

            foreach ($keys as $index => $key) {
                if ($key === null || $key === '') {
                    continue;
                }

                $assoc[$key] = $row[$index] ?? null;
            }

            return collect($assoc);
        });
    }

    /** @return array<int, string> */
    private function parseSharedStrings(string|false $xml): array
    {
        if ($xml === false) {
            return [];
        }

        $document = simplexml_load_string($xml);

        if (! $document instanceof SimpleXMLElement) {
            return [];
        }

        $document->registerXPathNamespace('s', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $elements = $document->xpath('//s:si');

        if (! is_array($elements)) {
            return [];
        }

        $strings = [];

        foreach ($elements as $index => $item) {
            $text = '';

            if ($item instanceof SimpleXMLElement) {
                $item->registerXPathNamespace('s', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            }

            foreach ($item->xpath('.//s:t') ?? [] as $segment) {
                $text .= (string) $segment;
            }

            $strings[(int) $index] = $this->normalizeCellValue($text);
        }

        return $strings;
    }

    private function normalizeCellValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            $value = (string) $value;
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed === '' ? null : $trimmed;
        }

        return trim((string) $value);
    }

    private function columnIndexFromString(string $letters): int
    {
        $letters = strtoupper($letters);
        $length = strlen($letters);
        $index = 0;

        for ($i = 0; $i < $length; $i++) {
            $index *= 26;
            $index += ord($letters[$i]) - ord('A') + 1;
        }

        return max(0, $index - 1);
    }
}

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Support\Collection;

interface ToCollection
{
    public function collection(Collection $rows): void;
}

interface WithHeadingRow
{
}

interface FromView
{
    public function view(): \Illuminate\Contracts\View\View;
}

namespace Maatwebsite\Excel\Facades;

use Illuminate\Support\Facades\Facade;

class Excel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'excel';
    }
}
