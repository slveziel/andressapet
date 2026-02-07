/**
 * Andressa Pet - JavaScript
 */

const API = '/api';

let state = {
    donos: [],
    pets: [],
    consultas: [],
    donosMap: {},
    petsMap: {}
};

// API Helper
async function api(endpoint, options = {}) {
    const res = await fetch(`${API}${endpoint}`, {
        headers: { 'Content-Type': 'application/json' },
        ...options,
        body: options.body ? JSON.stringify(options.body) : undefined
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Erro na requisição');
    return data;
}

function toast(msg, type = 'success') {
    document.querySelectorAll('.toast').forEach(t => t.remove());
    const div = document.createElement('div');
    div.className = `toast ${type}`;
    div.textContent = msg;
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 3000);
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function formatDateTime(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
}

// Navigation
function showPage(page) {
    document.querySelectorAll('.page').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.header-nav a').forEach(a => a.classList.remove('active'));
    document.getElementById(`page-${page}`).style.display = 'block';
    document.querySelector(`[data-page="${page}"]`)?.classList.add('active');
    
    switch (page) {
        case 'dashboard': loadDashboard(); break;
        case 'pets': loadPets(); break;
        case 'consultas': loadConsultas(); break;
        case 'agenda': loadAgenda(); break;
        case 'prontuarios': loadProntuarios(); break;
    }
}

// Dashboard
async function loadDashboard() {
    try {
        const data = await api('/dashboard');
        document.getElementById('stat-pets').textContent = data.total_pets || 0;
        document.getElementById('stat-donos').textContent = data.total_donos || 0;
        document.getElementById('stat-hoje').textContent = data.consultas_hoje || 0;
        document.getElementById('stat-semana').textContent = data.consultas_semana || 0;
        
        document.getElementById('data-hoje').textContent = formatDate(new Date());
        
        renderProximasConsultas(data.proximas || []);
        loadAgendaDia(new Date().toISOString().split('T')[0]);
    } catch (e) { console.error(e); }
}

function renderProximasConsultas(consultas) {
    const el = document.getElementById('proximas-consultas');
    if (!consultas.length) {
        el.innerHTML = '<div class="empty-state">Nenhuma consulta próxima</div>';
        return;
    }
    el.innerHTML = consultas.slice(0, 5).map(c => `
        <div class="agenda-item">
            <span class="agenda-time">${formatDateTime(c.data_consulta)}</span>
            <div class="agenda-info">
                <h4>${c.pet_nome} (${c.especie})</h4>
                <p>${c.dono_nome} - ${c.tipo.replace('_', ' ')}</p>
            </div>
            <span class="badge status-${c.status}">${c.status}</span>
        </div>
    `).join('');
}

// Pets
async function loadPets() {
    try {
        state.pets = await api('/pets');
        renderPets(state.pets);
        await loadDonosSelect();
    } catch (e) { toast(e.message, 'error'); }
}

function renderPets(pets) {
    const el = document.getElementById('lista-pets');
    if (!pets.length) {
        el.innerHTML = '<div class="empty-state">Nenhum pet cadastrado</div>';
        return;
    }
    el.innerHTML = `<table class="table">
        <thead><tr><th>Pet</th><th>Espécie</th><th>Dono</th><th>Contato</th><th>Ações</th></tr></thead>
        <tbody>${pets.map(p => `
            <tr>
                <td><strong>${p.nome}</strong></td>
                <td>${p.especie} ${p.raca ? '- ' + p.raca : ''}</td>
                <td>${p.dono_nome}</td>
                <td>${p.telefone || '-'}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="verPet(${p.id})">Ver</button>
                    <button class="btn btn-sm btn-outline" onclick="editarPet(${p.id})">Editar</button>
                    <button class="btn btn-sm btn-danger" onclick="excluirPet(${p.id})">Excluir</button>
                </td>
            </tr>
        `).join('')}</tbody>
    </table>`;
}

async function buscarPets() {
    const busca = document.getElementById('busca-pet').value;
    const pets = await api(`/pets?busca=${encodeURIComponent(busca)}`);
    renderPets(pets);
}

function abrirModalPet(id = null) {
    document.getElementById('form-pet').reset();
    document.getElementById('pet-id').value = id || '';
    document.getElementById('modal-pet-titulo').textContent = id ? 'Editar Pet' : 'Novo Pet';
    loadDonosSelect();
    abrirModal('modal-pet');
}

async function editarPet(id) {
    const pet = state.pets.find(p => p.id === id);
    if (!pet) return;
    
    document.getElementById('pet-id').value = pet.id;
    document.getElementById('pet-dono').value = pet.dono_id;
    document.getElementById('pet-nome').value = pet.nome;
    document.getElementById('pet-especie').value = pet.especie;
    document.getElementById('pet-raca').value = pet.raca || '';
    document.getElementById('pet-sexo').value = pet.sexo;
    document.getElementById('pet-nascimento').value = pet.data_nascimento || '';
    document.getElementById('pet-peso').value = pet.peso || '';
    document.getElementById('pet-cor').value = pet.cor || '';
    document.getElementById('pet-observacoes').value = pet.observacoes || '';
    
    document.getElementById('modal-pet-titulo').textContent = 'Editar Pet';
    abrirModal('modal-pet');
}

async function salvarPet(e) {
    e.preventDefault();
    const id = document.getElementById('pet-id').value;
    const data = {
        dono_id: parseInt(document.getElementById('pet-dono').value),
        nome: document.getElementById('pet-nome').value,
        especie: document.getElementById('pet-especie').value,
        raca: document.getElementById('pet-raca').value || null,
        sexo: document.getElementById('pet-sexo').value,
        data_nascimento: document.getElementById('pet-nascimento').value || null,
        peso: document.getElementById('pet-peso').value || null,
        cor: document.getElementById('pet-cor').value || null,
        observacoes: document.getElementById('pet-observacoes').value || null
    };
    
    try {
        if (id) {
            await api(`/pets/${id}`, { method: 'PUT', body: data });
        } else {
            await api('/pets', { method: 'POST', body: data });
        }
        toast('Pet salvo!');
        fecharModal('modal-pet');
        loadPets();
    } catch (e) { toast(e.message, 'error'); }
}

async function verPet(id) {
    try {
        const pet = await api(`/pets/${id}/historico`);
        state.petsMap[id] = pet;
        
        document.getElementById('pet-detalhe-nome').textContent = `🐕 ${pet.nome}`;
        document.getElementById('pet-detalhe-content').innerHTML = `
            <div class="grid grid-2">
                <div>
                    <p><strong>Espécie:</strong> ${pet.especie}</p>
                    <p><strong>Raça:</strong> ${pet.raca || '-'}</p>
                    <p><strong>Sexo:</strong> ${pet.sexo}</p>
                    <p><strong>Nascimento:</strong> ${formatDate(pet.data_nascimento)}</p>
                </div>
                <div>
                    <p><strong>Peso:</strong> ${pet.peso ? pet.peso + ' kg' : '-'}</p>
                    <p><strong>Cor:</strong> ${pet.cor || '-'}</p>
                    <p><strong>Dono:</strong> ${pet.dono_nome}</p>
                    <p><strong>Telefone:</strong> ${pet.telefone || '-'}</p>
                </div>
            </div>
            <hr>
            <h3>📅 Histórico de Consultas</h3>
            ${pet.consultas?.length ? pet.consultas.map(c => `
                <div class="timeline-item">
                    <div class="timeline-date">${formatDateTime(c.data_consulta)}</div>
                    <div class="timeline-title">${c.tipo.replace('_', ' ')} - <span class="badge status-${c.status}">${c.status}</span></div>
                    <div class="timeline-content">
                        ${c.diagnostico || '-'}
                        ${c.prescricao ? '<br><small>Prescrição: ' + c.prescricao.substring(0, 100) + '...</small>' : ''}
                    </div>
                </div>
            `).join('') : '<p>Nenhuma consulta</p>'}
        `;
        
        showPage('pet-detalhe');
    } catch (e) { toast(e.message, 'error'); }
}

async function excluirPet(id) {
    if (!confirm('Excluir este pet?')) return;
    try {
        await api(`/pets/${id}`, { method: 'DELETE' });
        toast('Pet excluído!');
        loadPets();
    } catch (e) { toast(e.message, 'error'); }
}

// Donos
async function loadDonos() {
    try {
        state.donos = await api('/donos');
        renderDonos();
    } catch (e) { toast(e.message, 'error'); }
}

function renderDonos() {
    const el = document.getElementById('lista-donos');
    if (!state.donos.length) {
        el.innerHTML = '<div class="empty-state">Nenhum dono cadastrado</div>';
        return;
    }
    el.innerHTML = `<table class="table">
        <thead><tr><th>Nome</th><th>Telefone</th><th>Email</th><th>Pets</th><th>Ações</th></tr></thead>
        <tbody>${state.donos.map(d => `
            <tr>
                <td><strong>${d.nome}</strong></td>
                <td>${d.telefone || '-'}</td>
                <td>${d.email || '-'}</td>
                <td>${d.pets?.length || 0}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="verDono(${d.id})">Ver</button>
                    <button class="btn btn-sm btn-outline" onclick="editarDono(${d.id})">Editar</button>
                </td>
            </tr>
        `).join('')}</tbody>
    </table>`;
}

async function loadDonosSelect() {
    try {
        state.donos = await api('/donos');
        state.donosMap = {};
        state.donos.forEach(d => state.donosMap[d.id] = d);
        
        const select = document.getElementById('pet-dono');
        if (select) {
            select.innerHTML = '<option value="">Selecione...</option>' + 
                state.donos.map(d => `<option value="${d.id}">${d.nome}</option>`).join('');
        }
    } catch (e) { console.error(e); }
}

function abrirModalDono(id = null) {
    document.getElementById('form-dono').reset();
    document.getElementById('dono-id').value = id || '';
    document.getElementById('modal-dono-titulo').textContent = id ? 'Editar Dono' : 'Novo Dono';
    abrirModal('modal-dono');
}

async function salvarDono(e) {
    e.preventDefault();
    const id = document.getElementById('dono-id').value;
    const data = {
        nome: document.getElementById('dono-nome').value,
        telefone: document.getElementById('dono-telefone').value || null,
        email: document.getElementById('dono-email').value || null,
        endereco: document.getElementById('dono-endereco').value || null,
        observacoes: document.getElementById('dono-observacoes').value || null
    };
    
    try {
        if (id) {
            await api(`/donos/${id}`, { method: 'PUT', body: data });
        } else {
            await api('/donos', { method: 'POST', body: data });
        }
        toast('Dono salvo!');
        fecharModal('modal-dono');
        loadDonos();
        loadDonosSelect();
    } catch (e) { toast(e.message, 'error'); }
}

async function verDono(id) {
    const dono = await api(`/donos/${id}`);
    alert(`Dono: ${dono.nome}\nTelefone: ${dono.telefone || '-'}\nEmail: ${dono.email || '-'}\n\nPets: ${dono.pets?.map(p => p.nome).join(', ') || 'Nenhum'}`);
}

async function editarDono(id) {
    const dono = state.donos.find(d => d.id === id);
    if (!dono) return;
    
    document.getElementById('dono-id').value = dono.id;
    document.getElementById('dono-nome').value = dono.nome;
    document.getElementById('dono-telefone').value = dono.telefone || '';
    document.getElementById('dono-email').value = dono.email || '';
    document.getElementById('dono-endereco').value = dono.endereco || '';
    document.getElementById('dono-observacoes').value = dono.observacoes || '';
    
    document.getElementById('modal-dono-titulo').textContent = 'Editar Dono';
    abrirModal('modal-dono');
}

// Consultas
async function loadPetsSelect() {
    try {
        state.pets = await api('/pets');
        state.petsMap = {};
        state.pets.forEach(p => state.petsMap[p.id] = p);
        
        const select = document.getElementById('consulta-pet');
        if (select) {
            select.innerHTML = '<option value="">Selecione...</option>' + 
                state.pets.map(p => `<option value="${p.id}">${p.nome} (${p.dono_nome})</option>`).join('');
        }
    } catch (e) { console.error(e); }
}

async function loadConsultas() {
    const status = document.getElementById('filtro-consulta').value;
    let url = '/consultas';
    if (status) url += `?status=${status}`;
    
    try {
        state.consultas = await api(url);
        renderConsultas();
    } catch (e) { toast(e.message, 'error'); }
}

function renderConsultas() {
    const el = document.getElementById('lista-consultas');
    if (!state.consultas.length) {
        el.innerHTML = '<div class="empty-state">Nenhuma consulta encontrada</div>';
        return;
    }
    el.innerHTML = `<table class="table">
        <thead><tr><th>Data</th><th>Pet</th><th>Dono</th><th>Tipo</th><th>Status</th><th>Valor</th><th>Ações</th></tr></thead>
        <tbody>${state.consultas.map(c => `
            <tr>
                <td>${formatDateTime(c.data_consulta)}</td>
                <td><strong>${c.pet_nome}</strong> (${c.especie})</td>
                <td>${c.dono_nome}</td>
                <td>${c.tipo.replace('_', ' ')}</td>
                <td><span class="badge status-${c.status}">${c.status}</span></td>
                <td>${c.valor ? 'R$ ' + c.valor : '-'}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="verConsulta(${c.id})">Ver</button>
                    <button class="btn btn-sm btn-danger" onclick="cancelarConsulta(${c.id})">Cancelar</button>
                </td>
            </tr>
        `).join('')}</tbody>
    </table>`;
}

function abrirModalConsulta(id = null) {
    document.getElementById('form-consulta').reset();
    document.getElementById('consulta-id').value = id || '';
    document.getElementById('consulta-status').value = 'agendada';
    document.getElementById('modal-consulta-titulo').textContent = id ? 'Editar Consulta' : 'Nova Consulta';
    loadPetsSelect();
    abrirModal('modal-consulta');
}

async function salvarConsulta(e) {
    e.preventDefault();
    const id = document.getElementById('consulta-id').value;
    const data = {
        pet_id: parseInt(document.getElementById('consulta-pet').value),
        data_consulta: document.getElementById('consulta-data').value,
        tipo: document.getElementById('consulta-tipo').value,
        status: document.getElementById('consulta-status').value,
        valor: document.getElementById('consulta-valor').value || null,
        observacoes: document.getElementById('consulta-observacoes').value || null
    };
    
    try {
        if (id) {
            await api(`/consultas/${id}`, { method: 'PUT', body: data });
        } else {
            await api('/consultas', { method: 'POST', body: data });
        }
        toast('Consulta salva!');
        fecharModal('modal-consulta');
        loadConsultas();
    } catch (e) { toast(e.message, 'error'); }
}

async function verConsulta(id) {
    const consulta = await api(`/consultas/${id}`);
    state.consultaAtual = consulta;
    
    document.getElementById('consulta-detalhe-content').innerHTML = `
        <div class="grid grid-2">
            <div>
                <p><strong>Pet:</strong> ${consulta.pet_nome} (${consulta.especie})</p>
                <p><strong>Dono:</strong> ${consulta.dono_nome}</p>
                <p><strong>Telefone:</strong> ${consulta.dono_telefone || '-'}</p>
            </div>
            <div>
                <p><strong>Data:</strong> ${formatDateTime(consulta.data_consulta)}</p>
                <p><strong>Tipo:</strong> ${consulta.tipo.replace('_', ' ')}</p>
                <p><strong>Status:</strong> <span class="badge status-${consulta.status}">${consulta.status}</span></p>
            </div>
        </div>
        ${consulta.observacoes ? `<p><strong>Observações:</strong> ${consulta.observacoes}</p>` : ''}
        ${consulta.prontuario ? `
            <hr>
            <h4>📋 Prontuário</h4>
            <p><strong>Diagnóstico:</strong> ${consulta.prontuario.diagnostico || '-'}</p>
            <p><strong>Prescrição:</strong> ${consulta.prontuario.prescricao || '-'}</p>
            <p><strong>Peso:</strong> ${consulta.prontuario.peso_atual || '-'}</p>
        ` : ''}
    `;
    
    document.getElementById('consulta-id').value = consulta.id;
    abrirModal('modal-consulta-detalhe');
}

async function cancelarConsulta(id) {
    if (!confirm('Cancelar esta consulta?')) return;
    try {
        await api(`/consultas/${id}`, { method: 'DELETE' });
        toast('Consulta cancelada!');
        loadConsultas();
    } catch (e) { toast(e.message, 'error'); }
}

async function finalizarConsulta() {
    if (!state.consultaAtual) return;
    try {
        await api(`/consultas/${state.consultaAtual.id}`, { method: 'PUT', body: { status: 'finalizada' } });
        toast('Consulta finalizada!');
        fecharModal('modal-consulta-detalhe');
        loadConsultas();
    } catch (e) { toast(e.message, 'error'); }
}

// Agenda
function loadAgenda() {
    const data = document.getElementById('data-agenda').value || new Date().toISOString().split('T')[0];
    loadAgendaDia(data);
}

async function loadAgendaDia(data) {
    try {
        const consultas = await api(`/agenda?data=${data}`);
        renderAgenda(consultas, data);
    } catch (e) { toast(e.message, 'error'); }
}

function renderAgenda(consultas, data) {
    const el = document.getElementById('lista-agenda');
    document.getElementById('data-agenda').value = data;
    
    if (!consultas.length) {
        el.innerHTML = `<p style="text-align: center; color: var(--gray-500);">Nenhuma consulta em ${formatDate(data)}</p>`;
        return;
    }
    
    el.innerHTML = consultas.map(c => `
        <div class="agenda-item">
            <span class="agenda-time">${new Date(c.data_consulta).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })}</span>
            <div class="agenda-info">
                <h4>${c.pet_nome} (${c.especie})</h4>
                <p>${c.dono_nome} - ${c.tipo.replace('_', ' ')} ${c.valor ? '- R$ ' + c.valor : ''}</p>
            </div>
            <span class="badge status-${c.status}">${c.status}</span>
        </div>
    `).join('');
}

// Prontuários
async function loadProntuarios() {
    try {
        const consultas = await api('/consultas?status=finalizada');
        renderProntuarios(consultas);
    } catch (e) { toast(e.message, 'error'); }
}

function renderProntuarios(consultas) {
    const el = document.getElementById('lista-prontuarios');
    if (!consultas.length) {
        el.innerHTML = '<div class="empty-state">Nenhum prontuário encontrado</div>';
        return;
    }
    el.innerHTML = `<table class="table">
        <thead><tr><th>Data</th><th>Pet</th><th>Dono</th><th>Tipo</th><th>Diagnóstico</th></tr></thead>
        <tbody>${consultas.map(c => `
            <tr>
                <td>${formatDateTime(c.data_consulta)}</td>
                <td><strong>${c.pet_nome}</strong></td>
                <td>${c.dono_nome}</td>
                <td>${c.tipo.replace('_', ' ')}</td>
                <td>${c.diagnostico || '-'}</td>
            </tr>
        `).join('')}</tbody>
    </table>`;
}

function abrirProntuario() {
    if (!state.consultaAtual) return;
    
    document.getElementById('prontuario-consulta-id').value = state.consultaAtual.id;
    
    if (state.consultaAtual.prontuario) {
        const p = state.consultaAtual.prontuario;
        document.getElementById('prontuario-peso').value = p.peso_atual || '';
        document.getElementById('prontuario-temperatura').value = p.temperatura || '';
        document.getElementById('prontuario-fc').value = p.fc || '';
        document.getElementById('prontuario-fr').value = p.fr || '';
        document.getElementById('prontuario-queixa').value = p.queixa || '';
        document.getElementById('prontuario-historico').value = p.historico || '';
        document.getElementById('prontuario-exame-fisico').value = p.exame_fisico || '';
        document.getElementById('prontuario-hipoteses').value = p.hipoteses_diagnosticas || '';
        document.getElementById('prontuario-diagnostico').value = p.diagnostico || '';
        document.getElementById('prontuario-prescricao').value = p.prescricao || '';
        document.getElementById('prontuario-exames').value = p.exames_solicitados || '';
        document.getElementById('prontuario-orientacoes').value = p.orientacoes || '';
        document.getElementById('prontuario-atestado').value = p.atestado || '';
    } else {
        document.getElementById('form-prontuario').reset();
    }
    
    fecharModal('modal-consulta-detalhe');
    abrirModal('modal-prontuario');
}

async function salvarProntuario(e) {
    e.preventDefault();
    const consultaId = document.getElementById('prontuario-consulta-id').value;
    const data = {
        peso_atual: document.getElementById('prontuario-peso').value || null,
        temperatura: document.getElementById('prontuario-temperatura').value || null,
        fc: document.getElementById('prontuario-fc').value || null,
        fr: document.getElementById('prontuario-fr').value || null,
        queixa: document.getElementById('prontuario-queixa').value || null,
        historico: document.getElementById('prontuario-historico').value || null,
        exame_fisico: document.getElementById('prontuario-exame-fisico').value || null,
        hipoteses_diagnosticas: document.getElementById('prontuario-hipoteses').value || null,
        diagnostico: document.getElementById('prontuario-diagnostico').value || null,
        prescricao: document.getElementById('prontuario-prescricao').value || null,
        exames_solicitados: document.getElementById('prontuario-exames').value || null,
        orientacoes: document.getElementById('prontuario-orientacoes').value || null,
        atestado: document.getElementById('prontuario-atestado').value || null
    };
    
    try {
        await api(`/prontuarios/${consultaId}`, { method: 'POST', body: data });
        toast('Prontuário salvo!');
        fecharModal('modal-prontuario');
    } catch (e) { toast(e.message, 'error'); }
}

// Modals
function abrirModal(id) { document.getElementById(id).classList.add('show'); }
function fecharModal(id) { document.getElementById(id).classList.remove('show'); }

// Init
document.addEventListener('DOMContentLoaded', () => {
    loadDashboard();
});
