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
            margin: 20px;
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
        .info-general {
            margin-bottom: 30px;
        }
        .info-general h3, .representante-section h4 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
            font-size: 12px;
        }
        th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #f2f2f2;
        }
        .representante-section {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }
        .representante-section h4 {
            color: #2c3e50;
            margin-top: 20px;
            font-size: 16px;
        }
        .total-row {
            background-color: #3498db !important;
            color: white;
            font-weight: bold;
        }
        .total-row td {
            border-color: #2980b9;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 10px;
            margin-bottom: 15px;
        }
        .section-title {
            background: #2c3e50;
            color: white;
            padding: 10px 15px;
            margin: 20px 0;
            border-radius: 3px;
            font-size: 16px;
        }
        .page-number {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-top: 20px;
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
        <p style="margin: 5px 0 0 0; color: #666;">Generado el {{ now()->format('d/m/Y') }}</p>
        <h2 style="margin: 15px 0; font-size: 24px; text-align: center;">CICLO {{ $ciclo->id }}</h2>
    </div>

    <!-- Información General -->
    <div class="section-title">Información General del Ciclo</div>
    <div class="info-general">
        <table>
            <tr>
                <th style="width: 30%;">Fecha de Inicio</th>
                <td>{{ $ciclo->fecha_inicio->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    <span style="
                        background-color: {{ $ciclo->status === 'entregado' ? '#27ae60' : '#f1c40f' }};
                        color: white;
                        padding: 3px 8px;
                        border-radius: 3px;
                        font-size: 11px;
                    ">
                        {{ ucfirst($ciclo->status) }}
                    </span>
                </td>
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

    <!-- Resumen Total por Especialidad -->
    <div class="section-title" style="margin-top: 10px;">Resumen Total por Especialidad</div>
    <table>
        <thead>
            <tr>
                <th>Especialidad</th>
                <th style="text-align: center;">Total Productos Entregados</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalesPorEspecialidad = collect($detallesPorRepresentante)
                    ->flatten(1)
                    ->groupBy(function($detalle) {
                        return $detalle->especialidad ? $detalle->especialidad->name : 'Especialidad eliminada';
                    })
                    ->map(function ($grupo) {
                        return $grupo->sum('cantidad_con_porcentaje');
                    });
            @endphp
            
            @foreach($totalesPorEspecialidad as $especialidad => $total)
            <tr>
                <td>{{ $especialidad }}</td>
                <td style="text-align: center;">{{ $total }} </td>
            </tr>
            @endforeach
            
            <tr class="total-row">
                <td>Total General</td>
                <td style="text-align: center;">{{ $totalesPorEspecialidad->sum() }} </td>
            </tr>
        </tbody>
    </table>

    <!-- Medicamentos Entregados -->
    <div class="section-title" style="margin-top: 20px;">Medicamentos Entregados</div>
    <table>
        <thead>
            <tr>
                <th>Medicamento</th>
                <th style="text-align: center;">Total Entregados</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalesPorMedicamento = collect($detallesPorRepresentante)
                    ->flatten(1)
                    ->groupBy(function($detalle) {
                        return $detalle->producto ? $detalle->producto->name : 'Producto eliminado';
                    })
                    ->map(function ($grupo) {
                        return $grupo->sum('cantidad_con_porcentaje');
                    });
            @endphp
            
            @foreach($totalesPorMedicamento as $medicamento => $total)
            <tr>
                <td>{{ $medicamento }}</td>
                <td style="text-align: center;">{{ $total }}  </td>
            </tr>
            @endforeach
            
            <tr class="total-row">
                <td>Total General</td>
                <td style="text-align: center;">{{ $totalesPorMedicamento->sum() }} </td>
            </tr>
        </tbody>
    </table>

    <!-- Detalles por Representante -->
    <div class="section-title" style="margin-top: 20px;">Detalles por Representante</div>
    @foreach($detallesPorRepresentante as $representanteId => $detalles)
        <div class="representante-section">
            <div class="info-box">
                <h4 style="margin: 0;">{{ $detalles->first()->representante->name }}</h4>
                <p style="margin: 5px 0 0 0;">Total de doctores asignados: {{ $detalles->first()->representante->doctors->sum('doctors_count') }}</p>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Especialidad</th>
                        <th>Producto</th>
                        <th style="text-align: center;">Doctores en Especialidad</th>
                        <th style="text-align: center;">Cantidad por Doctor</th>
                        <th style="text-align: center;">Total Entregados</th>
                        <th style="text-align: center;">Hospitalario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detalles as $detalle)
                    <tr>
                        <td>{{ $detalle->especialidad ? $detalle->especialidad->name : 'Especialidad eliminada' }}</td>
                        <td>{{ $detalle->producto ? $detalle->producto->name : 'Producto eliminado' }}</td>
                        <td style="text-align: center;">{{ $detalle->representante->doctors->where('medical_specialty_id', $detalle->especialidad_id)->sum('doctors_count') }}</td>
                        <td style="text-align: center;">{{ $detalle->cantidad_por_doctor }}</td>
                        <td style="text-align: center;">{{ $detalle->cantidad_total }} </td>
                        <td style="text-align: center;">{{ $detalle->cantidad_con_porcentaje }} </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="footer">
        {{ now()->year }} Sistema de Gestión de Muestras Médicas - Página 1
    </div>
</body>
</html>
