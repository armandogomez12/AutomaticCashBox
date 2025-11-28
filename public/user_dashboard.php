<?php
session_start();
// ... (Tu código de seguridad PHP no cambia)
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login_user.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Panel de Compras</title>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        :root { --primary-color: #007bff; --success-color: #28a745; --danger-color: #dc3545; --light-gray: #f8f9fa; --border-color: #dee2e6; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background-color: var(--light-gray); margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto 30px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        main { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; max-width: 1200px; margin: auto; }
        .container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .container-right { display: flex; flex-direction: column; gap: 30px; }
        h1, h2 { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid var(--border-color); }
        th { background-color: var(--light-gray); }
        form label { display: block; margin-bottom: 5px; font-weight: 500; }
        form input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid var(--border-color); border-radius: 5px; box-sizing: border-box; }
        form button { width: 100%; padding: 12px; background-color: var(--primary-color); color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        #resultado { margin-top: 20px; padding: 15px; border-radius: 5px; display: none; }
        .pass { background-color: #d4edda; color: #155724; }
        .fail { background-color: #f8d7da; color: #721c24; }
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100; display: none; justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 500px; text-align: center; }
        .modal-buttons { display: flex; gap: 15px; justify-content: center; margin-top: 25px; }
        .modal-buttons button { width: auto; padding: 10px 20px; border: none; color: white; border-radius: 5px; cursor: pointer; font-size: 16px; }
        #btn-pay-confirm { background-color: var(--success-color); }
        #btn-continue-shopping { background-color: var(--primary-color); }
        #btn-clear-purchases { background-color: var(--danger-color); }
        #payment-methods-msg { color: #6c757d; font-style: italic; margin-top: 15px; display: none; }
        #btn-show-pay-modal { background-color: var(--success-color); color: white; width: 100%; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 20px;}
    </style>
</head>
<body>
    <header class="header">
        <h1>Panel de Cliente</h1>
        <span>Hola, <strong><?php echo htmlspecialchars($_SESSION['user_username']); ?></strong></span>
        <a href="logout_user.php" style="text-decoration:none; background-color:#dc3545; color:white; padding:10px 15px; border-radius:5px;">Cerrar Sesión</a>
    </header>
    <main>
        <div class="container">
            <h2>Catálogo de Productos Disponibles</h2>
            <table id="products-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>ID (para Validación)</th>
                        <th>Precio</th>
                        <th>Peso (g)</th> </tr>
                </thead>
                <tbody id="products-tbody"></tbody>
            </table>
        </div>
        <div class="container-right">
            <div class="container">
                <h2>Validar Nueva Compra</h2>
                <form id="purchase-form">
                    <label for="scale_id">ID del Producto:</label>
                    <input type="text" id="scale_id" required>
                    <label for="price">Costo Pagado ($):</label>
                    <input type="number" id="price" step="0.01" required>
                    <button type="submit">Validar Compra</button>
                </form>
                <button id="scan-qr-btn" style="width:100%; padding:10px; margin-top:10px;">Escanear QR</button>
                <div id="qr-reader" style="display: none;"></div>
                <div id="resultado"></div>
            </div>
            <div class="container">
                <h2>Mis Compras Anteriores</h2>
                <table id="purchases-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Peso (g)</th> <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody id="purchases-tbody"></tbody>
                </table>
                <button id="btn-show-pay-modal">Pagar / Finalizar</button>
            </div>
        </div>
    </main>
    <div id="payment-modal" class="modal-overlay">
        <div class="modal-content">
            <h3>¿Desea concluir la compra?</h3>
            <p id="payment-methods-msg">Formas de pago (próximamente)...</p>
            <div class="modal-buttons">
                <button id="btn-pay-confirm">Pagar</button>
                <button id="btn-continue-shopping">Seguir Comprando</button>
                <button id="btn-clear-purchases">Limpiar Compras</button>
            </div>
        </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- (Tus referencias al DOM y la lógica del Modal no cambian) ---
    const productsTbody = document.getElementById('products-tbody');
    const purchasesTbody = document.getElementById('purchases-tbody');
    const purchaseForm = document.getElementById('purchase-form');
    const resultadoDiv = document.getElementById('resultado');
    const scaleIdInput = document.getElementById('scale_id');
    const paymentModal = document.getElementById('payment-modal');
    const btnShowPayModal = document.getElementById('btn-show-pay-modal');
    const btnContinueShopping = document.getElementById('btn-continue-shopping');
    const btnPayConfirm = document.getElementById('btn-pay-confirm');
    const btnClearPurchases = document.getElementById('btn-clear-purchases');
    const paymentMethodsMsg = document.getElementById('payment-methods-msg');

    async function fetchProducts() {
        try {
            const response = await fetch('../api/products.php?action=get_all');
            const data = await response.json();
            if (data.success) {
                productsTbody.innerHTML = '';
                data.products.forEach(product => {
                    // --- CAMBIO 3: Añadimos la celda del peso al catálogo ---
                    const row = `
                        <tr>
                            <td>${product.product_name}</td>
                            <td><strong>${product.scale_id}</strong></td>
                            <td>$${parseFloat(product.price).toFixed(2)}</td>
                            <td>${parseFloat(product.expected_weight).toFixed(2)} g</td>
                        </tr>`;
                    productsTbody.innerHTML += row;
                });
            } else { productsTbody.innerHTML = `<tr><td colspan="4">${data.error}</td></tr>`; } // Colspan ahora es 4
        } catch (error) { productsTbody.innerHTML = '<tr><td colspan="4">Error de conexión al cargar catálogo.</td></tr>'; } // Colspan ahora es 4
    }

    async function fetchUserPurchases() {
        try {
            const response = await fetch('../api/user_purchases.php');
            const data = await response.json();
            if (data.success) {
                purchasesTbody.innerHTML = '';
                if(data.purchases.length === 0){
                    purchasesTbody.innerHTML = '<tr><td colspan="4">Aún no tienes compras.</td></tr>'; // Colspan ahora es 4
                } else {
                    data.purchases.forEach(purchase => {
                        const date = new Date(purchase.timestamp).toLocaleString('es-ES');
                        // --- CAMBIO 4: Añadimos la celda del peso al historial ---
                        const row = `
                            <tr>
                                <td>${purchase.product_name}</td>
                                <td>$${parseFloat(purchase.price).toFixed(2)}</td>
                                <td>${parseFloat(purchase.expected_weight).toFixed(2)} g</td>
                                <td>${date}</td>
                            </tr>`;
                        purchasesTbody.innerHTML += row;
                    });
                }
            } else { purchasesTbody.innerHTML = `<tr><td colspan="4">${data.error}</td></tr>`; } // Colspan ahora es 4
        } catch (error) { purchasesTbody.innerHTML = '<tr><td colspan="4">Error de conexión al cargar historial.</td></tr>'; } // Colspan ahora es 4
    }

    // --- (El resto de tu JavaScript: purchaseForm, QR, Modal... no cambia) ---
    purchaseForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const formData = { scale_id: scaleIdInput.value, price: document.getElementById('price').value };
        try {
            const response = await fetch('validate_purchase.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(formData) });
            const result = await response.json();
            resultadoDiv.style.display = 'block';
            resultadoDiv.textContent = result.message || result.error;
            resultadoDiv.className = result.success ? 'pass' : 'fail';
            if (result.success) {
                fetchUserPurchases();
                purchaseForm.reset();
            }
        } catch (error) {
            resultadoDiv.style.display = 'block';
            resultadoDiv.textContent = 'Error de conexión al validar.';
            resultadoDiv.className = 'fail';
        }
    });

    const scanQrBtn = document.getElementById('scan-qr-btn');
    const qrReaderDiv = document.getElementById('qr-reader');
    const html5QrCode = new Html5Qrcode("qr-reader");
    const onScanSuccess = (decodedText) => {
        scaleIdInput.value = decodedText;
        html5QrCode.stop().catch(err => console.error("Fallo al detener el scanner.", err));
        qrReaderDiv.style.display = 'none';
    };
    scanQrBtn.addEventListener('click', () => {
        qrReaderDiv.style.display = 'block';
        html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: { width: 250, height: 250 } }, onScanSuccess, () => {});
    });

    btnShowPayModal.addEventListener('click', () => {
        paymentModal.style.display = 'flex';
        paymentMethodsMsg.style.display = 'none';
    });
    btnContinueShopping.addEventListener('click', () => {
        paymentModal.style.display = 'none';
    });
    btnPayConfirm.addEventListener('click', () => {
        paymentMethodsMsg.style.display = 'block';
    });
    btnClearPurchases.addEventListener('click', async () => {
        if (confirm('¿Estás seguro de que quieres borrar TODO tu historial de compras? Esta acción no se puede deshacer.')) {
            try {
                const response = await fetch('../api/clear_purchases.php', { method: 'POST' });
                const data = await response.json();
                if (data.success) {
                    alert('Tu historial ha sido limpiado.');
                    fetchUserPurchases();
                    paymentModal.style.display = 'none';
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Error de conexión al intentar limpiar el historial.');
            }
        }
    });

    fetchProducts();
    fetchUserPurchases();
});
</script>
</body>
</html>