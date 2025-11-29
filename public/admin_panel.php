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
    <!-- Librería para el Escáner QR -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <style>
        :root { 
            --primary-color: #6f42c1; 
            --primary-hover: #5a32a3;
            --bg-color: #f4f6f9;
            --text-color: #333;
            --border-color: #dee2e6;
            --success-color: #28a745;
            --danger-color: #dc3545;
        }

        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; 
            background-color: var(--bg-color); 
            margin: 0; 
            padding: 20px; 
            color: var(--text-color);
        }

        /* --- Header --- */
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            max-width: 1200px; 
            margin: 0 auto 30px auto; 
            background: white; 
            padding: 15px 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
        }
        .header h1 { margin: 0; font-size: 1.5em; color: var(--primary-color); }
        .header .user-info { display: flex; align-items: center; gap: 15px; }
        .header a { 
            text-decoration: none; 
            background-color: var(--danger-color); 
            color: white; 
            padding: 8px 16px; 
            border-radius: 6px; 
            font-size: 0.9em; 
            font-weight: 500; 
            transition: opacity 0.2s;
        }
        .header a:hover { opacity: 0.9; }

        /* --- Layout Principal --- */
        main { 
            display: grid; 
            grid-template-columns: 2fr 1fr; /* 2 partes tabla, 1 parte formulario */
            gap: 25px; 
            max-width: 1200px; 
            margin: auto; 
        }
        
        .card { 
            background-color: white; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
            height: fit-content;
        }

        h2 { 
            border-bottom: 2px solid #f0f0f0; 
            padding-bottom: 15px; 
            margin-top: 0; 
            font-size: 1.25em; 
            color: #495057;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* --- Tabla --- */
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th, td { text-align: left; padding: 15px; border-bottom: 1px solid var(--border-color); }
        th { background-color: #f8f9fa; font-weight: 600; color: #495057; border-top: 1px solid var(--border-color); }
        th:first-child { border-top-left-radius: 8px; border-left: 1px solid var(--border-color); }
        th:last-child { border-top-right-radius: 8px; border-right: 1px solid var(--border-color); }
        td { border-left: 1px solid transparent; } /* Ajuste visual */
        
        /* --- Formulario --- */
        form label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; color: #555; }
        form input { 
            width: 100%; 
            padding: 12px; 
            margin-bottom: 15px; 
            border: 1px solid var(--border-color); 
            border-radius: 8px; 
            box-sizing: border-box; 
            transition: all 0.2s;
            font-size: 1rem;
        }
        form input:focus { 
            border-color: var(--primary-color); 
            outline: none; 
            box-shadow: 0 0 0 3px rgba(111, 66, 193, 0.1); 
        }
        
        /* --- Botones --- */
        .btn { 
            width: 100%; 
            padding: 12px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: 500; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            gap: 8px; 
            transition: transform 0.1s, background-color 0.2s;
        }
        .btn:active { transform: scale(0.98); }
        
        .btn-primary { background-color: var(--primary-color); color: white; margin-top: 10px; }
        .btn-primary:hover { background-color: var(--primary-hover); }

        /* --- Diseño del Escáner --- */
        .scanner-wrapper {
            background-color: #f8f9fa;
            border: 2px dashed #cbd5e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .scan-instruction { font-size: 0.85rem; color: #6c757d; display: block; margin-bottom: 15px; }

        #qr-reader {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 15px;
            display: none; /* Se oculta por defecto */
            border: none !important;
        }

        .btn-scan { background-color: #e2e6ea; color: #333; border: 1px solid #ced4da; }
        .btn-scan:hover { background-color: #dbe0e5; }
        
        .btn-stop { background-color: #fff5f5; color: var(--danger-color); border: 1px solid #feb2b2; display: none; }
        .btn-stop:hover { background-color: #fed7d7; }

        /* Notificaciones */
        #notification { 
            position: fixed; top: 20px; right: 20px; 
            padding: 15px 25px; border-radius: 8px; 
            color: white; z-index: 1000; display: none; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.2); 
            animation: slideIn 0.3s;
        }
        #notification.success { background-color: var(--success-color); }
        #notification.error { background-color: var(--danger-color); }
        
        @keyframes slideIn { from { transform: translateX(100%); } to { transform: translateX(0); } }

        /* Responsive */
        @media (max-width: 900px) { main { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <!-- Encabezado -->
    <div class="header">
        <h1>Panel de Control</h1>
        <div class="user-info">
            <span>Hola, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong></span>
            <a href="../src/logout.php">Salir</a>
        </div>
    </div>

    <main>
        <!-- Columna Izquierda: Tabla de Productos -->
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2>📦 Inventario</h2>
                <button onclick="fetchProducts()" style="background:none; border:none; cursor:pointer; color:var(--primary-color); font-weight:bold;">↻ Refrescar</button>
            </div>
            
            <div style="overflow-x:auto;">
                <table id="products-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>ID (QR)</th>
                            <th>Peso</th>
                            <th>+/-</th>
                            <th>Precio</th>
                        </tr>
                    </thead>
                    <tbody id="products-tbody">
                        <!-- Se llena dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Columna Derecha: Formulario + Escáner -->
        <div class="card">
            <h2>✨ Nuevo Producto</h2>

            <!-- SECCIÓN DEL ESCÁNER -->
            <div class="scanner-wrapper">
                <span class="scan-instruction">Usa la cámara para llenar los datos automáticamente</span>
                
                <!-- Botón Activar Cámara -->
                <button type="button" id="btn-scan" class="btn btn-scan">
                    <!-- Icono Cámara SVG -->
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Escanear Código QR
                </button>
                
                <!-- Botón Detener Cámara -->
                <button type="button" id="btn-stop-scan" class="btn btn-stop">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Detener Cámara
                </button>
                
                <!-- Área de Video -->
                <div id="qr-reader"></div>
            </div>

            <!-- FORMULARIO -->
            <form id="add-product-form">
                <label for="product_name">Nombre del Producto:</label>
                <input type="text" id="product_name" required placeholder="Ej: Manzana Roja">

                <label for="scale_id">ID Único (QR):</label>
                <input type="text" id="scale_id" required placeholder="Ej: MANZANA_ROJA">

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label for="expected_weight">Peso (g):</label>
                        <input type="number" id="expected_weight" step="0.01" required placeholder="1000">
                    </div>
                    <div>
                        <label for="tolerance">Tol. (g):</label>
                        <input type="number" id="tolerance" step="0.01" required placeholder="10">
                    </div>
                </div>

                <label for="price">Precio ($):</label>
                <input type="number" id="price" step="0.01" required placeholder="0.00">

                <button type="submit" class="btn btn-primary">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Guardar Producto
                </button>
            </form>
        </div>
    </main>

    <div id="notification"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productsTbody = document.getElementById('products-tbody');
            const addForm = document.getElementById('add-product-form');
            const notification = document.getElementById('notification');
            
            // Elementos del Escáner
            const btnScan = document.getElementById('btn-scan');
            const btnStopScan = document.getElementById('btn-stop-scan');
            const qrReaderDiv = document.getElementById('qr-reader');
            let html5QrCode;

            // --- Lógica del Escáner ---
            btnScan.addEventListener('click', () => {
                // UI: Mostrar zona de video, ocultar botón iniciar, mostrar botón detener
                qrReaderDiv.style.display = 'block';
                btnScan.style.display = 'none';
                btnStopScan.style.display = 'flex'; 

                html5QrCode = new Html5Qrcode("qr-reader");
                const config = { fps: 10, qrbox: { width: 250, height: 250 } };
                
                // Iniciar cámara trasera
                html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess)
                .catch(err => {
                    showNotification("No se pudo acceder a la cámara: " + err, 'error');
                    stopScanning();
                });
            });

            btnStopScan.addEventListener('click', stopScanning);

            function stopScanning() {
                if (html5QrCode) {
                    html5QrCode.stop().then(() => {
                        qrReaderDiv.style.display = 'none';
                        btnScan.style.display = 'flex';
                        btnStopScan.style.display = 'none';
                        html5QrCode.clear();
                    }).catch(console.error);
                }
            }

            function onScanSuccess(decodedText) {
                stopScanning(); // Detener cámara al leer
                showNotification("¡Código QR leído!", 'success');
                
                // Formato esperado: Nombre|ID|Peso|Tolerancia|Precio
                let parts = decodedText.split('|');
                
                // Fallback por si usas comas
                if (parts.length < 5) parts = decodedText.split(',');

                if (parts.length >= 5) {
                    // Llenar inputs
                    document.getElementById('product_name').value = parts[0].trim();
                    document.getElementById('scale_id').value = parts[1].trim();
                    document.getElementById('expected_weight').value = parts[2].trim();
                    document.getElementById('tolerance').value = parts[3].trim();
                    document.getElementById('price').value = parts[4].trim();
                } else {
                    showNotification("Formato QR inválido. Faltan datos.", 'error');
                }
            }

            // --- Notificaciones ---
            function showNotification(message, type) {
                notification.textContent = message;
                notification.className = type;
                notification.style.display = 'block';
                setTimeout(() => notification.style.display = 'none', 4000);
            }

            // --- Cargar Productos (GET) ---
            window.fetchProducts = async function() {
                try {
                    // Si ya tienes tu API funcionando, esto cargará los datos
                    const response = await fetch('../api/products.php?action=get_all');
                    const data = await response.json();

                    if (data.success) {
                        productsTbody.innerHTML = ''; 
                        data.products.forEach(p => {
                            productsTbody.innerHTML += `
                                <tr>
                                    <td>${p.product_name}</td>
                                    <td><code style="background:#eee; padding:2px 4px; border-radius:4px;">${p.scale_id}</code></td>
                                    <td>${p.expected_weight}g</td>
                                    <td>±${p.tolerance}g</td>
                                    <td>$${p.price}</td>
                                </tr>`;
                        });
                    }
                } catch (e) { 
                    console.log("Error cargando productos (posiblemente la API no esté lista o vacía).");
                }
            }

            // --- Guardar Producto (POST) ---
            addForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = {
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
                        body: JSON.stringify(formData)
                    });
                    const data = await response.json();

                    if (data.success) {
                        showNotification("Producto guardado correctamente", 'success');
                        addForm.reset();
                        fetchProducts();
                    } else {
                        showNotification(data.error || "Error al guardar", 'error');
                    }
                } catch (e) {
                    showNotification('Error de conexión con el servidor', 'error');
                }
            });

            // Cargar inicial
            fetchProducts();
        });
    </script>

</body>
</html>
