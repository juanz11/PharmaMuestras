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
            margin-top: 20px;
            page-break-before: auto;
            page-break-inside: avoid;
        }
        .resumen-total-table th,
        .resumen-total-table td {
            padding: 6px 2px;
            font-size: 9px;
        }
        .resumen-total-table .representante-column {
            width: 15%;
            min-width: 100px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Ciclo de Distribución</h1>
        <p style="margin: 5px 0 0 0; color: #666;">Generado el {{ $ciclo->fecha_inicio->format('d/m/Y') }}</p>
        <h2 style="margin: 15px 0; font-size: 24px; text-align: center;">CICLO {{ $ciclo->id }}</h2>
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
                                    ({{ $detalles->first()->representante->doctors->where('medical_specialty_id', $especialidad->id)->sum('doctors_count') }} doctores)
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
                                            ({{ $detalle->cantidad_por_doctor }} x doctor)
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
        <div class="header">
            <h2 style="margin: 15px 0; font-size: 24px; text-align: center;">RESUMEN TOTAL - CICLO {{ $ciclo->id }}</h2>
        </div>
        
        @php
            $especialidades = \App\Models\MedicalSpecialty::whereIn('id', collect($detallesPorRepresentante)->flatten(1)->pluck('especialidad_id')->unique())->get();
            $productosPorEspecialidad = collect($detallesPorRepresentante)
                ->flatten(1)
                ->groupBy('especialidad_id')
                ->map(function($grupo) {
                    return $grupo->pluck('producto_id')->unique();
                });
        @endphp

        <table class="resumen-total-table">
            <thead>
                <tr>
                    <th class="representante-column">Representante</th>
                    @foreach($especialidades as $especialidad)
                        @php
                            $numProductos = $productosPorEspecialidad->get($especialidad->id, collect())->count();
                        @endphp
                        <th colspan="{{ $numProductos }}" class="specialty-header">
                            {{ $especialidad->name }}
                        </th>
                    @endforeach
                </tr>
                <tr>
                    <th></th>
                    @foreach($especialidades as $especialidad)
                        @foreach($productosPorEspecialidad->get($especialidad->id, collect()) as $productoId)
                            @php
                                $producto = \App\Models\Product::find($productoId);
                            @endphp
                            <th class="product-header" style="width: {{ 85 / collect($productosPorEspecialidad)->flatten()->count() }}%">
                                {{ $producto ? $producto->name : 'Producto eliminado' }}
                            </th>
                        @endforeach
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($detallesPorRepresentante as $representanteId => $detalles)
                    <tr>
                        <td>{{ $detalles->first()->representante->name }}</td>
                        @foreach($especialidades as $especialidad)
                            @foreach($productosPorEspecialidad->get($especialidad->id, collect()) as $productoId)
                                @php
                                    $detalle = $detalles->first(function($d) use ($especialidad, $productoId) {
                                        return $d->especialidad_id == $especialidad->id && $d->producto_id == $productoId;
                                    });
                                @endphp
                                <td style="text-align: center;">
                                    {{ $detalle ? round($detalle->cantidad_total) : '-' }}
                                </td>
                            @endforeach
                        @endforeach
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td>Hospitalario ({{ $ciclo->porcentaje_hospitalario }}%)</td>
                    @foreach($especialidades as $especialidad)
                        @foreach($productosPorEspecialidad->get($especialidad->id, collect()) as $productoId)
                            @php
                                $totalRegular = collect($detallesPorRepresentante)
                                    ->flatten(1)
                                    ->where('especialidad_id', $especialidad->id)
                                    ->where('producto_id', $productoId)
                                    ->sum('cantidad_total');
                                $totalHospitalario = $totalRegular * ($ciclo->porcentaje_hospitalario / 100);
                            @endphp
                            <td style="text-align: center;">
                                {{ $totalHospitalario > 0 ? round($totalHospitalario) : '-' }}
                            </td>
                        @endforeach
                    @endforeach
                </tr>
                <tr class="total-row">
                    <td>Total</td>
                    @foreach($especialidades as $especialidad)
                        @foreach($productosPorEspecialidad->get($especialidad->id, collect()) as $productoId)
                            @php
                                $totalRegular = collect($detallesPorRepresentante)
                                    ->flatten(1)
                                    ->where('especialidad_id', $especialidad->id)
                                    ->where('producto_id', $productoId)
                                    ->sum('cantidad_total');
                                $totalHospitalario = $totalRegular * ($ciclo->porcentaje_hospitalario / 100);
                                $granTotal = $totalRegular + $totalHospitalario;
                            @endphp
                            <td style="text-align: center;">
                                {{ $granTotal > 0 ? round($granTotal) : '-' }}
                            </td>
                        @endforeach
                    @endforeach
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
                    <th>Representante</th>
                    <th>Producto</th>
                    <th>Especialidad</th>
                    <th class="text-center">Regular</th>
                    <th class="text-center">Hosp.</th>
                    <th class="text-center">Valor</th>
                    <th class="text-center">Total</th>
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
                            <td>{{ $representante }}</td>
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
