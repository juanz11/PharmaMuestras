<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Ciclos</title>
    <style>
        @page {
            margin: 0.5cm;
            size: landscape;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 8px;
            padding: 5px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .header h2 {
            margin: 0 0 3px 0;
            font-size: 12px;
            color: #333;
        }
        .dates {
            font-size: 9px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 7px;
        }
        th, td {
            border: 0.5px solid #dee2e6;
            padding: 3px 2px;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
            white-space: normal;
            height: 24px;
            vertical-align: top;
        }
        .producto-header {
            max-width: 45px;
            min-width: 35px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
            word-break: break-word;
            font-size: 7px;
        }
        .precio {
            font-size: 6px;
            color: #666;
            display: block;
            white-space: nowrap;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .ciclo-nombre {
            font-weight: bold;
            color: #333;
            font-size: 7px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100px;
        }
        .ciclo-fechas {
            font-size: 6px;
            color: #666;
            white-space: nowrap;
        }
        .cantidad {
            text-align: center;
            font-family: 'Courier New', monospace;
            white-space: nowrap;
        }
        .valor-total {
            font-weight: bold;
            color: #333;
            white-space: nowrap;
        }
        .page-number {
            text-align: right;
            font-size: 6px;
            color: #999;
            position: fixed;
            bottom: 5px;
            right: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Reporte de Ciclos</h2>
        <div class="dates">
            Período: {{ $start_date }} - {{ $end_date }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 90px">Ciclo</th>
                @foreach($productos as $producto)
                    <th class="text-center producto-header">
                        {{ $producto->name }}
                        <span class="precio">{{ number_format($producto->valor, 2) }} DOP</span>
                    </th>
                @endforeach
                <th class="text-right" style="width: 60px">Valor Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ciclos as $ciclo)
                <tr>
                    <td>
                        <div class="ciclo-nombre" title="{{ $ciclo->nombre }}">{{ $ciclo->nombre }}</div>
                        <div class="ciclo-fechas">{{ $ciclo->fecha_inicio }} - {{ $ciclo->fecha_fin }}</div>
                    </td>
                    @foreach($productos as $producto)
                        <td class="cantidad">
                            {{ number_format($ciclo->cantidades[$producto->id] ?? 0, 0) }}
                        </td>
                    @endforeach
                    <td class="text-right valor-total">
                        {{ number_format($ciclo->costo_total, 2) }}
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td>Total General</td>
                @foreach($productos as $producto)
                    <td class="cantidad">
                        {{ number_format($totales['cantidades'][$producto->id] ?? 0, 0) }}
                    </td>
                @endforeach
                <td class="text-right valor-total">
                    {{ number_format($totales['costo_total'], 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="page-number">
        Página 1
    </div>
</body>
</html>
