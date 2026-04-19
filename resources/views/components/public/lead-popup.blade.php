{{-- Chatbot calificador de leads — bottom-right --}}
<div x-data="leadChatbot()" x-cloak class="fixed bottom-6 right-6 z-50">

    {{-- Chat window --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="mb-3 w-80 max-w-[calc(100vw-3rem)] bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden flex flex-col"
         style="max-height: min(480px, calc(100vh - 8rem));">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-[var(--color-primary)] to-[var(--color-secondary)] px-4 py-3 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2.5 text-white">
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                    <x-icon name="bot" class="w-4 h-4" />
                </div>
                <div>
                    <p class="text-sm font-semibold leading-tight">Asistente HDV</p>
                    <p class="text-[10px] text-white/70 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span> En linea
                    </p>
                </div>
            </div>
            <button @click="open = false" class="text-white/70 hover:text-white transition-colors p-1">
                <x-icon name="x" class="w-4 h-4" />
            </button>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-3" x-ref="messages">
            <template x-for="(msg, i) in messages" :key="i">
                <div>
                    {{-- Bot message --}}
                    <template x-if="msg.from === 'bot'">
                        <div class="flex items-start gap-2">
                            <div class="w-6 h-6 rounded-full bg-[var(--color-primary)]/10 flex items-center justify-center shrink-0 mt-0.5">
                                <x-icon name="bot" class="w-3 h-3 text-[var(--color-primary)]" />
                            </div>
                            <div class="bg-gray-100 rounded-2xl rounded-tl-sm px-3 py-2 text-sm text-gray-700 leading-relaxed max-w-[85%]"
                                 x-html="msg.text"></div>
                        </div>
                    </template>
                    {{-- User message --}}
                    <template x-if="msg.from === 'user'">
                        <div class="flex justify-end">
                            <div class="bg-[var(--color-primary)] text-white rounded-2xl rounded-tr-sm px-3 py-2 text-sm leading-relaxed max-w-[85%]"
                                 x-text="msg.text"></div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div x-show="typing" class="flex items-start gap-2">
                <div class="w-6 h-6 rounded-full bg-[var(--color-primary)]/10 flex items-center justify-center shrink-0">
                    <x-icon name="bot" class="w-3 h-3 text-[var(--color-primary)]" />
                </div>
                <div class="bg-gray-100 rounded-2xl rounded-tl-sm px-3 py-2.5 flex gap-1">
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                </div>
            </div>
        </div>

        {{-- Options / Input --}}
        <div class="border-t border-gray-100 p-3 shrink-0">
            {{-- Quick reply buttons --}}
            <template x-if="step === 'qualify'">
                <div class="space-y-2">
                    <button @click="selectType('vendedor')" class="w-full flex items-center gap-2.5 rounded-xl border border-gray-200 px-3 py-2.5 text-sm text-left hover:border-[var(--color-primary)] hover:bg-[var(--color-primary)]/5 transition-colors">
                        <x-icon name="home" class="w-4 h-4 text-[var(--color-primary)] shrink-0" />
                        <span>Quiero vender mi propiedad</span>
                    </button>
                    <button @click="selectType('comprador')" class="w-full flex items-center gap-2.5 rounded-xl border border-gray-200 px-3 py-2.5 text-sm text-left hover:border-[var(--color-primary)] hover:bg-[var(--color-primary)]/5 transition-colors">
                        <x-icon name="search" class="w-4 h-4 text-[var(--color-primary)] shrink-0" />
                        <span>Busco comprar o rentar</span>
                    </button>
                    <button @click="selectType('desarrollador')" class="w-full flex items-center gap-2.5 rounded-xl border border-gray-200 px-3 py-2.5 text-sm text-left hover:border-[var(--color-primary)] hover:bg-[var(--color-primary)]/5 transition-colors">
                        <x-icon name="landmark" class="w-4 h-4 text-[var(--color-primary)] shrink-0" />
                        <span>Soy desarrollador inmobiliario</span>
                    </button>
                </div>
            </template>

            {{-- Email input --}}
            <template x-if="step === 'email'">
                <form @submit.prevent="submitEmail()" class="flex gap-2">
                    <input type="email" x-model="email" required placeholder="tu@email.com"
                           class="flex-1 min-w-0 rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]/30 focus:border-[var(--color-primary)] transition-colors"
                           x-ref="emailInput">
                    <button type="submit" :disabled="loading"
                            class="shrink-0 w-10 h-10 rounded-xl bg-[var(--color-primary)] text-white flex items-center justify-center hover:opacity-90 active:scale-95 transition-all disabled:opacity-60">
                        <x-icon name="send" class="w-4 h-4" />
                    </button>
                </form>
            </template>

            {{-- Final CTA --}}
            <template x-if="step === 'done'">
                <div class="space-y-2">
                    <a :href="ctaUrl" class="w-full flex items-center justify-center gap-2 rounded-xl bg-[var(--color-primary)] text-white py-2.5 text-sm font-semibold hover:opacity-90 active:scale-[0.98] transition-all">
                        <x-icon name="arrow-right" class="w-4 h-4" />
                        <span x-text="ctaText"></span>
                    </a>
                    @if($siteSettings?->whatsapp_number)
                    @php $phone = preg_replace('/[^0-9]/', '', $siteSettings->whatsapp_number); @endphp
                    <a href="https://wa.me/{{ $phone }}?text={{ urlencode('Hola, me interesa información sobre sus propiedades.') }}"
                       target="_blank" rel="noopener noreferrer"
                       class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#25D366] text-white py-2.5 text-sm font-semibold hover:bg-[#1ebe57] active:scale-[0.98] transition-all">
                        <x-icon name="brands/whatsapp" class="w-4 h-4" />
                        O escríbenos por WhatsApp
                    </a>
                    @endif
                </div>
            </template>
        </div>
    </div>

    {{-- Toggle button --}}
    <button @click="toggle()" class="relative group">
        {{-- Notification dot --}}
        <span x-show="!opened && !open" class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full border-2 border-white text-[8px] text-white font-bold flex items-center justify-center z-10">1</span>

        <div class="w-14 h-14 rounded-full shadow-lg flex items-center justify-center transition-all duration-300 group-hover:scale-110 group-hover:shadow-xl"
             :class="open ? 'bg-gray-700' : 'bg-[var(--color-primary)]'">
            <span x-show="!open"><x-icon name="bot" class="w-7 h-7 text-white" /></span>
            <span x-show="open" x-cloak><x-icon name="x" class="w-6 h-6 text-white" /></span>
        </div>
    </button>
</div>

<script>
function leadChatbot() {
    return {
        open: false,
        opened: false,
        typing: false,
        step: 'init',
        leadType: '',
        email: '',
        loading: false,
        messages: [],
        ctaUrl: '',
        ctaText: '',

        init() {
            // Auto-open hint after 25s if not interacted
            if (!localStorage.getItem('hdv_chat_done')) {
                setTimeout(() => {
                    if (!this.opened) this.open = true;
                    this.startChat();
                }, 25000);
            }
        },

        toggle() {
            this.open = !this.open;
            if (this.open && !this.opened) {
                this.startChat();
            }
        },

        startChat() {
            if (this.opened) return;
            this.opened = true;
            this.botSay('¡Hola! 👋 Soy el asistente de <strong>Home del Valle</strong>. ¿En qué te puedo ayudar?', () => {
                this.step = 'qualify';
            });
        },

        selectType(type) {
            this.leadType = type;
            const labels = {
                vendedor: 'Quiero vender mi propiedad',
                comprador: 'Busco comprar o rentar',
                desarrollador: 'Soy desarrollador inmobiliario',
            };
            this.userSay(labels[type]);

            const responses = {
                vendedor: '¡Excelente! Podemos darte una <strong>valuación gratuita</strong> de tu propiedad. Déjame tu email para contactarte.',
                comprador: '¡Perfecto! Te puedo enviar propiedades nuevas <strong>antes que nadie</strong>. ¿Me dejas tu email?',
                desarrollador: 'Trabajamos con desarrolladores en <strong>proyectos exclusivos</strong>. Déjame tu email para coordinar.',
            };
            this.step = null;
            this.botSay(responses[type], () => {
                this.step = 'email';
                this.$nextTick(() => {
                    if (this.$refs.emailInput) this.$refs.emailInput.focus();
                });
            });
        },

        async submitEmail() {
            if (!this.email || this.loading) return;
            this.loading = true;
            this.userSay(this.email);

            try {
                await fetch('{{ route("newsletter.subscribe") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({
                        email: this.email,
                        source: 'chatbot_' + this.leadType,
                        website_url: '',
                    }),
                });
            } catch(e) {}

            this.loading = false;
            this.step = null;
            localStorage.setItem('hdv_chat_done', '1');

            const ctas = {
                vendedor: { url: '{{ route("landing.vende") }}', text: 'Solicitar valuación gratuita' },
                comprador: { url: '{{ route("propiedades.index") }}', text: 'Ver propiedades disponibles' },
                desarrollador: { url: '{{ route("contacto") }}', text: 'Contactar al equipo' },
            };
            this.ctaUrl = ctas[this.leadType].url;
            this.ctaText = ctas[this.leadType].text;

            this.botSay('¡Gracias! Te contactaremos pronto. Mientras tanto:', () => {
                this.step = 'done';
            });
        },

        botSay(text, cb) {
            this.typing = true;
            this.scrollDown();
            setTimeout(() => {
                this.typing = false;
                this.messages.push({ from: 'bot', text });
                this.scrollDown();
                if (cb) cb();
            }, 800 + Math.random() * 400);
        },

        userSay(text) {
            this.messages.push({ from: 'user', text });
            this.scrollDown();
        },

        scrollDown() {
            this.$nextTick(() => {
                if (this.$refs.messages) {
                    this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
                }
            });
        },
    };
}
</script>
