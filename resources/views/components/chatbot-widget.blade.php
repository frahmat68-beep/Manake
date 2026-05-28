@php
    $chatbotWelcomeMessage = config('chatbot.welcome_message');
    $chatbotFaqPreview = collect(config('chatbot.faqs', []))
        ->take(4)
        ->map(fn ($entry) => [
            'question' => (string) data_get($entry, 'question', ''),
            'answer' => (string) data_get($entry, 'answer', ''),
        ])
        ->filter(fn ($entry) => $entry['question'] !== '' && $entry['answer'] !== '')
        ->values();
@endphp

<div
    x-data="{
        isOpen: false,
        messages: [
            { role: 'assistant', content: @js($chatbotWelcomeMessage) }
        ],
        faqPreview: @js($chatbotFaqPreview),
        userInput: '',
        isLoading: false,
        
        async sendMessage() {
            if (!this.userInput.trim() || this.isLoading) return;
            
            const message = this.userInput;
            this.messages.push({ role: 'user', content: message });
            this.userInput = '';
            this.isLoading = true;
            
            this.$nextTick(() => this.scrollToBottom());

            try {
                const response = await fetch('{{ route('chatbot.message') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message: message })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.messages.push({ role: 'assistant', content: data.message });
                } else {
                    this.messages.push({ role: 'assistant', content: 'Maaf, sepertinya ada masalah teknis. Coba lagi nanti ya!' });
                }
            } catch (error) {
                this.messages.push({ role: 'assistant', content: 'Koneksi terputus. Pastikan internetmu stabil.' });
            } finally {
                this.isLoading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },
        
        scrollToBottom() {
            const container = this.$refs.chatContainer;
            container.scrollTop = container.scrollHeight;
        },
        
        resetChat() {
            fetch('{{ route('chatbot.reset') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
            this.messages = [{ role: 'assistant', content: @js($chatbotWelcomeMessage) }];
            this.$nextTick(() => this.scrollToBottom());
        },

        useFaq(question, answer) {
            this.messages.push({ role: 'user', content: question });
            this.messages.push({ role: 'assistant', content: answer });
            this.$nextTick(() => this.scrollToBottom());
        }
    }"
    class="fixed bottom-6 right-6 z-[100]"
>
    <!-- Floating Button -->
    <button
        type="button"
        @click="isOpen = !isOpen"
        :aria-label="isOpen ? 'Tutup Manake Guide' : 'Buka Manake Guide'"
        data-chatbot-toggle
        class="group relative flex h-14 w-14 items-center justify-center rounded-full shadow-2xl transition duration-300 hover:-translate-y-1 hover:scale-105 active:scale-95"
        :class="isOpen ? 'rotate-90 bg-[#111113] text-[#E8E8EC] border border-[#1A1A1E]' : 'bg-[#D4A843] text-[#0A0A0B]'"
    >
        <span class="absolute inset-0 rounded-full bg-[#D4A843]/20 opacity-0 blur-md transition duration-300 group-hover:opacity-100"></span>
        <div x-show="!isOpen" x-transition>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
        </div>
        <div x-show="isOpen" x-transition x-cloak>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>
    </button>

    <!-- Chat Window -->
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-10 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-10 scale-95"
        x-cloak
        class="absolute bottom-16 right-0 flex h-[540px] w-[360px] flex-col overflow-hidden rounded-[2rem] border border-[#1A1A1E] bg-[#0A0A0B]/95 shadow-[0_30px_80px_-35px_rgba(0,0,0,0.8)] backdrop-blur-xl sm:w-[410px]"
    >
        <!-- Header -->
        <div class="flex items-center justify-between bg-[#111113] border-b border-[#1A1A1E] p-4 text-[#E8E8EC]">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#D4A843]/10 border border-[#D4A843]/20 text-[#D4A843] shadow-inner backdrop-blur-md">
                    <span class="text-lg font-bold">M</span>
                </div>
                <div>
                    <h3 class="text-sm font-bold tracking-[0.01em]">Manake Guide</h3>
                    <div class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-[10px] text-[#A0A0A8]">Rental copilot + FAQ live help</span>
                    </div>
                </div>
            </div>
            <button @click="resetChat()" class="rounded-xl p-2 transition hover:bg-[#1A1A1E] text-[#A0A0A8] hover:text-[#E8E8EC]" title="Reset Chat">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>

        <!-- Chat Container -->
        <div
            x-ref="chatContainer"
            class="flex-1 space-y-4 overflow-y-auto bg-[#0A0A0B] p-4"
        >
            <template x-if="messages.length === 1 && faqPreview.length > 0">
                <div class="rounded-[1.5rem] border border-[#1A1A1E] bg-[#111113] p-3 shadow-sm backdrop-blur">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#D4A843]">FAQ cepat</p>
                    <p class="mt-1 text-xs leading-relaxed text-[#A0A0A8]">Pilih pertanyaan umum untuk balasan instan, atau ketik pertanyaan Anda sendiri.</p>
                    <div class="mt-3 grid gap-2">
                        <template x-for="(faq, index) in faqPreview" :key="`faq-${index}`">
                            <button
                                type="button"
                                class="w-full rounded-2xl border border-[#1A1A1E] bg-[#0A0A0B] px-3 py-2.5 text-left text-xs font-medium text-[#E8E8EC] shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-[#D4A843]/40 hover:text-[#D4A843] hover:shadow-md"
                                @click="useFaq(faq.question, faq.answer)"
                                x-text="faq.question"
                            ></button>
                        </template>
                    </div>
                </div>
            </template>

            <template x-for="(msg, index) in messages" :key="index">
                <div
                    class="flex"
                    :class="msg.role === 'user' ? 'justify-end' : 'justify-start'"
                >
                    <div
                        class="max-w-[82%] rounded-[1.35rem] px-4 py-3 text-sm shadow-sm transition duration-200"
                        :class="msg.role === 'user' 
                            ? 'bg-[#D4A843] text-[#0A0A0B] font-medium rounded-tr-md shadow-[0_16px_30px_-22px_rgba(212,168,67,0.3)]' 
                            : 'border border-[#1A1A1E] bg-[#111113] text-[#E8E8EC] rounded-tl-md shadow-[0_18px_32px_-26px_rgba(0,0,0,0.5)]'"
                    >
                        <p x-text="msg.content" class="leading-relaxed whitespace-pre-wrap"></p>
                    </div>
                </div>
            </template>

            <!-- Loading Indicator -->
            <div x-show="isLoading" class="flex flex-col gap-1.5" x-transition>
                <span class="text-[10px] font-medium tracking-wider text-[#D4A843]/80 pl-1.5 animate-pulse">Manake Guide sedang memikirkan jawaban...</span>
                <div class="flex items-center gap-1.5 self-start rounded-[1.35rem] border border-[#D4A843]/20 bg-[#111113] px-5 py-3.5 shadow-[0_10px_25px_-10px_rgba(0,0,0,0.5)]">
                    <div class="h-2 w-2 animate-bounce rounded-full bg-[#D4A843]" style="animation-delay: -0.3s"></div>
                    <div class="h-2 w-2 animate-bounce rounded-full bg-[#D4A843]" style="animation-delay: -0.15s"></div>
                    <div class="h-2 w-2 animate-bounce rounded-full bg-[#D4A843]"></div>
                </div>
            </div>
        </div>

        <!-- Input -->
        <div class="border-t border-[#1A1A1E] bg-[#0A0A0B]/95 p-4 backdrop-blur">
            <form @submit.prevent="sendMessage()" data-skip-loader="true" class="flex items-center gap-2">
                <input
                    type="text"
                    id="chatbot-search-input"
                    name="chatbot_query"
                    x-model="userInput"
                    placeholder="Tanya seputar sewa alat..."
                    aria-label="Tanya seputar sewa alat..."
                    data-chatbot-input
                    class="w-full rounded-2xl border border-[#1A1A1E] bg-[#111113] px-4 py-2.5 text-sm text-[#E8E8EC] transition focus:border-[#D4A843] focus:outline-none focus:ring-2 focus:ring-[#D4A843]/20 placeholder:text-slate-600"
                    :disabled="isLoading"
                >
                <button
                    type="submit"
                    aria-label="Kirim pesan"
                    data-chatbot-send
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[#D4A843] text-[#0A0A0B] shadow-sm transition hover:scale-105 hover:bg-[#e0ba5d] hover:shadow-md disabled:opacity-50"
                    :disabled="isLoading || !userInput.trim()"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </form>
            <p class="mt-2 text-center text-[10px] text-[#A0A0A8]">Manake Guide • FAQ + AI fallback + catalog-aware help</p>
        </div>
    </div>
</div>
