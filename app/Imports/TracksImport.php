<?php

namespace App\Imports;

use App\Models\TrackList;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithReadDataOnly;
use Maatwebsite\Excel\Concerns\WithLimit;
use Throwable;

class TracksImport implements
    ToModel,
    WithBatchInserts,
    WithChunkReading,
    WithReadDataOnly,   // <-- добавлено
    WithLimit,          // <-- добавлено
    SkipsOnError
{
    use Importable;

    private string $date;
    private int    $counter = 0;

    public function __construct(string $date)
    {
        $this->date = $date;
    }

    public function readDataOnly(): bool
    {
        return true;          // <-- обязательный метод интерфейса
    }

    public function model(array $row)
    {
        $trackCode = $row[1] ?? null;      // при необходимости поправьте индекс

        if (empty($trackCode)) {
            return null;
        }

        ++$this->counter;

        return new TrackList([
            'track_code' => $trackCode,
            'to_china'   => $this->date,
            'status'     => 'Получено в Китае',
            'reg_china'  => 1,
            'created_at' => Carbon::now(),
        ]);
    }

    /* ---------- Ограничения ---------- */

    public function limit(): int
    {
        // читаем не более 500 строк (с запасом)
        return 500;
    }

    public function batchSize(): int { return 200; }
    public function chunkSize(): int { return 200; }

    /* ---------- Служебные ---------- */

    public function getRowCount(): int { return $this->counter; }

    public function onError(Throwable $e) {\Log::error($e); }
}
