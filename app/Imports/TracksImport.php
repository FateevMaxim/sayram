<?php

namespace App\Imports;

use App\Models\TrackList;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Throwable;

class TracksImport implements
    ToModel,
    WithBatchInserts,
    WithChunkReading,
    SkipsOnError
{
    use Importable;

    private string $date;
    private int    $counter = 0;

    public function __construct(string $date)
    {
        $this->date = $date;
    }

    /**
     * Возвращает модель для вставки или null, если строка пустая.
     */
    public function model(array $row)
    {
        // $row[0] ‑ обычно колонка А в Excel; адаптируйте при необходимости
        $trackCode = $row[1] ?? null;

        if (empty($trackCode)) {
            return null; // пропускаем пустые строки/заголовок
        }

        ++$this->counter;

        return new TrackList([
            'track_code' => $trackCode,
            'to_china'   => $this->date,
            'status'     => 'Получено в Китае',
            'reg_china'  => 1,
            'created_at' => Carbon::now(), // корректнее, чем date(now())
        ]);
    }

    /* ---------- Настройки пакетной работы ---------- */

    public function batchSize(): int
    {
        return 1000; // строк за один INSERT
    }

    public function chunkSize(): int
    {
        return 1000; // строк читаем за проход
    }

    /* ---------- Служебные методы ---------- */

    public function getRowCount(): int
    {
        return $this->counter;
    }

    public function onError(Throwable $e)
    {
        Log::error($e);
    }
}
