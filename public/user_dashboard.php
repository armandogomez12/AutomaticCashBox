<?php
session_start();

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
        form input, form select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid var(--border-color); border-radius: 5px; box-sizing: border-box; }
        form button { width: 100%; padding: 12px; background-color: var(--primary-color); color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        #resultado { margin-top: 20px; padding: 15px; border-radius: 5px; display: none; }
        .pass { background-color: #d4edda; color: #155724; }
        .fail { background-color: #f8d7da; color: #721c24; }
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100; display: none; justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 500px; text-align: center; }
        .modal-buttons { display: flex; gap: 15px; justify-content: center; margin-top: 25px; }
        .modal-buttons button { width: auto; padding: 10px 20px; border: none; color: white; border-radius: 5px; cursor: pointer; font-size: 16px; }
        
        #btn-continue-shopping { background-color: var(--primary-color); }
        #btn-clear-purchases { background-color: var(--danger-color); }
        #btn-show-pay-modal { background-color: var(--success-color); color: white; width: 100%; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 20px;}
        
        /* Estilos para los botones de pago */
        .payment-options { display: flex; flex-direction: column; gap: 10px; margin-top: 20px; }
        .btn-payment { 
            display: flex; align-items: center; justify-content: center; gap: 10px;
            padding: 12px; border-radius: 5px; text-decoration: none; color: white; font-weight: bold; transition: opacity 0.3s;
        }
        .btn-payment:hover { opacity: 0.9; }
        .btn-paypal { background-color: #003087; } /* Azul PayPal */
        .btn-visa { background-color: #1a1f71; }   /* Azul Visa */
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
                    <label for="purchase_scale_id">ID del Producto:</label>
                    <input type="text" id="purchase_scale_id" required>
                    
                    <label for="purchase_price">Costo Pagado ($):</label>
                    <input type="number" id="purchase_price" step="0.01" required>
                    
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

            <div class="validation-container" style="margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h3>Validar Compra por Peso</h3>
                <form id="validation-form">
                    <label>Selecciona un producto:</label>
                    <select id="validation_scale_id" name="scale_id" required style="padding: 8px; margin: 10px 0;">
                        <option value="">-- Selecciona un producto --</option>
                        <option value="MANZANA_ROJA">Manzana Roja Grande (1000g)</option>
                        <option value="PLATANO_CHIAPAS">Plátano de Chiapas (1000g)</option>
                        <option value="JITOMATE_BOLA">Jitomate Bola (1000g)</option>
                        <option value="AGUACATE_HASS">Aguacate Hass (1000g)</option>
                        <option value="XBOX_SERIES">Xbox Series X (4500g)</option>
                    </select>
                    <br>
                    <button type="submit" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        Solicitar Validación
                    </button>
                </form>

                <div id="weight-info" style="display: none; margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
                    <p><strong>Estado:</strong> <span id="status" style="font-weight: bold; color: orange;">Esperando...</span></p>
                    <p><strong>Peso esperado:</strong> <span id="expected-weight">--</span> g</p>
                    <p><strong>Peso medido:</strong> <span id="measured-weight">--</span> g</p>
                    <p><strong>Tolerancia:</strong> <span id="tolerance">--</span> g</p>
                    <p><strong>Precio:</strong> $<span id="display-price">--</span></p>
                    <p id="message" style="font-size: 16px; font-weight: bold;"></p>
                    <button type="button" id="cancel-btn" style="padding: 10px 20px; background-color: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- MODAL DE PAGO ACTUALIZADO -->
    <div id="payment-modal" class="modal-overlay">
        <div class="modal-content">
            <h3>Finalizar Compra</h3>
            <p>Selecciona tu método de pago preferido:</p>
            
            <div class="payment-options">
                <!-- Enlace a PayPal -->
                <a href="https://www.paypal.com/signin" target="_blank" class="btn-payment btn-paypal">
                    Pagar con PayPal
                </a>
                
                <!-- Enlace simulado a Tarjeta  -->
                <a href="https://www.visa.com.mx/pagar-con-visa/portal-de-tarjetahabientes.html" target="_blank" class="btn-payment btn-visa">
                    Pagar con Tarjeta
                </a>
            </div>

            <div class="modal-buttons">
                <button id="btn-continue-shopping">Seguir Comprando</button>
                <button id="btn-clear-purchases">Limpiar Carrito</button>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Referencias DOM
    const productsTbody = document.getElementById('products-tbody');
    const purchasesTbody = document.getElementById('purchases-tbody');
    const purchaseForm = document.getElementById('purchase-form');
    const resultadoDiv = document.getElementById('resultado');
    
    const scaleIdInput = document.getElementById('purchase_scale_id'); 
    const priceInput = document.getElementById('purchase_price'); 
    
    const paymentModal = document.getElementById('payment-modal');
    const btnShowPayModal = document.getElementById('btn-show-pay-modal');
    const btnContinueShopping = document.getElementById('btn-continue-shopping');
    const btnClearPurchases = document.getElementById('btn-clear-purchases');

    async function fetchProducts() {
        try {
            const response = await fetch('../api/products.php?action=get_all');
            const data = await response.json();
            if (data.success) {
                productsTbody.innerHTML = '';
                data.products.forEach(product => {
                    const row = `
                        <tr>
                            <td>${product.product_name}</td>
                            <td><strong>${product.scale_id}</strong></td>
                            <td>$${parseFloat(product.price).toFixed(2)}</td>
                            <td>${parseFloat(product.expected_weight).toFixed(2)} g</td>
                        </tr>`;
                    productsTbody.innerHTML += row;
                });
            } else { productsTbody.innerHTML = `<tr><td colspan="4">${data.error}</td></tr>`; } 
        } catch (error) { productsTbody.innerHTML = '<tr><td colspan="4">Error de conexión al cargar catálogo.</td></tr>'; } 
    }

    async function fetchUserPurchases() {
        console.log('🔄 Llamando fetchUserPurchases...');
        try {
            const response = await fetch('../api/user_purchases.php');
            console.log('📡 Respuesta recibida:', response.status);
            
            const data = await response.json();
            console.log('✓ JSON parseado:', data);
            
            const tbody = document.querySelector('#purchases-table tbody');
            console.log('📋 Tbody encontrado:', tbody ? 'SÍ' : 'NO');
            
            if (!tbody) {
                console.error('❌ No encontró #purchases-table tbody');
                return;
            }
            
            if (data.success && data.purchases) {
                console.log('✓ Éxito. Registros:', data.purchases.length);
                
                if (data.purchases.length > 0) {
                    tbody.innerHTML = ''; // Limpiar
                    
                    data.purchases.forEach((purchase, index) => {
                        console.log(`Insertando compra ${index + 1}:`, purchase);
                        
                        const row = `
                            <tr>
                                <td>${purchase.product_name || purchase.scale_id}</td>
                                <td>$${parseFloat(purchase.price).toFixed(2)}</td>
                                <td>${purchase.expected_weight} g</td>
                                <td>${new Date(purchase.timestamp).toLocaleString('es-MX')}</td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                    
                    console.log('✓ Tabla actualizada con', data.purchases.length, 'compras');
                } else {
                    tbody.innerHTML = '<tr><td colspan="4">No hay compras registradas</td></tr>';
                }
            } else {
                console.error('❌ Error en respuesta:', data.error);
                tbody.innerHTML = `<tr><td colspan="4">Error: ${data.error}</td></tr>`;
            }
        } catch (error) {
            console.error('❌ Error general:', error);
            const tbody = document.querySelector('#purchases-table tbody');
            if (tbody) tbody.innerHTML = '<tr><td colspan="4">Error de conexión</td></tr>';
        }
    }
    

    // LÓGICA COMPRA 
    purchaseForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const formData = { scale_id: scaleIdInput.value, price: priceInput.value };
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


    // QR 
    const scanQrBtn = document.getElementById('scan-qr-btn');
    const qrReaderDiv = document.getElementById('qr-reader');
    const html5QrCode = new Html5Qrcode("qr-reader");

    const onScanSuccess = (decodedText) => {
        let parts = decodedText.split('|');
        if (parts.length < 2) parts = decodedText.split(',');

        if (parts.length >= 5) {
            scaleIdInput.value = parts[1].trim(); 
            priceInput.value = parts[4].trim();
        } else if (parts.length >= 2) {
            scaleIdInput.value = parts[0].trim();
            priceInput.value = parts[1].trim();
        } else {
            scaleIdInput.value = decodedText;
            alert("Formato QR simple detectado. Verifica el precio manualmente.");
        }
        html5QrCode.stop().catch(console.error);
        qrReaderDiv.style.display = 'none';
    };

    scanQrBtn.addEventListener('click', () => {
        qrReaderDiv.style.display = 'block';
        html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: { width: 250, height: 250 } }, onScanSuccess, () => {});
    });

    // --- Modal ---
    btnShowPayModal.addEventListener('click', () => {
        paymentModal.style.display = 'flex';
    });
    btnContinueShopping.addEventListener('click', () => {
        paymentModal.style.display = 'none';
    });
    
    btnClearPurchases.addEventListener('click', async () => {
        if (confirm('¿Estás seguro de que quieres borrar TODO tu historial de compras?')) {
            try {
                const response = await fetch('../api/clear_purchases.php', { method: 'POST' });
                const data = await response.json();
                if (data.success) {
                    alert('Historial limpiado.');
                    fetchUserPurchases();
                    paymentModal.style.display = 'none';
                } else { alert('Error: ' + data.error); }
            } catch (error) { alert('Error de conexión.'); }
        }
    });

    

    fetchProducts();
    fetchUserPurchases();
});
</script>

<script>
    let validationId = null;
    let checkInterval = null;

    document.getElementById('validation-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const scaleId = document.getElementById('validation_scale_id').value;
        if(!scaleId) { alert("Por favor selecciona un producto"); return; }

        const formData = new FormData();
        formData.append('scale_id', scaleId);

        try {
            const response = await fetch('validate_purchase.php', { method: 'POST', body: formData });
            const data = await response.json();

            if (data.success) {
                validationId = data.validation_id;
                document.getElementById('weight-info').style.display = 'block';
                document.getElementById('expected-weight').textContent = data.expected_weight;
                document.getElementById('tolerance').textContent = data.tolerance;
                document.getElementById('display-price').textContent = data.price;
                document.getElementById('message').textContent = data.message;
                checkValidationStatus();
                checkInterval = setInterval(checkValidationStatus, 2000);
            } else { alert('Error: ' + data.error); }
        } catch (error) { alert('Error al conectar: ' + error.message); }
    });

    async function fetchUserPurchases() {
        console.log('🔄 Llamando fetchUserPurchases...');
        try {
            const response = await fetch('../api/user_purchases.php');
            console.log('📡 Respuesta recibida:', response.status);
            
            const data = await response.json();
            console.log('✓ JSON parseado:', data);
            
            const tbody = document.querySelector('#purchases-table tbody');
            console.log('📋 Tbody encontrado:', tbody ? 'SÍ' : 'NO');
            
            if (!tbody) {
                console.error('❌ No encontró #purchases-table tbody');
                return;
            }
            
            if (data.success && data.purchases) {
                console.log('✓ Éxito. Registros:', data.purchases.length);
                
                if (data.purchases.length > 0) {
                    tbody.innerHTML = '';
                    
                    data.purchases.forEach((purchase, index) => {
                        console.log(`Insertando compra ${index + 1}:`, purchase);
                        
                        const row = `
                            <tr>
                                <td>${purchase.product_name || purchase.scale_id}</td>
                                <td>$${parseFloat(purchase.price).toFixed(2)}</td>
                                <td>${purchase.expected_weight} g</td>
                                <td>${new Date(purchase.timestamp).toLocaleString('es-MX')}</td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                    
                    console.log('✓ Tabla actualizada con', data.purchases.length, 'compras');
                } else {
                    tbody.innerHTML = '<tr><td colspan="4">No hay compras registradas</td></tr>';
                }
            } else {
                console.error('❌ Error en respuesta:', data.error);
                tbody.innerHTML = `<tr><td colspan="4">Error: ${data.error}</td></tr>`;
            }
        } catch (error) {
            console.error('❌ Error general:', error);
            const tbody = document.querySelector('#purchases-table tbody');
            if (tbody) tbody.innerHTML = '<tr><td colspan="4">Error de conexión</td></tr>';
        }
    }

    // === LLAMAR PRIMERO AL CARGAR ===
    console.log('📍 Página cargada, cargando compras iniciales...');
    fetchUserPurchases();
    async function checkValidationStatus() {
        try {
            const response = await fetch(`../api/check_validation_status.php?validation_id=${validationId}`);
            const data = await response.json();

            if (data.success) {
                const statusEl = document.getElementById('status');
                
                // Cambiar color según estado
                if (data.status === 'PENDING') statusEl.style.color = 'orange';
                else if (data.status === 'VALIDATED') statusEl.style.color = 'green';
                else if (data.status === 'FAILED') statusEl.style.color = 'red';
                
                statusEl.textContent = data.status;
                
                if (data.measured_weight) {
                    document.getElementById('measured-weight').textContent = data.measured_weight;
                }
                
                document.getElementById('message').textContent = data.message;

                // Si terminó la validación, detener el polling
                if (data.status !== 'PENDING') {
                    clearInterval(checkInterval);
                    
                    if (data.is_valid) {
                        // CAMBIO: Actualizar tabla de compras inmediatamente
                        fetchUserPurchases();
                        
                        // Después de 3 segundos, ocultar caja y limpiar
                        setTimeout(() => {
                            document.getElementById('weight-info').style.display = 'none';
                            document.getElementById('validation-form').reset();
                            validationId = null;
                        }, 3000);
                    }
                }
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    document.getElementById('cancel-btn').addEventListener('click', () => {
        clearInterval(checkInterval);
        document.getElementById('weight-info').style.display = 'none';
        document.getElementById('validation-form').reset();
        validationId = null;
    });
</script>
</body>
</html>
