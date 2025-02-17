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
        }
        .resumen-total-table .specialty-header {
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
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

    <!-- Detalles por Representante -->
    <div class="section-title">Detalles por Representante</div>
    @foreach($detallesPorRepresentante as $representanteId => $detalles)
        <div class="representante-box">
            <p class="representante-name">{{ $detalles->first()->representante->name }}</p>
            <p class="doctores-count">Total de doctores: {{ $detalles->first()->representante->doctors->sum('doctors_count') }}</p>
            
            @php
                $productos = $detalles->groupBy('producto_id');
                $especialidades = \App\Models\MedicalSpecialty::whereIn('id', $detalles->pluck('especialidad_id')->unique())->get();
            @endphp

            <table style="margin-top: 10px;">
                <thead>
                    <tr>
                        <th>Producto</th>
                        @foreach($especialidades as $especialidad)
                            <th class="specialty-header">
                                {{ $especialidad->name }}
                                <div style="font-size: 9px; font-weight: normal;">
                                    ({{ $detalles->first()->representante->doctors->where('medical_specialty_id', $especialidad->id)->sum('doctors_count') }}  und )
                                </div>
                            </th>
                        @endforeach
                        <th>Hospitalario</th>
                        <th>TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $productoId => $productoDetalles)
                        @php
                            $producto = \App\Models\Product::find($productoId);
                            $totalProducto = 0;
                        @endphp
                        <tr>
                            <td>{{ $producto ? $producto->name : 'Producto eliminado' }}</td>
                            @foreach($especialidades as $especialidad)
                                @php
                                    $detalle = $productoDetalles->where('especialidad_id', $especialidad->id)->first();
                                    if ($detalle) {
                                        $totalProducto += $detalle->cantidad_total;
                                    }
                                @endphp
                                <td style="text-align: center;">
                                    @if($detalle)
                                        {{ round($detalle->cantidad_total) }}
                                        <div style="font-size: 9px; color: #666;">
                                            ({{ $detalle->cantidad_por_doctor }} und)
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                            @endforeach
                            <td style="text-align: center;">
                                {{ round($totalProducto * ($ciclo->porcentaje_hospitalario / 100)) }}
                            </td>
                            <td style="text-align: center; font-weight: bold;">
                                {{ round($totalProducto * (1 + $ciclo->porcentaje_hospitalario / 100)) }}
                                @if($producto && $producto->value > 0)
                                    <div style="font-size: 9px; color: #666;">
                                        (${{ number_format(($totalProducto * (1 + $ciclo->porcentaje_hospitalario / 100)) * $producto->value, 2) }})
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

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
                        <th class="representante-column">Representante</th>
                        @foreach($productos as $producto)
                            <th class="product-header">
                                {{ $producto ? $producto->name : 'Producto eliminado' }}
                                @if($producto)
                                    <div style="font-size: 8px; color: #666;">
                                        @php
                                            $cantidadPorDoctor = collect($detallesPorRepresentante)
                                                ->flatten(1)
                                                ->where('producto_id', $producto->id)
                                                ->first()
                                                ->cantidad_por_doctor ?? 0;
                                        @endphp
                                        ({{ $cantidadPorDoctor }} und)
                                    </div>
                                @endif
                            </th>
                        @endforeach
                        <th style="text-align: right;">Valor Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detallesPorRepresentante as $representanteId => $detalles)
                        @php
                            $valorTotalRepresentante = 0;
                        @endphp
                        <tr>
                            <td>{{ $detalles->first()->representante->name }}</td>
                            @foreach($productos as $producto)
                                @php
                                    $totalProducto = $detalles
                                        ->where('producto_id', $producto->id)
                                        ->sum('cantidad_total');
                                    $valorProducto = $producto && $producto->valor ? $totalProducto * $producto->valor : 0;
                                    $valorTotalRepresentante += $valorProducto;
                                @endphp
                                <td style="text-align: center;">
                                    {{ $totalProducto > 0 ? round($totalProducto) : '-' }}
                                </td>
                            @endforeach
                            <td style="text-align: right;">
                                ${{ number_format($valorTotalRepresentante, 2) }}
                            </td>
                        </tr>
                    @endforeach

                    <!-- Fila de hospitalario -->
                    <tr style="background-color: #f8f9fa;">
                        <td>Hospitalario ({{ $ciclo->porcentaje_hospitalario }}%)</td>
                        @foreach($productos as $producto)
                            @php
                                $totalProducto = collect($detallesPorRepresentante)
                                    ->flatten(1)
                                    ->where('producto_id', $producto->id)
                                    ->sum('cantidad_total');
                                $hospitalario = round($totalProducto * ($ciclo->porcentaje_hospitalario / 100));
                                $valorHospitalario = $producto && $producto->valor ? $hospitalario * $producto->valor : 0;
                            @endphp
                            <td style="text-align: center;">
                                {{ $hospitalario > 0 ? $hospitalario : '-' }}
                            </td>
                        @endforeach
                        <td style="text-align: right;">
                            ${{ number_format($valorHospitalario, 2) }}
                        </td>
                    </tr>

                    <!-- Fila de totales -->
                    <tr style="background-color: #f8f9fa; font-weight: bold;">
                        <td>Total</td>
                        @php
                            $valorTotalGeneral = 0;
                        @endphp
                        @foreach($productos as $producto)
                            @php
                                $totalProducto = collect($detallesPorRepresentante)
                                    ->flatten(1)
                                    ->where('producto_id', $producto->id)
                                    ->sum('cantidad_total');
                                $granTotal = $totalProducto * (1 + ($ciclo->porcentaje_hospitalario / 100));
                                $valorTotal = $producto && $producto->valor ? $granTotal * $producto->valor : 0;
                                $valorTotalGeneral += $valorTotal;
                            @endphp
                            <td style="text-align: center;">
                                {{ $granTotal > 0 ? round($granTotal) : '-' }}
                            </td>
                        @endforeach
                        <td style="text-align: right; font-weight: bold;">
                            ${{ number_format($valorTotalGeneral, 2) }}
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
                            <th style="text-align: center;">Total Entregados</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $resumenPorProducto = collect($detallesPorRepresentante)
                                ->flatten(1)
                                ->groupBy('producto_id')
                                ->map(function ($grupo) {
                                    $cantidadTotal = $grupo->sum('cantidad_total');
                                    return $cantidadTotal;
                                });
                        @endphp
                        
                        @foreach($resumenPorProducto as $productoId => $total)
                            @php
                                $producto = \App\Models\Product::find($productoId);
                                $totalConHospitalario = $total * (1 + $ciclo->porcentaje_hospitalario / 100);
                            @endphp
                            <tr>
                                <td>{{ $producto ? $producto->name : 'Producto eliminado' }}</td>
                                <td style="text-align: center;">
                                    {{ round($totalConHospitalario) }}
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
                            <td style="text-align: center;">
                                {{ round($resumenPorProducto->sum() * (1 + $ciclo->porcentaje_hospitalario / 100)) }}
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

            <div class="section-title" style="margin-top: 40px;">Valor de Productos Entregados</div>
            <table class="table">
                <thead>
                    <tr>
                        <th class="representante-column">Representante</th>
                        <th class="producto-column">Producto</th>
                        <th class="producto-column">Especialidad</th>
                        <th class="cantidad-column">Regular</th>
                        <th class="cantidad-column">Hosp.</th>
                        <th class="valor-column">Valor</th>
                        <th class="valor-column">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalRegularGeneral = 0;
                        $totalHospitalarioGeneral = 0;
                        $totalValorGeneral = 0;
                        $totalGeneral = 0;
                    @endphp

                    @foreach($detallesPorRepresentante as $representante => $detalles)
                        @php
                            $subtotalRegular = 0;
                            $subtotalHospitalario = 0;
                            $subtotalValor = 0;
                            $subtotal = 0;
                        @endphp

                        @foreach($detalles as $detalle)
                            @php
                                $cantidadRegular = $detalle->cantidad_total;
                                $cantidadHospitalaria = round($detalle->cantidad_total * ($ciclo->porcentaje_hospitalario / 100));
                                $total = $cantidadRegular + $cantidadHospitalaria;
                                $valor = $detalle->producto ? $total * floatval($detalle->producto->valor) : 0;
                                
                                $subtotalRegular += $cantidadRegular;
                                $subtotalHospitalario += $cantidadHospitalaria;
                                $subtotalValor += $valor;
                                $subtotal += $total;
                            @endphp
                            <tr>
                                <td>{{ App\Models\Representative::find($representante)->name }}</td>
                                <td>{{ $detalle->producto ? $detalle->producto->name : 'Producto eliminado' }}</td>
                                <td>{{ $detalle->producto && $detalle->producto->medicalSpecialties->isNotEmpty() ? $detalle->producto->medicalSpecialties->first()->name : 'Sin especialidad' }}</td>
                                <td class="text-center">{{ $cantidadRegular }}</td>
                                <td class="text-center">{{ $cantidadHospitalaria }}</td>
                                <td class="text-center">${{ number_format($valor, 2) }}</td>
                                <td class="text-center">{{ $total }}</td>
                            </tr>
                        @endforeach

                        @php
                            $totalRegularGeneral += $subtotalRegular;
                            $totalHospitalarioGeneral += $subtotalHospitalario;
                            $totalValorGeneral += $subtotalValor;
                            $totalGeneral += $subtotal;
                        @endphp

                      
                    @endforeach

                    <tr class="total">
                        <td colspan="3"><strong>TOTAL GENERAL</strong></td>
                        <td class="text-center"><strong>{{ $totalRegularGeneral }}</strong></td>
                        <td class="text-center"><strong>{{ $totalHospitalarioGeneral }}</strong></td>
                        <td class="text-center"><strong>${{ number_format($totalValorGeneral, 2) }}</strong></td>
                        <td class="text-center"><strong>{{ $totalGeneral }}</strong></td>
                    </tr>
                </tbody>
            </table>

    <div class="footer">
        {{ now()->year }} Sistema de Gestión de Muestras Médicas - Página 1
    </div>
</body>
</html>
