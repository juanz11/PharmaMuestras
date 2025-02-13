<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nota de Entrega - {{ $ciclo->fecha_inicio }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 15px;
        }
        .invoice-info {
            margin-bottom: 20px;
        }
        .invoice-info table {
            width: 100%;
            font-size: 11px;
        }
        .invoice-info td {
            padding: 3px;
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        .products-table th, .products-table td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
            word-wrap: break-word;
            max-width: 150px;
        }
        .products-table th {
            background-color: #f8f9fa;
            font-size: 10px;
            text-align: center;
            white-space: normal;
        }
        .totals {
            margin-top: 20px;
            text-align: right;
        }
        .signature {
            margin-top: 30px;
            text-align: center;
        }
        .signature-line {
            width: 200px;
            border-top: 1px solid #000;
            margin: 10px auto;
        }
        .page-break {
            page-break-after: always;
        }
        .representative-info {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
            font-size: 11px;
        }
        .representative-info h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .header h2 {
            font-size: 16px;
            margin: 10px 0;
        }
        .products-table td {
            font-size: 10px;
            padding: 4px 6px;
        }
        .products-table td:first-child {
            max-width: 120px;
        }
        .products-table th {
            padding: 6px 4px;
        }
    </style>
</head>
<body>
    @foreach($ciclo->getRepresentativesWithProducts() as $representative)
    <div class="invoice-page">
        <div class="header">
            <img src="{{ public_path('images/logo/logo.png') }}" alt="Logo" class="logo" style="width: 150px; height: auto;">
            <h2>Nota de Entrega de Muestras Medicas</h2>
        </div>

        <div class="representative-info">
            <h3>Información del Representante</h3>
            <table>
                <tr>
                    <td><strong>Nombre:</strong></td>
                    <td>{{ $representative->name }}</td>
                    <td><strong>Zona:</strong></td>
                    <td>{{ $representative->zone }}</td>
                    <td><strong>Ciclo:</strong></td>
                    <td>Ciclo {{ numberToRoman(intval(preg_replace('/[^0-9]/', '', $ciclo->nombre))) }}</td>
                </tr>
            </table>
        </div>

        <div class="invoice-info">
            <table>
                <tr>
                 
                    <td><strong>Fecha:</strong></td>
                    <td>{{ $ciclo->fecha_inicio->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td><strong>N° Descargo:</strong></td>
                    <td> {{ optional($ciclo->descargos->where('representante_id', $representative->id)->first())->numero_descargo ?? 'No especificado' }}</td>
                    <td><strong>Hospitalario:</strong></td>
                    <td>{{ $ciclo->porcentaje_hospitalario }}%</td>
                </tr>
            </table>
        </div>

        <table class="products-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Producto</th>
                    @php
                        // Obtener solo las especialidades específicamente seleccionadas para este ciclo y representante
                        $especialidades = $ciclo->detalles()
                            ->where('representante_id', $representative->id)
                            ->with('especialidad')
                            ->get()
                            ->pluck('especialidad')
                            ->unique('id')
                            ->sortBy('name');
                    @endphp
                    @foreach($especialidades as $especialidad)
                        <th style="text-align: center; width: {{ 50 / $especialidades->count() }}%;">{{ $especialidad->name }}</th>
                    @endforeach
                    <th style="text-align: center; width: 10%;">Hosp.</th>
                    <th style="text-align: center; width: 10%;">Valor</th>
                    <th style="text-align: center; width: 10%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $productos = $ciclo->detalles()
                        ->where('representante_id', $representative->id)
                        ->with(['producto' => function($query) {
                            $query->select('id', 'name', 'valor');
                        }, 'especialidad'])
                        ->get()
                        ->groupBy('producto_id');
                @endphp

                @foreach($productos as $productoId => $detalles)
                    @php
                        $producto = $detalles->first()->producto;
                        $totalRegular = 0;
                    @endphp
                    <tr>
                        <td>{{ $producto ? $producto->name : 'Producto eliminado' }}</td>
                        @foreach($especialidades as $especialidad)
                            @php
                                $detalle = $detalles->first(function($d) use ($especialidad) {
                                    return $d->especialidad_id === $especialidad->id;
                                });
                                if ($detalle) {
                                    $totalRegular += $detalle->cantidad_total;
                                }
                            @endphp
                            <td style="text-align: center;">
                                {{ $detalle ? $detalle->cantidad_total : '-' }}
                            </td>
                        @endforeach
                        @php
                            $hospitalario = $totalRegular * ($ciclo->porcentaje_hospitalario / 100);
                            $total = $totalRegular + $hospitalario;
                            $valor = $producto ? $total * floatval($producto->valor) : 0;
                        @endphp
                        <td style="text-align: center;">{{ round($hospitalario) }}</td>
                        <td style="text-align: center;">${{ number_format($valor, 2) }}</td>
                        <td style="text-align: center;">{{ round($total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="signature">
            <div class="signature-line"></div>
            <p>Firma del Representante</p>
            <p>{{ $representative->name }}</p>
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    </div>
    @endforeach

    @php
    function numberToRoman($number) {
        $map = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1
        ];
        $result = '';
        foreach ($map as $roman => $value) {
            while ($number >= $value) {
                $result .= $roman;
                $number -= $value;
            }
        }
        return $result;
    }
    @endphp
</body>
</html>
