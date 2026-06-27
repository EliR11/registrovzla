// API URL - Ruta correcta para tu proyecto  http://localhost/registro_personas/api/
const API_URL = 'https://rescatevzla2.onrender.com';

let persons = [];

// Detectar si es móvil
function isMobile() {
    return window.innerWidth <= 768;
}

// Cargar personas desde la base de datos
async function loadPersons(searchTerm = '') {
    try {
        let url = `${API_URL}get_persons.php`;
        if (searchTerm) {
            url += `?search=${encodeURIComponent(searchTerm)}`;
        }
        
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error('Error al cargar los datos');
        }
        
        persons = await response.json();
        renderTable();
        updateStats();
    } catch (error) {
        showNotification('Error al cargar los datos: ' + error.message, 'error');
        console.error('Error:', error);
    }
}

// Renderizar tabla (Desktop o Móvil)
function renderTable() {
    if (isMobile()) {
        renderMobileCards();
    } else {
        renderDesktopTable();
    }
}

// Renderizar vista Desktop (Tabla)
function renderDesktopTable() {
    const tbody = document.getElementById('personTableBody');
    
    if (persons.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="11" style="text-align: center; padding: 40px; color: #999;">
                    No hay personas registradas
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = persons.map(person => `
        <tr style="${person.estado === 'encontrado' ? 'background: #e8f5e9;' : ''}">
            <td>
                <span class="status-badge ${person.estado === 'encontrado' ? 'status-encontrado' : 'status-desaparecido'}">
                    ${person.estado === 'encontrado' ? '✅ Encontrado' : '❌ Desaparecido'}
                </span>
            </td>
            <td>${person.localidad}</td>
            <td>
                <strong>${person.primerNombre} ${person.segundoNombre || ''} ${person.primerApellido} ${person.segundoApellido || ''}</strong>
            </td>
            <td>${person.cedula}</td>
            <td>${person.edad} años<br>${getEdadBadge(person.edad)}</td>
            <td>${person.hospital || 'N/A'}</td>
            <td>
                ${person.zonaHospital ? `<span class="zona-hospital">📍 ${person.zonaHospital}</span>` : 'N/A'}
            </td>
            <td>${person.telefono || 'N/A'}</td>
            <td>${person.correo || 'N/A'}</td>
            <td>
                ${person.casaDestruida === 'si' ? '<span class="status-destruida">🏚️ Destruida</span>' : 
                  person.casaDestruida === 'averiada' ? '<span class="status-averiada">⚠️ Averiada</span>' : 
                  '<span class="status-no-averiada">✅ No</span>'}
            </td>
            <td>
                <div class="actions">
                    <button class="btn-small btn-edit" onclick="openEditModal(${person.id})">
                        ✏️ Editar
                    </button>
                    ${person.estado === 'desaparecido' ? `
                        <button class="btn-small btn-found" onclick="markAsFound(${person.id})">
                            ✅ Encontrar
                        </button>
                    ` : `
                        <button class="btn-small btn-edit" onclick="markAsMissing(${person.id})">
                            ↩️ Desaparecido
                        </button>
                    `}
                    <button class="btn-small btn-delete" onclick="deletePerson(${person.id})">
                        🗑️
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Renderizar vista Móvil (Tarjetas)
function renderMobileCards() {
    const container = document.getElementById('mobileCardsContainer');
    
    if (persons.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 40px; color: #999;">
                No hay personas registradas
            </div>
        `;
        return;
    }

    container.innerHTML = persons.map(person => `
        <div class="card-person" style="${person.estado === 'encontrado' ? 'border-left: 4px solid #2e7d32;' : 'border-left: 4px solid #c62828;'}">
            <div class="card-header">
                <div class="card-name">
                    ${person.primerNombre} ${person.segundoNombre || ''} ${person.primerApellido} ${person.segundoApellido || ''}
                </div>
                <div class="card-status">
                    <span class="badge ${person.estado === 'encontrado' ? 'badge-encontrado' : 'badge-desaparecido'}">
                        ${person.estado === 'encontrado' ? '✅ Encontrado' : '❌ Desaparecido'}
                    </span>
                </div>
            </div>
            
            <div class="card-body">
                <div class="card-item">
                    <span class="label">📌 Localidad</span>
                    <span class="value">${person.localidad}</span>
                </div>
                <div class="card-item">
                    <span class="label">🆔 Cédula</span>
                    <span class="value">${person.cedula}</span>
                </div>
                <div class="card-item">
                    <span class="label">🎂 Edad</span>
                    <span class="value">${person.edad} años ${getEdadBadge(person.edad)}</span>
                </div>
                <div class="card-item">
                    <span class="label">🏥 Hospital</span>
                    <span class="value">${person.hospital || 'N/A'}</span>
                </div>
                <div class="card-item" style="grid-column: span 2;">
                    <span class="label">📍 Zona Hospital</span>
                    <span class="value">${person.zonaHospital ? `<span class="zona-hospital">${person.zonaHospital}</span>` : 'N/A'}</span>
                </div>
                <div class="card-item">
                    <span class="label">📞 Teléfono</span>
                    <span class="value">${person.telefono || 'N/A'}</span>
                </div>
                <div class="card-item">
                    <span class="label">✉️ Correo</span>
                    <span class="value">${person.correo || 'N/A'}</span>
                </div>
                <div class="card-item" style="grid-column: span 2;">
                    <span class="label">🏠 Casa</span>
                    <span class="value">
                        ${person.casaDestruida === 'si' ? '<span class="badge-destruida">🏚️ Destruida</span>' : 
                          person.casaDestruida === 'averiada' ? '<span class="badge-averiada">⚠️ Averiada</span>' : 
                          '<span class="badge-no-averiada">✅ No</span>'}
                    </span>
                </div>
            </div>
            
            <div class="card-actions">
                <button class="btn-small btn-edit" onclick="openEditModal(${person.id})">
                    ✏️ Editar
                </button>
                ${person.estado === 'desaparecido' ? `
                    <button class="btn-small btn-found" onclick="markAsFound(${person.id})">
                        ✅ Encontrar
                    </button>
                ` : `
                    <button class="btn-small btn-edit" onclick="markAsMissing(${person.id})">
                        ↩️ Desaparecido
                    </button>
                `}
                <button class="btn-small btn-delete" onclick="deletePerson(${person.id})">
                    🗑️
                </button>
            </div>
        </div>
    `).join('');
}

// Obtener badge de edad
function getEdadBadge(edad) {
    if (edad <= 12) return '<span class="edad-badge edad-nino">👶 Niño</span>';
    if (edad <= 59) return '<span class="edad-badge edad-adulto">👨 Adulto</span>';
    return '<span class="edad-badge edad-anciano">👴 Anciano</span>';
}

// Marcar como encontrado
async function markAsFound(id) {
    try {
        const response = await fetch(`${API_URL}mark_found.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id, estado: 'encontrado' })
        });
        
        const result = await response.json();
        if (result.success) {
            await loadPersons(document.getElementById('searchInput').value);
            showNotification(`✅ ${result.persona?.primer_nombre || 'Persona'} ha sido encontrado!`, 'success');
        } else {
            showNotification('❌ Error: ' + result.error, 'error');
        }
    } catch (error) {
        showNotification('Error al marcar como encontrado', 'error');
        console.error('Error:', error);
    }
}

// Marcar como desaparecido
async function markAsMissing(id) {
    try {
        const response = await fetch(`${API_URL}mark_found.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id, estado: 'desaparecido' })
        });
        
        const result = await response.json();
        if (result.success) {
            await loadPersons(document.getElementById('searchInput').value);
            showNotification(`↩️ ${result.persona?.primer_nombre || 'Persona'} marcado como desaparecido`, 'error');
        } else {
            showNotification('❌ Error: ' + result.error, 'error');
        }
    } catch (error) {
        showNotification('Error al actualizar el estado', 'error');
        console.error('Error:', error);
    }
}

// Eliminar persona
async function deletePerson(id) {
    if (!confirm('¿Estás seguro de eliminar este registro?')) return;
    
    try {
        const response = await fetch(`${API_URL}delete_person.php`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id })
        });
        
        const result = await response.json();
        if (result.success) {
            await loadPersons(document.getElementById('searchInput').value);
            showNotification('🗑️ Registro eliminado correctamente', 'error');
        } else {
            showNotification('❌ Error: ' + result.error, 'error');
        }
    } catch (error) {
        showNotification('Error al eliminar el registro', 'error');
        console.error('Error:', error);
    }
}

// Agregar persona
document.getElementById('personForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const edad = parseInt(document.getElementById('edad').value);
    if (edad < 0 || edad > 120) {
        showNotification('⚠️ La edad debe estar entre 0 y 120 años', 'error');
        return;
    }
    
    const cedula = document.getElementById('cedula').value.trim();
    if (!/^[0-9]{7,10}$/.test(cedula)) {
        showNotification('⚠️ La cédula debe tener entre 7 y 10 dígitos', 'error');
        return;
    }
    
    const personData = {
        localidad: document.getElementById('localidad').value.trim(),
        primerNombre: document.getElementById('primerNombre').value.trim(),
        segundoNombre: document.getElementById('segundoNombre').value.trim(),
        primerApellido: document.getElementById('primerApellido').value.trim(),
        segundoApellido: document.getElementById('segundoApellido').value.trim(),
        cedula: cedula,
        edad: edad,
        hospital: document.getElementById('hospital').value.trim(),
        zonaHospital: document.getElementById('zonaHospital').value.trim(),
        telefono: document.getElementById('telefono').value.trim(),
        correo: document.getElementById('correo').value.trim(),
        casaDestruida: document.getElementById('casaDestruida').value
    };
    
    try {
        const response = await fetch(`${API_URL}add_person.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(personData)
        });
        
        const result = await response.json();
        if (result.success) {
            await loadPersons();
            this.reset();
            showNotification(`✅ ${personData.primerNombre} ${personData.primerApellido} registrado correctamente`, 'success');
        } else {
            showNotification('❌ Error: ' + result.error, 'error');
        }
    } catch (error) {
        showNotification('Error al registrar la persona', 'error');
        console.error('Error:', error);
    }
});

// Abrir modal de edición
function openEditModal(id) {
    const person = persons.find(p => p.id === id);
    if (!person) return;
    
    document.getElementById('editId').value = person.id;
    document.getElementById('editLocalidad').value = person.localidad;
    document.getElementById('editPrimerNombre').value = person.primerNombre;
    document.getElementById('editSegundoNombre').value = person.segundoNombre || '';
    document.getElementById('editPrimerApellido').value = person.primerApellido;
    document.getElementById('editSegundoApellido').value = person.segundoApellido || '';
    document.getElementById('editCedula').value = person.cedula;
    document.getElementById('editEdad').value = person.edad;
    document.getElementById('editHospital').value = person.hospital || '';
    document.getElementById('editZonaHospital').value = person.zonaHospital || '';
    document.getElementById('editTelefono').value = person.telefono || '';
    document.getElementById('editCorreo').value = person.correo || '';
    document.getElementById('editCasaDestruida').value = person.casaDestruida;
    document.getElementById('editEstado').value = person.estado;
    
    document.getElementById('editModal').classList.add('active');
}

// Cerrar modal de edición
function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
    document.getElementById('editForm').reset();
}

// Guardar edición
document.getElementById('editForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const id = parseInt(document.getElementById('editId').value);
    const edad = parseInt(document.getElementById('editEdad').value);
    
    if (edad < 0 || edad > 120) {
        showNotification('⚠️ La edad debe estar entre 0 y 120 años', 'error');
        return;
    }
    
    const cedula = document.getElementById('editCedula').value.trim();
    if (!/^[0-9]{7,10}$/.test(cedula)) {
        showNotification('⚠️ La cédula debe tener entre 7 y 10 dígitos', 'error');
        return;
    }
    
    const personData = {
        id: id,
        localidad: document.getElementById('editLocalidad').value.trim(),
        primerNombre: document.getElementById('editPrimerNombre').value.trim(),
        segundoNombre: document.getElementById('editSegundoNombre').value.trim(),
        primerApellido: document.getElementById('editPrimerApellido').value.trim(),
        segundoApellido: document.getElementById('editSegundoApellido').value.trim(),
        cedula: cedula,
        edad: edad,
        hospital: document.getElementById('editHospital').value.trim(),
        zonaHospital: document.getElementById('editZonaHospital').value.trim(),
        telefono: document.getElementById('editTelefono').value.trim(),
        correo: document.getElementById('editCorreo').value.trim(),
        casaDestruida: document.getElementById('editCasaDestruida').value,
        estado: document.getElementById('editEstado').value
    };
    
    try {
        const response = await fetch(`${API_URL}update_person.php`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(personData)
        });
        
        const result = await response.json();
        if (result.success) {
            await loadPersons(document.getElementById('searchInput').value);
            closeEditModal();
            showNotification('✅ Información actualizada correctamente', 'success');
        } else {
            showNotification('❌ Error: ' + result.error, 'error');
        }
    } catch (error) {
        showNotification('Error al actualizar la información', 'error');
        console.error('Error:', error);
    }
});

// Buscar
function filterTable() {
    const searchTerm = document.getElementById('searchInput').value.trim();
    loadPersons(searchTerm);
}

// Resetear filtros
function resetFilters() {
    document.getElementById('searchInput').value = '';
    loadPersons();
}

// Actualizar estadísticas
function updateStats() {
    const total = persons.length;
    const missing = persons.filter(p => p.estado === 'desaparecido').length;
    const found = persons.filter(p => p.estado === 'encontrado').length;
    
    document.getElementById('totalCount').textContent = total;
    document.getElementById('missingCount').textContent = missing;
    document.getElementById('foundCount').textContent = found;
}

// Notificaciones
function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.className = 'notification';
    if (type === 'error') {
        notification.classList.add('error');
    }
    notification.style.display = 'block';
    
    setTimeout(() => {
        notification.style.display = 'none';
    }, 4000);
}

// Redimensionar ventana - Cambiar vista
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        if (persons.length > 0) {
            renderTable();
        }
    }, 250);
});

// Buscar en tiempo real
document.getElementById('searchInput').addEventListener('input', function() {
    if (this.value.length > 2 || this.value.length === 0) {
        filterTable();
    }
});

// Cargar datos al iniciar
loadPersons();
