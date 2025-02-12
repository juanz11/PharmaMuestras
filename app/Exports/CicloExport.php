<?php

namespace App\Exports;

use App\Models\Ciclo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class CicloExport implements FromCollection, WithStyles, WithTitle, WithEvents
{
    protected $ciclo;
    protected $data;
    protected $especialidades;
    protected $productosPorEspecialidad;
    protected $columnMapping;

    public function __construct(Ciclo $ciclo)
    {
        $this->ciclo = $ciclo;
        $this->ciclo->load([
            'detallesCiclo.representante', // Cargar solo el representante
            'detallesCiclo.especialidad',
            'detallesCiclo.producto'
        ]);
        
        // Obtener especialidades únicas ordenadas
        $this->especialidades = $this->ciclo->detallesCiclo
            ->pluck('especialidad')
            ->unique('id')
            ->sortBy('name')
            ->values();

        // Agrupar productos por especialidad
        $this->productosPorEspecialidad = [];
        foreach ($this->especialidades as $especialidad) {
            $this->productosPorEspecialidad[$especialidad->id] = $this->ciclo->detallesCiclo
                ->where('especialidad_id', $especialidad->id)
                ->pluck('producto')
                ->unique('id')
                ->sortBy('name')
                ->values();
        }

        // Crear mapeo de columnas
        $this->createColumnMapping();
        $this->prepareData();
    }

    protected function createColumnMapping()
    {
        $this->columnMapping = [];
        $currentColumn = 3; // Empezamos después de #, Representante y Zona

        foreach ($this->especialidades as $especialidad) {
            $productos = $this->productosPorEspecialidad[$especialidad->id];
            foreach ($productos as $producto) {
                $this->columnMapping["{$especialidad->id}_{$producto->id}"] = $currentColumn++;
            }
        }
    }

    protected function prepareData()
    {
        $this->data = new Collection();

        // Primera fila - Nombre de especialidades
        $headerRow1 = array_fill(0, count($this->columnMapping) + 3, '');
        $headerRow1[0] = 'MUESTRAS';
        $currentCol = 3;
        foreach ($this->especialidades as $especialidad) {
            $productos = $this->productosPorEspecialidad[$especialidad->id];
            $headerRow1[$currentCol] = strtoupper($especialidad->name);
            $currentCol += count($productos);
        }
        $this->data->push($headerRow1);

        // Segunda fila - Productos
        $headerRow2 = ['#', 'Representante', 'Zona'];
        foreach ($this->especialidades as $especialidad) {
            foreach ($this->productosPorEspecialidad[$especialidad->id] as $producto) {
                $headerRow2[] = $producto->name;
            }
        }
        $this->data->push($headerRow2);

        // Datos de representantes
        $counter = 1;
        $detalles = $this->ciclo->detallesCiclo->groupBy('representante_id');
        
        foreach ($detalles as $representanteId => $grupoDetalles) {
            $representante = $grupoDetalles->first()->representante;
            
            // Inicializar fila con valores en 0
            $row = array_fill(0, count($this->columnMapping) + 3, 0);
            $row[0] = $counter;
            $row[1] = $representante->name;
            $row[2] = $representante->zone ?? ''; // Usar el campo zone directamente
            
            // Llenar cantidades
            foreach ($grupoDetalles as $detalle) {
                $columnIndex = $this->columnMapping["{$detalle->especialidad_id}_{$detalle->producto_id}"];
                $row[$columnIndex] = $detalle->cantidad_total;
            }
            
            $this->data->push($row);
            $counter++;
        }

        // Calcular totales incluyendo hospitalario
        $totalRow = array_fill(0, count($this->columnMapping) + 3, 0);
        $totalRow[0] = '';
        $totalRow[1] = 'Total';
        $totalRow[2] = '';

        foreach ($this->ciclo->detallesCiclo as $detalle) {
            $columnIndex = $this->columnMapping["{$detalle->especialidad_id}_{$detalle->producto_id}"];
            $cantidadNormal = $detalle->cantidad_total;
            $cantidadHospitalaria = 0;
            
            if ($this->ciclo->porcentaje_hospitalario > 0 && $cantidadNormal > 0) {
                $cantidadHospitalaria = max(1, round($cantidadNormal * ($this->ciclo->porcentaje_hospitalario / 100)));
            }
            
            $totalRow[$columnIndex] = $cantidadNormal + $cantidadHospitalaria;
        }

        // Fila de hospitalario (antes del total)
        if ($this->ciclo->porcentaje_hospitalario > 0) {
            $hospitalRow = array_fill(0, count($this->columnMapping) + 3, 0);
            $hospitalRow[0] = '';
            $hospitalRow[1] = 'Hospitalario';
            $hospitalRow[2] = $this->ciclo->porcentaje_hospitalario . '%';

            foreach ($this->ciclo->detallesCiclo as $detalle) {
                $columnIndex = $this->columnMapping["{$detalle->especialidad_id}_{$detalle->producto_id}"];
                $cantidad = $detalle->cantidad_total;
                if ($cantidad > 0) {
                    $hospitalRow[$columnIndex] = max(1, round($cantidad * ($this->ciclo->porcentaje_hospitalario / 100)));
                }
            }

            $this->data->push($hospitalRow);
        }

        // Agregar fila de totales
        $this->data->push($totalRow);
    }

    public function collection()
    {
        return $this->data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Ciclo ' . $this->ciclo->id;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();

                // Aplicar bordes a todas las celdas
                $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Centrar el contenido
                $sheet->getStyle('A1:' . $lastColumn . $lastRow)
                      ->getAlignment()
                      ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Ajustar el ancho de las columnas
                foreach(range('A', $lastColumn) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // Combinar celdas para especialidades
                $currentCol = 'D';
                foreach ($this->especialidades as $especialidad) {
                    $productos = $this->productosPorEspecialidad[$especialidad->id];
                    $endCol = chr(ord($currentCol) + count($productos) - 1);
                    if ($currentCol !== $endCol) {
                        $sheet->mergeCells($currentCol . '1:' . $endCol . '1');
                    }
                    $currentCol = chr(ord($endCol) + 1);
                }

                // Colorear la fila de totales en verde
                $sheet->getStyle('A' . $lastRow . ':' . $lastColumn . $lastRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('90EE90');

                // Colorear las celdas de especialidades
                $sheet->getStyle('A1:' . $lastColumn . '1')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E0E0E0');

                // Colorear la fila de hospitalario en gris claro
                if ($this->ciclo->porcentaje_hospitalario > 0) {
                    $hospitalRow = $lastRow - 1;
                    $sheet->getStyle('A' . $hospitalRow . ':' . $lastColumn . $hospitalRow)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F0F0F0');
                }
            },
        ];
    }
}
