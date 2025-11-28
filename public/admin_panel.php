<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login_admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador</title>
    <style>
        :root { --primary-color: #6f42c1; --light-gray: #f8f9fa; --border-color: #dee2e6; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: var(--light-gray); margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto 30px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .header h1 { margin: 0; font-size: 1.5em; }
        .header a { text-decoration: none; background-color: #dc3545; color: white; padding: 10px 15px; border-radius: 5px; font-weight: 500; }
        main { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; max-width: 1200px; margin: auto; }
        .container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        h2 { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid var(--border-color); }
        th { background-color: var(--light-gray); }
        form label { display: block; margin-bottom: 5px; font-weight: 500; }
        form input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid var(--border-color); border-radius: 5px; box-sizing: border-box; }
        form button { width: 100%; padding: 12px; background-color: var(--primary-color); color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        #notification { position: fixed; top: 20px; right: 20px; padding: 15px; border-radius: 5px; color: white; z-index: 1000; display: none; }
        #notification.success { background-color: #28a745; }
        #notification.error { background-color: #dc3545; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Panel de Control</h1>
        <span>Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong></span>
        <a href="../src/logout.php">Cerrar Sesión</a>
    </div>

    <main>
        <div class="container">
            <h2>Lista de Productos</h2>
            <table id="products-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>ID (scale_id)</th>
                        <th>Peso Esperado (g)</th>
                        <th>Tolerancia (g)</th>
                        <th>Precio ($)</th>
                    </tr>
                </thead>
                <tbody id="products-tbody">
                    </tbody>
            </table>
        </div>

        <div class="container">
            <h2>Añadir Producto</h2>
            <form id="add-product-form">
                <label for="product_name">Nombre del Producto:</label>
                <input type="text" id="product_name" required>

                <label for="scale_id">ID de Producto (único):</label>
                <input type="text" id="scale_id" required>

                <label for="expected_weight">Peso Esperado (g):</label>
                <input type="number" id="expected_weight" step="0.01" required>

                <label for="tolerance">Tolerancia (g):</label>
                <input type="number" id="tolerance" step="0.01" required>

                <label for="price">Precio ($):</label>
                <input type="number" id="price" step="0.01" required>

                <button type="submit">Añadir Producto</button>
            </form>
        </div>
    </main>

    <div id="notification"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productsTbody = document.getElementById('products-tbody');
            const addForm = document.getElementById('add-product-form');
            const notification = document.getElementById('notification');

            // --- FUNCIÓN PARA MOSTRAR NOTIFICACIONES ---
            function showNotification(message, type) {
                notification.textContent = message;
                notification.className = type;
                notification.style.display = 'block';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 3000);
            }

            // --- FUNCIÓN PARA CARGAR LOS PRODUCTOS ---
            async function fetchProducts() {
                try {
                    const response = await fetch('../api/products.php?action=get_all');
                    const data = await response.json();

                    if (data.success) {
                        productsTbody.innerHTML = ''; // Limpiar la tabla
                        data.products.forEach(product => {
                            const row = `
                                <tr>
                                    <td>${product.product_name}</td>
                                    <td>${product.scale_id}</td>
                                    <td>${product.expected_weight}</td>
                                    <td>${product.tolerance}</td>
                                    <td>${product.price}</td>
                                </tr>`;
                            productsTbody.innerHTML += row;
                        });
                    } else {
                        showNotification(data.error, 'error');
                    }
                } catch (error) {
                    showNotification('Error de conexión al cargar productos.', 'error');
                }
            }

            // --- FUNCIÓN PARA AÑADIR UN PRODUCTO ---
            addForm.addEventListener('submit', async function(event) {
                event.preventDefault();

                const newProduct = {
                    product_name: document.getElementById('product_name').value,
                    scale_id: document.getElementById('scale_id').value,
                    expected_weight: document.getElementById('expected_weight').value,
                    tolerance: document.getElementById('tolerance').value,
                    price: document.getElementById('price').value
                };

                try {
                    const response = await fetch('../api/products.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(newProduct)
                    });
                    const data = await response.json();

                    if (data.success) {
                        showNotification(data.message, 'success');
                        addForm.reset(); // Limpiar el formulario
                        fetchProducts(); // Recargar la lista de productos
                    } else {
                        showNotification(data.error, 'error');
                    }
                } catch (error) {
                    showNotification('Error de conexión al añadir producto.', 'error');
                }
            });

            // Cargar los productos al iniciar la página
            fetchProducts();
        });
    </script>

</body>
</html>