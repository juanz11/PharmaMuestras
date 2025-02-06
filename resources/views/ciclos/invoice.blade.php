<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura de Entrega - {{ $ciclo->fecha_inicio }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 20px;
        }
        .invoice-info {
            margin-bottom: 30px;
        }
        .invoice-info table {
            width: 100%;
        }
        .invoice-info td {
            padding: 5px;
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .products-table th, .products-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .products-table th {
            background-color: #f8f9fa;
        }
        .totals {
            margin-top: 30px;
            text-align: right;
        }
        .signature {
            margin-top: 50px;
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
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    @foreach($ciclo->getRepresentativesWithProducts() as $representative)
    <div class="invoice-page">
        <div class="header">
            <img src="{{ public_path('images/logo/logo.png') }}" alt="Logo" class="logo">
            <h2>Factura de Entrega de Muestras</h2>
        </div>

        <div class="representative-info">
            <h3>Información del Representante</h3>
            <table>
                <tr>
                    <td><strong>Nombre:</strong></td>
                    <td>{{ $representative->name }}</td>
                    <td><strong>Zona:</strong></td>
                    <td>{{ $representative->zone }}</td>
                </tr>
            </table>
        </div>

        <div class="invoice-info">
            <table>
                <tr>
                    <td><strong>Ciclo ID:</strong></td>
                    <td>#{{ $ciclo->id }}</td>
                    <td><strong>Fecha:</strong></td>
                    <td>{{ $ciclo->fecha_inicio }}</td>
                </tr>
                <tr>
                    <td><strong>Estado:</strong></td>
                    <td>{{ ucfirst($ciclo->status) }}</td>
                    <td><strong>% Hospitalario:</strong></td>
                    <td>{{ $ciclo->porcentaje_hospitalario }}%</td>
                </tr>
            </table>
        </div>

        <table class="products-table">
            <thead>
                <tr>
                    <th>Especialidad</th>
                    <th>Producto</th>
                    <th style="text-align: center;">Total Entregados</th>
                    <th style="text-align: center;">Con % Hospitalario</th>
                </tr>
            </thead>
            <tbody>
                @foreach($representative->getProductsForCycle($ciclo) as $item)
                    <tr>
                        <td>{{ $item->producto && $item->producto->medicalSpecialty ? $item->producto->medicalSpecialty->name : 'Especialidad eliminada' }}</td>
                        <td>{{ $item->producto ? $item->producto->name : 'Producto eliminado' }}</td>
                        <td style="text-align: center;">{{ $item->cantidad_total }} Unidades</td>
                        <td style="text-align: center;">{{ $item->cantidad_con_porcentaje }} Unidades</td>
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
</body>
</html>
