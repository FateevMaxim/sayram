<?php

namespace App\Imports;

use App\Models\TrackList;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Throwable;

class TracksImport implements ToModel, SkipsOnError, WithEvents
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
        if (isset($row[1])){
            return new TrackList([
                'track_code' => $row[1],
                'to_china' => $this->date,
                'status' => 'Получено в Китае',
                'reg_china' => 1,
                'created_at' => date(now()),
            ]);
        }
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
}
