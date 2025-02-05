<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Ciclo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .info-general {
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        .representante-section {
            margin-bottom: 30px;
        }
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Detalles del Ciclo</h1>
    </div>

    <!-- Información General -->
    <div class="info-general">
        <h3>Información General</h3>
        <table>
            <tr>
                <th>Fecha de Inicio</th>
                <td>{{ $ciclo->fecha_inicio->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Estado</th>
                <td>{{ ucfirst($ciclo->status) }}</td>
            </tr>
            <tr>
                <th>Porcentaje Hospitalario</th>
                <td>{{ $ciclo->porcentaje_hospitalario }}%</td>
            </tr>
            @if($ciclo->delivered_at)
            <tr>
                <th>Fecha de Entrega</th>
                <td>{{ $ciclo->delivered_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Detalles por Representante -->
    <div>
        <h3>Detalles por Representante</h3>
        @foreach($detallesPorRepresentante as $representanteId => $detalles)
            <div class="representante-section">
                <h4>{{ $detalles->first()->representante->name }}</h4>
                <p>Total de doctores: {{ $detalles->first()->representante->doctors->sum('doctors_count') }}</p>
                
                <table>
                    <thead>
                        <tr>
                            <th>Especialidad</th>
                            <th>Producto</th>
                            <th>Cantidad por Doctor</th>
                            <th>Cantidad Total</th>
                            <th>Con % Hospitalario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detalles as $detalle)
                        <tr>
                            <td>{{ $detalle->especialidad->name }}</td>
                            <td>{{ $detalle->producto->name }}</td>
                            <td>{{ $detalle->cantidad_por_doctor }}</td>
                            <td>{{ $detalle->cantidad_total }}</td>
                            <td>{{ $detalle->cantidad_con_porcentaje }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>

    <!-- Resumen Total por Especialidad -->
    <div>
        <h3>Resumen Total por Especialidad</h3>
        <table>
            <thead>
                <tr>
                    <th>Especialidad</th>
                    <th>Total Productos Entregados</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalesPorEspecialidad = collect($detallesPorRepresentante)
                        ->flatten(1)
                        ->groupBy(function($detalle) {
                            return $detalle->especialidad->name;
                        })
                        ->map(function ($grupo) {
                            return $grupo->sum('cantidad_con_porcentaje');
                        });
                @endphp
                
                @foreach($totalesPorEspecialidad as $especialidad => $total)
                <tr>
                    <td>{{ $especialidad }}</td>
                    <td>{{ $total }}</td>
                </tr>
                @endforeach
                
                <tr class="total-row">
                    <td>Total General</td>
                    <td>{{ $totalesPorEspecialidad->sum() }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
