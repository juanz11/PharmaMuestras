<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Ciclo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 6px 4px;
            font-size: 10px;
            word-wrap: break-word;
            max-width: 150px;
        }
        th {
            border: 1px solid #dee2e6;
            padding: 6px 4px;
            font-size: 10px;
            word-wrap: break-word;
            max-width: 150px;
        }
        .specialty-header {
            background-color: #f8f9fa;
            border-left: 2px solid #dee2e6;
            text-align: center;
            font-size: 11px;
            padding: 8px 4px;
        }
        .product-header {
            background-color: #fff;
            border-left: 1px solid #dee2e6;
            font-size: 9px;
            text-align: center;
            padding: 6px 2px;
        }
        .resumen-total-table {
            margin-top: 10px;
            page-break-inside: avoid;
            font-size: 8px;
            width: 100%;
            table-layout: fixed;
            margin-left: auto;
            margin-right: auto;
            border: 2px solid #000;
        }
        .resumen-total-table th,
        .resumen-total-table td {
            text-align: center;
            padding: 3px 2px;
            white-space: normal;
            word-wrap: break-word;
            vertical-align: middle;
            height: auto;
            min-width: 35px;
            border: 1.5px solid #000;
        }
        .resumen-total-table .specialty-header {
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            border-bottom: 2px solid #000;
            padding: 3px 1px;
            white-space: normal;
            word-wrap: break-word;
        }
        .resumen-total-table .product-header {
            font-size: 8px;
            padding: 2px 1px;
            white-space: normal;
            word-wrap: break-word;
            height: auto;
        }
        .resumen-total-table .representante-column {
            width: 100px;
            text-align: left;
            padding-left: 5px;
        }
        .resumen-total-table .specialty-group {
            text-align: center;
        }
        .resumen-total-table .number-column {
            width: 25px;
            text-align: center;
        }
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            color: #2c3e50;
        }
        .representante-box {
            border: 1px solid #dee2e6;
            padding: 10px;
            margin-bottom: 15px;
            background: #f8f9fa;
        }
        .representante-name {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
        }
        .doctores-count {
            font-size: 11px;
            color: #666;
            margin: 5px 0 0 0;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #666;
            padding: 10px 0;
            border-top: 1px solid #dee2e6;
        }
        @page {
            size: legal landscape;
            margin: 5px;
        }
        thead tr:first-child {
            background-color: #1e3a8a !important;
            color: white !important;
        }
        thead tr:first-child th {
            background-color: #1e3a8a !important;
            color: white !important;
        }
    </style>
</head>
<body>
    @php
        use App\Helpers\NumberToRoman;
        $numero = preg_match('/Ciclo (\d+)/', $ciclo->nombre ?: 'Ciclo ' . $ciclo->id, $matches) ? $matches[1] : $ciclo->id;
        $numeroRomano = NumberToRoman::convert($numero);
    @endphp

    <div class="header">
        <h1>Reporte de Ciclo de Distribución</h1>
        <p style="margin: 5px 0 0 0; color: #666;">Generado el {{ $ciclo->fecha_inicio->format('d/m/Y') }}</p>
        <h2 style="margin: 15px 0; font-size: 24px; text-align: center;">CICLO {{ $numeroRomano }}</h2>
    </div>

    <!-- Información General -->
    <div class="section-title">Información General</div>
    <table>
        <tr>
            <th style="width: 30%;">Fecha:</th>
            <td>{{ $ciclo->fecha_inicio->format('d/m/Y') }}</td>
        </tr>
       
        <tr>
            <th>Porcentaje Hospitalario</th>
            <td>{{ $ciclo->porcentaje_hospitalario }}%</td>
        </tr>
       
    </table>

    <!-- Resumen Total -->
    <div style="page-break-before: always;">
        <div class="resumen-total-section">
            <div class="header">
                <h2 style="margin: 15px 0; font-size: 24px; text-align: center;">RESUMEN TOTAL - CICLO {{ $numeroRomano }}</h2>
            </div>
            
            @php
                // Obtener todos los productos únicos usados en el ciclo
                $productos = collect($detallesPorRepresentante)
                    ->flatten(1)
                    ->pluck('producto_id')
                    ->unique()
                    ->map(function($id) {
                        return \App\Models\Product::find($id);
                    });
            @endphp

            <table class="resumen-total-table">
                <thead>
                    <tr>
                        <th class="representante-column" style="text-align: center;"># Representante</th>
                        @foreach($productos as $producto)
                            <th class="product-header">
                                {{ $producto ? $producto->name : 'Producto eliminado' }}
                                @if($producto)
                                    <div style="font-size: 8px; color: white;">
                                        @php
                                            $cantidadPorDoctor = collect($detallesPorRepresentante)
                                                ->flatten(1)
                                                ->where('producto_id', $producto->id)
                                                ->first()
                                                ->cantidad_por_doctor ?? 0;
                                        @endphp
                                       
                                    </div>
                                @endif
                            </th>
                        @endforeach
                        <th style="text-align: right;">Costo Total</th>
                        <th style="text-align: right; font-weight: bold;">Total General</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detallesPorRepresentante as $representanteId => $detalles)
                        @php
                            $valorTotalRepresentante = 0;
                            $cantidadesProductos = [];
                            $valoresProductos = [];
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}. {{ $detalles->first()->representante->name }}</td>
                            @foreach($productos as $producto)
                                @php
                                    $totalProducto = $detalles
                                        ->where('producto_id', $producto->id)
                                        ->sum('cantidad_total');
                                    $totalProductoConHospitalario = $totalProducto * (1 + $ciclo->porcentaje_hospitalario / 100);
                                    $valorProducto = $producto && $producto->valor ? $totalProductoConHospitalario * $producto->valor : 0;
                                    $valoresProductos[] = $valorProducto;
                                    $cantidadesProductos[] = $totalProductoConHospitalario;
                                @endphp
                                <td style="text-align: center;">
                                    {{ $totalProductoConHospitalario > 0 ? round($totalProductoConHospitalario) : '-' }}
                                </td>
                            @endforeach
                            @php
                                $valorTotalRepresentante = array_sum($valoresProductos);
                                $cantidadTotalFila = array_sum(array_map('round', $cantidadesProductos));
                            @endphp
                            <td style="text-align: right;">${{ number_format($valorTotalRepresentante, 2) }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ number_format($cantidadTotalFila) }}</td>
                        </tr>
                    @endforeach

                    <!-- Fila de totales -->
                    <tr style="background-color: #f8f9fa;">
                        <td class="representante-column">Total</td>
                        @php
                            $totalesColumnas = [];
                            $valorTotalGeneral = 0;
                            
                            // Primero calculamos los totales redondeados por columna
                            foreach($productos as $producto) {
                                $totalColumna = 0;
                                foreach($detallesPorRepresentante as $detalles) {
                                    $totalProducto = $detalles
                                        ->where('producto_id', $producto->id)
                                        ->sum('cantidad_total');
                                    $totalConHospitalario = $totalProducto * (1 + ($ciclo->porcentaje_hospitalario / 100));
                                    $totalColumna += round($totalConHospitalario);
                                }
                                $totalesColumnas[] = $totalColumna;
                                
                                // Calculamos el valor total general
                                if ($producto && $producto->valor) {
                                    $valorTotalGeneral += $totalColumna * $producto->valor;
                                }
                            }
                        @endphp
                        
                        @foreach($totalesColumnas as $total)
                            <td style="text-align: center;">
                                {{ $total > 0 ? $total : '-' }}
                            </td>
                        @endforeach
                        <td style="text-align: right; font-weight: bold;">
                            ${{ number_format($valorTotalGeneral, 2) }}
                        </td>
                        <td style="text-align: right; font-weight: bold;">
                            {{ array_sum($totalesColumnas) }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div style="page-break-before: always;">
                <!-- Productos Entregados -->
                <div class="section-title" style="margin-top: 40px;">Productos Entregados</div>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th style="text-align: center;">Cantidad Base</th>
                            <th style="text-align: center;">Hospitalario ({{ $ciclo->porcentaje_hospitalario }}%)</th>
                            <th style="text-align: center;">Total Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $resumenPorProducto = collect($detallesPorRepresentante)
                                ->flatten(1)
                                ->groupBy('producto_id')
                                ->map(function ($grupo) {
                                    return $grupo->sum('cantidad_total');
                                });
                        @endphp
                        
                        @foreach($resumenPorProducto as $productoId => $total)
                            @php
                                $producto = \App\Models\Product::find($productoId);
                                $cantidadHospitalaria = round($total * ($ciclo->porcentaje_hospitalario / 100));
                                $totalConHospitalario = $total + $cantidadHospitalaria;
                            @endphp
                            <tr>
                                <td>{{ $producto ? $producto->name : 'Producto eliminado' }}</td>
                                <td style="text-align: center;">{{ number_format($total) }}</td>
                                <td style="text-align: center;">{{ number_format($cantidadHospitalaria) }}</td>
                                <td style="text-align: center;">
                                    {{ number_format($totalConHospitalario) }}
                                    @if($producto && $producto->value > 0)
                                        <div style="font-size: 9px; color: #666;">
                                            (${{ number_format($totalConHospitalario * $producto->value, 2) }})
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        
                        <tr class="total-row">
                            <td>Total General</td>
                            <td style="text-align: center;">{{ number_format($resumenPorProducto->sum()) }}</td>
                            <td style="text-align: center;">
                                {{ number_format($resumenPorProducto->sum() * ($ciclo->porcentaje_hospitalario / 100)) }}
                            </td>
                            <td style="text-align: center;">
                                {{ number_format($resumenPorProducto->sum() * (1 + $ciclo->porcentaje_hospitalario / 100)) }}
                                @php
                                    $valorTotal = $resumenPorProducto->map(function($cantidad, $productoId) use ($ciclo) {
                                        $producto = \App\Models\Product::find($productoId);
                                        return $producto ? $cantidad * (1 + $ciclo->porcentaje_hospitalario / 100) * $producto->value : 0;
                                    })->sum();
                                @endphp
                                @if($valorTotal > 0)
                                    <div style="font-size: 9px; color: #666;">
                                        (${{ number_format($valorTotal, 2) }})
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

    <div class="footer">
        {{ now()->year }} Sistema de Gestión de Muestras Médicas - Página 1
    </div>
</body>
</html>
