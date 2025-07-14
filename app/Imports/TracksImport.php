<?php

namespace App\Imports;

use App\Models\TrackList;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithReadFilter;
use Maatwebsite\Excel\Events\AfterImport;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use Throwable;

class TracksImport implements ToModel, SkipsOnError, WithEvents, WithChunkReading, WithBatchInserts, SkipsEmptyRows, WithReadFilter
{
    use Importable;
    private $date;
    private $rowCount = 0;

    public function __construct(string $date)
    {
        $this->date = $date;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Skip rows with empty track code
        if (empty($row[1])) {
            return null;
        }

        return new TrackList([
            'track_code' => $row[1],
            'to_china' => $this->date,
            'status' => 'Получено в Китае',
            'reg_china' => 1,
            'created_at' => date(now()),
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function(AfterImport $event) {
                $reader = $event->getReader();
                $this->rowCount = $reader->getTotalRows();
            },
        ];
    }

    public function getRowCount()
    {
        return $this->rowCount;
    }

    public function onError(Throwable $e)
    {
        // TODO: Implement onError() method.
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * @return IReadFilter
     */
    public function readFilter(): IReadFilter
    {
        return new class implements IReadFilter {
            public function readCell($columnAddress, $row, $worksheetName = '')
            {
                // Only read cells from the first sheet
                if ($worksheetName !== 'Worksheet' && $worksheetName !== 'Sheet1' && $worksheetName !== '') {
                    return false;
                }

                // Only read columns A and B (track codes are in column B)
                if ($columnAddress === 'A' || $columnAddress === 'B') {
                    return true;
                }

                return false;
            }
        };
    }
}
