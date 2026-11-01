<section id="faq" class="c-section bg-white dark:bg-surface-950" aria-labelledby="faq-title">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="c-eyebrow justify-center"><i class="fa-solid fa-circle-question" aria-hidden="true"></i> Dúvidas Frequentes</p>
            <h2 id="faq-title" class="c-h2 mt-3">Perguntas frequentes</h2>
            <p class="c-lead mt-4">Tudo o que você precisa saber antes de começar.</p>
        </div>

        <div class="mt-10 space-y-3" x-data="{ open: 0 }">
            @foreach($faqs as $index => $faq)
                <div class="c-card overflow-hidden">
                    <h3>
                        <button type="button" id="faq-q-{{ $index }}" aria-controls="faq-a-{{ $index }}"
                                @click="open = open === {{ $index }} ? null : {{ $index }}"
                                :aria-expanded="(open === {{ $index }}).toString()" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                class="w-full flex items-center justify-between gap-4 px-5 sm:px-6 py-5 text-left font-semibold text-surface-900 dark:text-white">
                            <span>{{ $faq['question'] }}</span>
                            <i class="fa-solid fa-chevron-down text-brand-500 transition-transform shrink-0" :class="{ 'rotate-180': open === {{ $index }} }" aria-hidden="true"></i>
                        </button>
                    </h3>
                    <div id="faq-a-{{ $index }}" role="region" aria-labelledby="faq-q-{{ $index }}" x-show="open === {{ $index }}" x-collapse @if($index !== 0) x-cloak @endif>
                        <p class="px-5 sm:px-6 pb-5 text-sm text-surface-600 dark:text-surface-400 leading-relaxed">{{ $faq['answer'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
