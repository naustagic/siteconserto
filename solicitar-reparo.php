<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// --- LÓGICA ATUALIZADA PARA BUSCAR DADOS DE COBERTURA COMPLETOS ---
$coverage_stmt = $pdo->query("SELECT city, state, allows_pickup FROM logistics_coverage");
$coverage_areas = $coverage_stmt->fetchAll(PDO::FETCH_ASSOC);
// Passa o array completo de objetos para o JavaScript
$area_abrangencia_json = json_encode($coverage_areas);

// Busca APENAS as categorias principais para o carregamento inicial
$categorias_principais = $pdo->query("SELECT id, name, requires_brand_model FROM form_options WHERE type = 'CATEGORY' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

include 'templates/header.php';
?>

<div class="container mx-auto px-6 py-12">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold text-center brand-text mb-2">Solicitação de Reparo</h1>
        <p class="text-center text-gray-600 mb-8">Preencha os passos para iniciar seu atendimento.</p>

        <div class="bg-white p-8 rounded-2xl shadow-xl card">
            <div id="progress-bar" class="flex justify-between items-center mb-8 text-sm md:text-base">
                <div class="step flex-1 text-center" data-step="0"><div class="font-bold brand-text">Seus Dados</div><div class="h-1 mt-2 brand-bg"></div></div>
                <div class="w-8 h-1 bg-gray-300 mx-2"></div>
                <div class="step flex-1 text-center" data-step="1"><div class="font-bold text-gray-400">Dispositivo</div><div class="h-1 mt-2 bg-gray-300"></div></div>
                <div class="w-8 h-1 bg-gray-300 mx-2"></div>
                <div class="step flex-1 text-center" data-step="2"><div class="font-bold text-gray-400">Problema</div><div class="h-1 mt-2 bg-gray-300"></div></div>
            </div>

            <form id="repair-form" novalidate>
                
                <div id="coverage-feedback">
                    <div id="area-covered-pickup" class="hidden bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-lg relative mb-4" role="alert">
                        <strong class="font-bold">Ótima notícia!</strong>
                        <span class="block sm:inline"> Atendemos sua região e você pode <strong class="font-semibold">trazer seu equipamento até nosso balcão</strong> sem custo.</span>
                    </div>
                    <div id="area-covered-delivery" class="hidden bg-blue-100 border border-blue-400 text-blue-800 px-4 py-3 rounded-lg relative mb-4" role="alert">
                        <strong class="font-bold">Ótima notícia!</strong>
                        <span class="block sm:inline"> Atendemos sua região com nosso <strong class="font-semibold">serviço de entrega/coleta</strong>.</span>
                    </div>
                    <div id="area-notice" class="hidden bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded-lg relative mb-4" role="alert">
                        Sua cidade está fora da nossa área de cobertura para retirada. Mas não se preocupe! Você pode nos enviar seu equipamento pelos Correios.
                    </div>
                </div>
                <div id="form-error" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4"></div>
                <div id="form-steps-container">
                    <div class="form-step" data-step-index="0">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2"><input type="text" name="nome" placeholder="Nome Completo" required class="w-full p-3 border rounded-md brand-ring-focus"></div>
                            <div><input type="tel" id="whatsapp" name="whatsapp" placeholder="WhatsApp" required class="w-full p-3 border rounded-md brand-ring-focus"></div>
                            <div><input type="email" name="email" placeholder="E-mail (opcional)" class="w-full p-3 border rounded-md brand-ring-focus"></div>
                            <div><input type="text" id="cep" name="cep" placeholder="CEP" required class="w-full p-3 border rounded-md brand-ring-focus"></div>
                            <div><input type="text" name="rua" placeholder="Rua / Logradouro" required class="w-full p-3 border bg-gray-100 rounded-md brand-ring-focus" readonly></div>
                            <div><input type="text" name="numero" placeholder="Número" required class="w-full p-3 border rounded-md brand-ring-focus"></div>
                            <div><input type="text" name="bairro" placeholder="Bairro" required class="w-full p-3 border bg-gray-100 rounded-md brand-ring-focus" readonly></div>
                            <div><input type="text" name="cidade" placeholder="Cidade" required class="w-full p-3 border bg-gray-100 rounded-md brand-ring-focus" readonly></div>
                            <div><input type="text" name="estado" placeholder="Estado" required class="w-full p-3 border bg-gray-100 rounded-md brand-ring-focus" readonly></div>
                        </div>
                    </div>
                    <div class="form-step hidden" data-step-index="1">
                        <div id="dynamic-step-2-content">
                            <h3 class="font-bold mb-2">Qual tipo de dispositivo?</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <?php foreach($categorias_principais as $cat): ?>
                                <button type="button" data-id="<?php echo $cat['id']; ?>" data-name="<?php echo sanitize_output($cat['name']); ?>" data-requires-brand="<?php echo $cat['requires_brand_model']; ?>" class="category-btn p-4 border-2 rounded-lg text-center font-semibold hover:border-[var(--cor-primaria)] transition-all"><?php echo sanitize_output($cat['name']); ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <input type="hidden" name="categoria_id" required>
                        <input type="hidden" name="subcategoria_id">
                        <input type="hidden" name="dispositivo_final" required>
                        <div id="brand-model-fields" class="hidden mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div><input type="text" name="marca" placeholder="Marca (Ex: Samsung, Dell)" class="w-full p-3 border rounded-md brand-ring-focus"></div>
                            <div><input type="text" name="modelo" placeholder="Modelo (Ex: Galaxy S22, G15)" class="w-full p-3 border rounded-md brand-ring-focus"></div>
                        </div>
                    </div>
                    <div class="form-step hidden" data-step-index="2">
                        <div id="dynamic-step-3-content">
                             <div class="text-center p-4">
                                <div class="w-8 h-8 border-4 border-gray-200 border-t-[var(--cor-primaria)] rounded-full animate-spin mx-auto"></div>
                                <p class="mt-2">Carregando problemas...</p>
                             </div>
                        </div>
                        <div class="mt-4">
                            <label class="font-bold block mb-2">Descreva com mais detalhes (obrigatório):</label>
                            <textarea name="descricao" rows="4" required class="w-full p-3 border rounded-md brand-ring-focus" placeholder="Ex: O console desliga sozinho após 10 minutos de jogo..."></textarea>
                        </div>
                        <div class="mt-4">
                            <label class="font-bold block mb-2">Envie uma foto ou vídeo (opcional):</label>
                            <input type="file" name="media" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-gray-200 flex justify-between items-center">
                    <button type="button" id="prev-step" class="bg-gray-200 text-gray-700 font-bold py-2 px-6 rounded-md hover:bg-gray-300 transition-all disabled:opacity-50">Voltar</button>
                    <button type="button" id="next-step" class="brand-bg text-white font-bold py-2 px-6 rounded-md brand-bg-hover transition-all">Avançar</button>
                    <button type="submit" id="submit-form" class="hidden brand-bg text-white font-bold py-2 px-6 rounded-md brand-bg-hover transition-all">
                        <span class="btn-text">Finalizar Solicitação</span>
                        <div class="spinner hidden w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    </button>
                </div>
            </form>

            <div id="form-success" class="hidden text-center py-10">
                <h2 class="text-3xl font-bold text-green-600">Solicitação Enviada!</h2>
                <p class="mt-2 text-gray-700">Sua Ordem de Serviço foi criada com sucesso.</p>
                <p class="mt-6 font-bold text-lg">Nº da sua OS: <span id="os-number" class="text-2xl brand-text"></span></p>
                <p class="mt-2 text-gray-600">Guarde este número! Você o usará para acompanhar o status do seu reparo.</p>
                <a href="guia-envio.php" class="mt-8 inline-block bg-gray-800 text-white font-bold py-3 px-8 rounded-md hover:bg-black transition-all">
                    Ver Guia de Como Enviar o Equipamento &rarr;
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 0;
    const form = document.getElementById('repair-form');
    const prevBtn = document.getElementById('prev-step');
    const nextBtn = document.getElementById('next-step');
    const submitBtn = document.getElementById('submit-form');
    // ADIÇÃO: O JavaScript agora recebe o array completo de objetos
    const areaAbrangencia = <?php echo $area_abrangencia_json; ?>;

    IMask(document.getElementById('whatsapp'), { mask: '(00) 00000-0000' });
    const cepInput = document.getElementById('cep');
    const cepMask = IMask(cepInput, { mask: '00000-000' });
    
    cepMask.on('complete', function() {
        handleCep(cepMask.unmaskedValue);
    });

    cepInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleCep(cepMask.unmaskedValue);
        }
    });

    function showStep(stepIndex) {
        document.querySelectorAll('.form-step').forEach(stepEl => stepEl.classList.add('hidden'));
        document.querySelector(`.form-step[data-step-index="${stepIndex}"]`).classList.remove('hidden');
        updateUI();
    }

    function updateUI() {
        document.querySelectorAll('.step').forEach((stepEl, index) => {
            const title = stepEl.querySelector('div:first-child');
            const bar = stepEl.querySelector('div:last-child');
            if (index <= currentStep) {
                title.classList.add('brand-text'); title.classList.remove('text-gray-400');
                bar.classList.add('brand-bg'); bar.classList.remove('bg-gray-300');
            } else {
                title.classList.remove('brand-text'); title.classList.add('text-gray-400');
                bar.classList.remove('brand-bg'); bar.classList.add('bg-gray-300');
            }
        });
        prevBtn.disabled = currentStep === 0;
        nextBtn.classList.toggle('hidden', currentStep === 2);
        submitBtn.classList.toggle('hidden', currentStep !== 2);
    }
    
    function validateStep() {
        const currentStepEl = document.querySelector(`.form-step[data-step-index="${currentStep}"]`);
        const inputs = currentStepEl.querySelectorAll('[required]');
        let isValid = true;
        document.getElementById('form-error').classList.add('hidden');
        inputs.forEach(input => {
            const fieldContainer = input.closest('div');
            if (fieldContainer) fieldContainer.classList.remove('input-error');
            input.classList.remove('input-error');

            if (!input.value.trim()) {
                isValid = false;
                if(input.type === 'hidden') {
                    const visualElement = document.getElementById('dynamic-step-2-content');
                    visualElement.classList.add('border-red-500', 'border-2', 'rounded-lg', 'p-2');
                } else {
                    input.classList.add('input-error');
                }
            }
        });
        if(!isValid) {
            document.getElementById('form-error').textContent = 'Por favor, preencha todos os campos obrigatórios.';
            document.getElementById('form-error').classList.remove('hidden');
        }
        return isValid;
    }

    nextBtn.addEventListener('click', () => {
        if (validateStep()) {
            currentStep++;
            showStep(currentStep);
        }
    });
    prevBtn.addEventListener('click', () => {
        if (currentStep > 0) {
            currentStep--;
            showStep(currentStep);
        }
    });

    async function fetchOptions(parentId, containerId, type) {
        const container = document.getElementById(containerId);
        container.innerHTML = `<div class="text-center p-4"><div class="w-8 h-8 border-4 border-gray-200 border-t-[var(--cor-primaria)] rounded-full animate-spin mx-auto"></div></div>`;
        try {
            const response = await fetch(`api/form_options_handler.php?parent_id=${parentId}`);
            if (!response.ok) throw new Error('Falha na rede');
            const options = await response.json();

            if (type === 'SUBCATEGORY') {
                container.innerHTML = `<h3 class="font-bold mb-2">Selecione o modelo específico:</h3>
                                     <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                     ${options.map(opt => `
                                         <button type="button" data-id="${opt.id}" data-name="${opt.name}" class="subcategory-btn p-4 border-2 rounded-lg text-center font-semibold hover:border-[var(--cor-primaria)] transition-all flex flex-col items-center justify-center">
                                             ${opt.icon_path ? `<img src="${opt.icon_path}" alt="${opt.name}" class="h-10 mb-2 object-contain">` : ''}
                                             <span>${opt.name}</span>
                                         </button>`).join('')}
                                     </div>`;
                container.querySelectorAll('.subcategory-btn').forEach(btn => btn.addEventListener('click', handleSubCategorySelection));
            }
            if (type === 'COMMON_PROBLEM') {
                 container.innerHTML = `<h3 class="font-bold mb-2">Quais problemas você notou? (selecione um ou mais)</h3>
                                     <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                     ${options.map(opt => `<label class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-100"><input type="checkbox" name="problemas[]" value="${opt.name}" class="rounded brand-ring-focus text-[var(--cor-primaria)]"> <span>${opt.name}</span></label>`).join('')}
                                     </div>`;
            }
        } catch (error) {
            container.innerHTML = `<p class="text-red-500">Erro ao carregar opções. Verifique o console.</p>`;
            console.error(error);
        }
    }
    
    document.querySelectorAll('.category-btn').forEach(btn => btn.addEventListener('click', handleCategorySelection));

    function handleCategorySelection(e) {
        const btn = e.currentTarget;
        const catId = btn.dataset.id;
        const catName = btn.dataset.name;
        const requiresBrand = btn.dataset.requiresBrand === '1';

        document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('border-[var(--cor-primaria)]', 'bg-indigo-50'));
        btn.classList.add('border-[var(--cor-primaria)]', 'bg-indigo-50');
        
        form.querySelector('[name="categoria_id"]').value = catId;
        form.querySelector('[name="dispositivo_final"]').value = catName;
        form.querySelector('[name="subcategoria_id"]').value = '';
        document.getElementById('dynamic-step-2-content').classList.remove('border-red-500', 'border-2', 'rounded-lg', 'p-2');
        
        const brandModelFields = document.getElementById('brand-model-fields');
        const fields = brandModelFields.querySelectorAll('input');
        if (requiresBrand) {
            brandModelFields.classList.remove('hidden');
            fields.forEach(f => f.required = true);
            fetchOptions(catId, 'dynamic-step-3-content', 'COMMON_PROBLEM');
        } else {
            brandModelFields.classList.add('hidden');
            fields.forEach(f => { f.required = false; f.value = ''; });
            fetchOptions(catId, 'dynamic-step-2-content', 'SUBCATEGORY');
        }
    }
    
    function handleSubCategorySelection(e) {
        const btn = e.currentTarget;
        const subCatId = btn.dataset.id;
        const subCatName = btn.dataset.name;

        document.querySelectorAll('.subcategory-btn').forEach(b => b.classList.remove('border-[var(--cor-primaria)]', 'bg-indigo-50'));
        btn.classList.add('border-[var(--cor-primaria)]', 'bg-indigo-50');

        form.querySelector('[name="subcategoria_id"]').value = subCatId;
        form.querySelector('[name="dispositivo_final"]').value = subCatName;
        fetchOptions(subCatId, 'dynamic-step-3-content', 'COMMON_PROBLEM');
    }

    // ADIÇÃO: Lógica de aviso de cobertura aprimorada
    async function handleCep(cep) {
        if (cep.length !== 8) return;

        // Esconde todos os avisos antes de uma nova busca
        document.getElementById('coverage-feedback').querySelectorAll('div[role="alert"]').forEach(el => el.classList.add('hidden'));
        
        try {
            const response = await fetch(`api/cep_handler.php?cep=${cep}`);
            const data = await response.json();
            if (!data.erro) {
                form.querySelector('[name="rua"]').value = data.logradouro;
                form.querySelector('[name="bairro"]').value = data.bairro;
                form.querySelector('[name="cidade"]').value = data.localidade;
                form.querySelector('[name="estado"]').value = data.uf;
                form.querySelector('[name="numero"]').focus();

                // Nova lógica de verificação
                const coverageData = areaAbrangencia.find(area => 
                    area.city.toLowerCase() === data.localidade.toLowerCase() && 
                    area.state.toLowerCase() === data.uf.toLowerCase()
                );

                if (coverageData) { // Se encontrou a cidade na nossa lista
                    if (coverageData.allows_pickup == "1") {
                        document.getElementById('area-covered-pickup').classList.remove('hidden');
                    } else {
                        document.getElementById('area-covered-delivery').classList.remove('hidden');
                    }
                } else { // Se não encontrou a cidade
                    document.getElementById('area-notice').classList.remove('hidden');
                }
            }
        } catch (error) {
            console.error("Erro ao buscar CEP:", error);
        }
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!validateStep()) return;
        submitBtn.disabled = true;
        submitBtn.querySelector('.btn-text').classList.add('hidden');
        submitBtn.querySelector('.spinner').classList.remove('hidden');
        
        const formData = new FormData(form);
        formData.set('categoria', formData.get('dispositivo_final'));
        
        try {
            const response = await fetch('api/os_handler.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) {
                document.querySelector('.bg-white.p-8').innerHTML = document.getElementById('form-success').innerHTML;
                document.getElementById('os-number').textContent = result.os_id;
            } else { throw new Error(result.message || 'Erro desconhecido.'); }
        } catch (error) {
            document.getElementById('form-error').textContent = `Erro ao enviar solicitação: ${error.message}`;
            document.getElementById('form-error').classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitBtn.querySelector('.btn-text').classList.remove('hidden');
            submitBtn.querySelector('.spinner').classList.add('hidden');
        }
    });
});
</script>

<?php include 'templates/footer.php'; ?>