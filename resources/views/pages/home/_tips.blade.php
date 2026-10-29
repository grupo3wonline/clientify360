@php
    $tips = [
        ['fa-tags', 'Use tags desde o primeiro cliente', 'Marque clientes como "VIP", "Indicação" ou "Orçamento enviado". Depois é só filtrar a lista.'],
        ['fa-bell', 'Todo contato termina com um lembrete', 'Depois de cada atendimento, agende o próximo passo. A central de notificações avisa os atrasados.'],
        ['fa-ranking-star', 'Priorize pelo score', 'Classifique os clientes como Quente, Morno, Frio ou VIP e comece o dia pelos mais quentes.'],
        ['fa-magnet', 'Coloque o formulário no seu site', 'Compartilhe o link do formulário de captação no Instagram, no WhatsApp ou no seu site.'],
        ['fa-file-invoice-dollar', 'Duplique orçamentos', 'Para serviços parecidos, duplique um orçamento pronto e ajuste só o que mudou.'],
        ['fa-moon', 'Escolha o tema', 'Use o tema escuro à noite ou em ambientes com pouca luz. A escolha fica salva no navegador.'],
    ];
@endphp
<section id="dicas" class="c-section bg-surface-50 dark:bg-surface-900" aria-labelledby="dicas-title">
    <div class="c-container">
        <div class="max-w-2xl">
            <p class="c-eyebrow"><i class="fa-solid fa-lightbulb" aria-hidden="true"></i> Dicas</p>
            <h2 id="dicas-title" class="c-h2 mt-3">Dicas para tirar o máximo do Clientify360</h2>
            <p class="c-lead mt-4">Pequenos hábitos que fazem diferença nas vendas do mês.</p>
        </div>
        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($tips as [$icon, $title, $text])
                <article class="flex gap-4 rounded-card border border-surface-200 dark:border-surface-800 bg-white dark:bg-surface-950 p-5">
                    <span class="c-icon-tile bg-warning-50 text-warning-700 dark:bg-warning-950/40 dark:text-warning-400"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                    <div>
                        <h3 class="font-semibold text-surface-900 dark:text-white">{{ $title }}</h3>
                        <p class="mt-1 text-sm text-surface-600 dark:text-surface-400 leading-relaxed">{{ $text }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
