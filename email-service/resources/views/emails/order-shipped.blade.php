<!DOCTYPE html>
<html>
<head>
    <title>Confirmación de Orden</title>
</head>
<body>
    <h1>Confirmación de Orden</h1>
    <p>Gracias por su pedido, {{ $order['customer_name'] }}!</p>

    <h2>Detalle de la órden:</h2>
    <ul>
        @foreach ($order['items'] as $item)
            <li>
                <strong>Producto:</strong> {{ $item['name'] }}<br>
                <strong>Descripción:</strong> {{ $item['description'] }}<br>
                <strong>Categoría:</strong> {{ $item['category'] }}<br>
                <strong>Precio:</strong> ${{ $item['price'] }}<br>
                <strong>Cantidad:</strong> {{ $item['quantity'] }}<br>
                <strong>Ingredientes:</strong> {{ implode(', ', $item['ingredients']) }}<br><br>
            </li>
        @endforeach
    </ul>

    <p><strong>{{ $contentBody }}</p>
    <p><strong>Total a pagar:</strong> ${{ $order['total_price'] }}</p>
    <p><strong>Estado de la órden:</strong> {{ ucfirst($order['status']) }}</p>

    <p>Gracias por comprar nuestros productos!</p>
</body>
</html>