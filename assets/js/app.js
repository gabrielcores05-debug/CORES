// assets/js/app.js - Lógica Interactiva, Gráficas y Mapa para CORES COMUNICACIONES S.A.S.

document.addEventListener('DOMContentLoaded', () => {
    initLeafletMap();
    initDashboardCharts();
});

// 1. Inicialización del Mapa Interactivo (Condoto, Chocó)
function initLeafletMap() {
    const mapElement = document.getElementById('map');
    if (!mapElement) return;

    const lat = 5.0939;
    const lng = -76.6508;

    // Inicializar mapa Leaflet
    const map = L.map('map', {
        scrollWheelZoom: false
    }).setView([lat, lng], 15);

    // Capa de OpenStreetMap con estilo limpio
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | CORES COMUNICACIONES'
    }).addTo(map);

    // Icono personalizado con colores de CORES
    const coresIcon = L.divIcon({
        className: 'custom-div-icon',
        html: `
            <div style="background: linear-gradient(135deg, #0284C7, #F97316); width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; box-shadow: 0 4px 15px rgba(249,115,22,0.6); border: 3px solid white;">
                <i class="fa-solid fa-wifi"></i>
            </div>
        `,
        iconSize: [44, 44],
        iconAnchor: [22, 44],
        popupAnchor: [0, -40]
    });

    // Marcador oficial
    const marker = L.marker([lat, lng], { icon: coresIcon }).addTo(map);
    marker.bindPopup(`
        <div style="text-align: center; padding: 6px; font-family: sans-serif;">
            <strong style="color: #0F172A; font-size: 14px;">CORES COMUNICACIONES S.A.S.</strong><br>
            <span style="color: #F97316; font-weight: bold; font-size: 11px;">"CONECTAMOS TU MUNDO, IMPULSAMOS TU VIDA"</span><br>
            <p style="margin: 6px 0; font-size: 12px; color: #475569;">Condoto, Chocó, Colombia<br>Tel: <strong>3126030464</strong></p>
            <a href="https://wa.me/573126030464" target="_blank" style="display: inline-block; background: #25D366; color: white; padding: 4px 10px; border-radius: 6px; font-size: 11px; text-decoration: none; font-weight: bold;">
                <i class="fa-brands fa-whatsapp"></i> Contactar Sede
            </a>
        </div>
    `).openPopup();
}

// 2. Gráficas del Dashboard Administrativo
function initDashboardCharts() {
    const revCanvas = document.getElementById('chartRevenue');
    const statusCanvas = document.getElementById('chartUserStatus');
    const invCanvas = document.getElementById('chartInvoices');
    const growthCanvas = document.getElementById('chartGrowth');

    if (!revCanvas && !statusCanvas && !invCanvas && !growthCanvas) return;

    // Obtener datos desde el endpoint JSON
    fetch('app/api.php?action=chart_data')
        .then(response => response.json())
        .then(data => {
            // Gráfica 1: Recaudo por Mes (Azul y Naranja)
            if (revCanvas) {
                new Chart(revCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: data.revenue_by_month.labels,
                        datasets: [{
                            label: 'Recaudo Mensual (COP)',
                            data: data.revenue_by_month.data,
                            backgroundColor: '#0284C7',
                            hoverBackgroundColor: '#F97316',
                            borderRadius: 8,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                ticks: {
                                    callback: val => '$ ' + (val / 1000000).toFixed(1) + 'M'
                                }
                            }
                        }
                    }
                });
            }

            // Gráfica 2: Usuarios por Estado (Doughnut en Azul, Naranja y Rojo)
            if (statusCanvas) {
                new Chart(statusCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: data.users_by_status.labels,
                        datasets: [{
                            data: data.users_by_status.data,
                            backgroundColor: ['#059669', '#F59E0B', '#DC2626'],
                            borderWidth: 2,
                            borderColor: '#FFFFFF'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }

            // Gráfica 3: Facturas Pagadas vs Pendientes
            if (invCanvas) {
                new Chart(invCanvas.getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: data.invoices_comparison.labels,
                        datasets: [{
                            data: data.invoices_comparison.data,
                            backgroundColor: ['#0284C7', '#F97316'],
                            borderWidth: 2,
                            borderColor: '#FFFFFF'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }

            // Gráfica 4: Crecimiento de Usuarios
            if (growthCanvas) {
                new Chart(growthCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: data.user_growth.labels,
                        datasets: [{
                            label: 'Clientes Conectados',
                            data: data.user_growth.data,
                            borderColor: '#06B6D4',
                            backgroundColor: 'rgba(6, 182, 212, 0.15)',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#F97316',
                            pointBorderColor: '#FFFFFF',
                            pointRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
        })
        .catch(err => console.error('Error cargando gráficas:', err));
}

// 3. Manejo de Modales
function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('hidden');
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('hidden');
}

// 4. Confirmación de Suspensión y Eliminación
function confirmSuspension(userName, formElement) {
    if (confirm(`¿Estás seguro de que deseas SUSPENDER el servicio a ${userName}?`)) {
        formElement.submit();
    }
}

function confirmDeletion(userName, formElement) {
    if (confirm(`⚠️ ¿ESTÁS SEGURO DE QUE DESEAS ELIMINAR ESTE USUARIO?\n\nCliente: ${userName}\nEsta acción borrará sus registros de forma definitiva.`)) {
        formElement.submit();
    }
}
