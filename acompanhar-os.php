<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
include 'templates/header.php';
?>
<style>
    /* Estilos para a linha do tempo */
    .timeline-item:before {
        content: '';
        display: block;
        width: 12px;
        height: 12px;
        background-color: white;
        border: 2px solid var(--cor-primaria);
        border-radius: 50%;
        position: absolute;
        left: -6px;
        top: 4px;
        z-index: 1;
    }
    .timeline-item:not(:last-child):after {
        content: '';
        display: block;
        width: 2px;
        height: 100%;
        background-color: #e5e7eb; /* gray-200 */
        position: absolute;
        left: 0;
        top: 12px;
    }
    /* Estrelas da avaliação */
    .star-rating span { cursor: pointer; transition: color 0.2s; }
    .star-rating:hover span { color: #facc15; /* yellow-400 */ }
    .star-rating span:hover ~ span { color: #d1d5db; /* gray-300 */ }
    .star-rating [data-rated="true"] { color: #facc15; }
</style>

<div class="container mx-auto px-6 py-12">
    <div class="max-w-md mx-auto">
        <h1 class="text-3xl font-bold text-center brand-text mb-2">Acompanhar Ordem de Serviço</h1>
        <p class="text-center text-gray-600 mb-8">Insira os dados para ver o status do seu reparo.</p>

        <div class="bg-white p-8 rounded-2xl shadow-xl">
            <form id="track-form">
                <div class="space-y-4">
                    <div>
                        <label for="os_id" class="font-medium">Número da OS</label>
                        <input type="number" name="os_id" id="os_id" required placeholder="Ex: 123" class="w-full mt-1 p-3 border rounded-md brand-ring-focus">
                    </div>
                    <div>
                        <label for="whatsapp" class="font-medium">Seu WhatsApp (usado no cadastro)</label>
                        <input type="tel" name="whatsapp" id="whatsapp" required placeholder="(00) 00000-0000" class="w-full mt-1 p-3 border rounded-md brand-ring-focus">
                    </div>
                </div>
                <button type="submit" class="w-full mt-6 brand-bg text-white font-bold py-3 px-6 rounded-md brand-bg-hover transition-all flex items-center justify-center">
                    <span class="btn-text">Buscar</span>
                    <div class="spinner hidden w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                </button>
            </form>
        </div>
    </div>
    
    <div id="result-container" class="max-w-4xl mx-auto mt-10"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    IMask(document.getElementById('whatsapp'), { mask: '(00) 00000-0000' });
    const trackForm = document.getElementById('track-form');
    const resultContainer = document.getElementById('result-container');
    const submitBtn = trackForm.querySelector('button[type="submit"]');

    trackForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        resultContainer.innerHTML = ''; // Limpa resultados anteriores
        submitBtn.disabled = true;
        submitBtn.querySelector('.btn-text').classList.add('hidden');
        submitBtn.querySelector('.spinner').classList.remove('hidden');

        const formData = new FormData(trackForm);
        
        try {
            const response = await fetch('api/track_os_handler.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.success) {
                renderResults(result.data);
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            resultContainer.innerHTML = `<div class="bg-red-100 text-red-700 p-4 rounded-lg text-center">${error.message}</div>`;
        } finally {
            submitBtn.disabled = false;
            submitBtn.querySelector('.btn-text').classList.remove('hidden');
            submitBtn.querySelector('.spinner').classList.add('hidden');
        }
    });

    function renderResults(data) {
        const os = data.os;
        const history = data.history;

        let actionBlock = '';

        // Bloco de Ação: Orçamento
        if (os.status === 'Orçamento Enviado' && os.orcamento_status === 'Pendente') {
            const valorFormatado = parseFloat(os.valor_orcamento).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            actionBlock = `
                <div class="bg-yellow-50 border-2 border-yellow-300 p-6 rounded-lg text-center">
                    <h3 class="text-xl font-bold text-yellow-800">Orçamento Disponível!</h3>
                    <p class="text-gray-700 mt-2">O valor do reparo é de:</p>
                    <p class="text-4xl font-extrabold my-4">${valorFormatado}</p>
                    <div id="quote-buttons" class="flex justify-center gap-4">
                        <button data-action="decline_quote" class="os-action-btn bg-red-600 text-white font-bold py-2 px-6 rounded-md hover:bg-red-700">Recusar</button>
                        <button data-action="approve_quote" class="os-action-btn bg-green-600 text-white font-bold py-2 px-6 rounded-md hover:bg-green-700">Aprovar Orçamento</button>
                    </div>
                    <div id="quote-feedback" class="hidden"></div>
                </div>`;
        }
        
        // Bloco de Ação: Rastreio
        if (os.status === 'Enviado de Volta' && os.codigo_rastreio_devolucao) {
            actionBlock = `
                <div class="bg-blue-50 border-2 border-blue-300 p-6 rounded-lg text-center">
                    <h3 class="text-xl font-bold text-blue-800">Seu dispositivo foi enviado!</h3>
                    <p class="text-gray-700 mt-2">Use o código abaixo para rastrear no site dos Correios:</p>
                    <p class="text-2xl font-extrabold my-4 font-mono tracking-widest">${os.codigo_rastreio_devolucao}</p>
                    <a href="https://www2.correios.com.br/sistemas/rastreamento/resultado.cfm?objetos=${os.codigo_rastreio_devolucao}" target="_blank" class="inline-block brand-bg text-white font-bold py-2 px-6 rounded-md">Rastrear Agora</a>
                </div>`;
        }

        // Bloco de Ação: Avaliação
        if (['Finalizado', 'Entregue'].includes(os.status) && !data.has_review) {
            actionBlock = `
                <div class="bg-green-50 border-2 border-green-300 p-6 rounded-lg">
                    <h3 class="text-xl font-bold text-green-800 text-center">Serviço Concluído!</h3>
                    <p class="text-gray-700 mt-2 text-center">Sua opinião é muito importante para nós. Que tal deixar uma avaliação?</p>
                    <form id="review-form" class="mt-4 space-y-4">
                         <div>
                            <label for="review_name" class="font-medium">Seu Nome</label>
                            <input type="text" id="review_name" name="nome" value="${os.cliente_nome}" required class="w-full mt-1 p-2 border rounded-md">
                        </div>
                        <div>
                            <label class="font-medium">Sua Nota</label>
                            <div class="star-rating flex text-3xl text-gray-300" id="star-rating">
                                <span data-value="1">★</span><span data-value="2">★</span><span data-value="3">★</span><span data-value="4">★</span><span data-value="5">★</span>
                            </div>
                            <input type="hidden" name="estrelas" id="review_stars" required>
                        </div>
                        <div>
                            <label for="review_comment" class="font-medium">Seu Comentário</label>
                            <textarea id="review_comment" name="comentario" rows="3" class="w-full mt-1 p-2 border rounded-md"></textarea>
                        </div>
                        <button type="submit" class="w-full brand-bg text-white font-bold py-2 px-6 rounded-md">Enviar Avaliação</button>
                    </form>
                    <div id="review-feedback" class="hidden"></div>
                </div>`;
        } else if (['Finalizado', 'Entregue'].includes(os.status) && data.has_review) {
             actionBlock = `<div class="bg-green-100 text-green-800 p-4 rounded-lg text-center font-medium">Obrigado por sua avaliação!</div>`;
        }

        const historyHtml = history.map(h => {
            const dataFormatada = new Date(h.data_alteracao).toLocaleDateString('pt-BR', {day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'});
            return `
            <li class="timeline-item relative pl-6 pb-6">
                <h4 class="font-bold">${h.status_novo}</h4>
                <p class="text-sm text-gray-500">${dataFormatada}</p>
                ${h.observacao ? `<p class="text-sm text-gray-700 mt-1">${h.observacao}</p>` : ''}
            </li>`;
        }).join('');

        const resultHtml = `
        <div class="bg-white p-8 rounded-2xl shadow-xl">
            <h2 class="text-2xl font-bold mb-6 pb-4 border-b">Detalhes da OS #${os.id}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="md:col-span-1">
                    <h3 class="font-bold text-lg mb-4">Linha do Tempo</h3>
                    <ul class="border-l-2 border-gray-200">${historyHtml}</ul>
                </div>
                <div class="md:col-span-2">
                     <h3 class="font-bold text-lg mb-4">Informações</h3>
                     <div class="space-y-2 text-gray-700 mb-8">
                        <p><strong>Dispositivo:</strong> ${os.dispositivo_categoria}</p>
                        ${os.dispositivo_marca ? `<p><strong>Marca/Modelo:</strong> ${os.dispositivo_marca} / ${os.dispositivo_modelo}</p>`:''}
                        <p><strong>Status Atual:</strong> <span class="font-bold brand-text">${os.status}</span></p>
                     </div>
                     ${actionBlock}
                </div>
            </div>
        </div>`;

        resultContainer.innerHTML = resultHtml;
        attachActionListeners();
    }
    
    function attachActionListeners() {
        // Listener para botões de orçamento
        document.querySelectorAll('.os-action-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const action = e.currentTarget.dataset.action;
                const feedbackDiv = document.getElementById('quote-feedback');
                
                const formData = new FormData(trackForm);
                formData.append('action', action);

                try {
                    const response = await fetch('api/os_action_handler.php', { method: 'POST', body: formData });
                    const result = await response.json();
                    if(result.success) {
                        document.getElementById('quote-buttons').classList.add('hidden');
                        feedbackDiv.className = 'text-green-700 font-bold';
                        feedbackDiv.textContent = result.message;
                        feedbackDiv.classList.remove('hidden');
                        trackForm.requestSubmit(); // Recarrega os dados
                    } else { throw new Error(result.message); }
                } catch(error) {
                    feedbackDiv.className = 'text-red-700 font-bold';
                    feedbackDiv.textContent = error.message;
                    feedbackDiv.classList.remove('hidden');
                }
            });
        });

        // Listener para estrelas de avaliação
        const starsContainer = document.getElementById('star-rating');
        if (starsContainer) {
            const stars = starsContainer.querySelectorAll('span');
            stars.forEach(star => {
                star.addEventListener('mouseenter', (e) => {
                    stars.forEach(s => s.removeAttribute('data-rated'));
                    e.currentTarget.setAttribute('data-rated', 'true');
                });
                star.addEventListener('click', (e) => {
                    const rating = e.currentTarget.dataset.value;
                    document.getElementById('review_stars').value = rating;
                    stars.forEach((s, i) => {
                        s.setAttribute('data-rated', i < rating);
                    });
                });
            });
            starsContainer.addEventListener('mouseleave', () => {
                 const rating = document.getElementById('review_stars').value || 0;
                 stars.forEach((s, i) => s.setAttribute('data-rated', i < rating));
            });
        }
        
        // Listener para formulário de avaliação
        const reviewForm = document.getElementById('review-form');
        if(reviewForm) {
            reviewForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const feedbackDiv = document.getElementById('review-feedback');
                const formData = new FormData(trackForm); // Pega OS e WhatsApp
                const reviewData = new FormData(reviewForm); // Pega dados da avaliação
                
                // Combina os dados
                for (let [key, value] of reviewData.entries()) {
                    formData.append(key, value);
                }
                formData.append('action', 'submit_review');

                try {
                    const response = await fetch('api/os_action_handler.php', { method: 'POST', body: formData });
                    const result = await response.json();
                    if(result.success) {
                        reviewForm.classList.add('hidden');
                        feedbackDiv.className = 'text-green-700 font-bold text-center p-4';
                        feedbackDiv.textContent = result.message;
                        feedbackDiv.classList.remove('hidden');
                    } else { throw new Error(result.message); }
                } catch(error) {
                     feedbackDiv.className = 'text-red-700 font-bold';
                     feedbackDiv.textContent = error.message;
                     feedbackDiv.classList.remove('hidden');
                }
            });
        }
    }
});
</script>

<?php include 'templates/footer.php'; ?>