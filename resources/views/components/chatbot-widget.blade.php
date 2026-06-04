@php
    $chatbotFaqPreview = collect(config('chatbot.faqs', []))
        ->take(4)
        ->map(fn ($entry) => [
            'question' => (string) data_get($entry, 'question', ''),
            'answer' => (string) data_get($entry, 'answer', ''),
        ])
        ->filter(fn ($entry) => $entry['question'] !== '' && $entry['answer'] !== '')
        ->values();
@endphp

<style>
    .manake-chatbot-widget {
        --chat-bg: #0A0A0B;
        --chat-surface: #111113;
        --chat-surface-soft: #171719;
        --chat-border: #25252A;
        --chat-text: #F4F4F5;
        --chat-muted: #A1A1AA;
        --chat-accent: #D4A843;
        --chat-accent-text: #0A0A0B;
    }

    html[data-theme-resolved="light"] .manake-chatbot-widget {
        --chat-bg: #FFFFFF;
        --chat-surface: #FFFFFF;
        --chat-surface-soft: #F8FAFC;
        --chat-border: #E5E7EB;
        --chat-text: #111827;
        --chat-muted: #6B7280;
        --chat-accent: #2563EB;
        --chat-accent-text: #FFFFFF;
    }

    .chatbot-accent-bg {
        background-color: var(--chat-accent) !important;
        color: var(--chat-accent-text) !important;
    }

    .chatbot-accent-bg:hover {
        opacity: 0.9 !important;
    }

    .manake-chatbot-panel {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid var(--chat-border);
        background-color: var(--chat-bg);
        box-shadow: 0 20px 60px -15px rgba(0,0,0,0.5);
        position: fixed;
        z-index: 100;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        
        /* Mobile sizing */
        width: calc(100vw - 24px);
        left: 12px;
        right: 12px;
        bottom: 12px;
        max-height: calc(100dvh - 24px);
        border-radius: 22px;
    }

    @media (min-width: 640px) {
        .manake-chatbot-panel {
            width: 390px;
            left: auto;
            right: 24px;
            bottom: 24px;
            max-height: min(680px, calc(100dvh - 120px));
            border-radius: 28px;
        }
    }

    .chat-bubble-bot {
        max-width: 88%;
        border-radius: 18px;
        border-bottom-left-radius: 4px;
        background-color: var(--chat-surface-soft);
        border: 1px solid var(--chat-border);
        color: var(--chat-text);
        padding: 12px 15px;
        font-size: 14px;
        line-height: 1.45;
    }

    .chat-bubble-user {
        max-width: 85%;
        border-radius: 18px;
        border-bottom-right-radius: 4px;
        background-color: var(--chat-accent);
        color: var(--chat-accent-text);
        padding: 12px 15px;
        font-size: 14px;
        line-height: 1.45;
        font-weight: 500;
    }

    .chatbot-loading-dot {
        background-color: var(--chat-accent) !important;
    }
</style>

<div
    x-data="{
        isOpen: false,
        messages: [
            { role: 'assistant', content: 'Halo! Saya Manake Guide. Saya bisa bantu cek alat, jadwal, buffer sewa, dan cara pembayaran.' }
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
                    this.messages.push({ role: 'assistant', content: 'Maaf, Manake Guide sedang bermasalah. Coba lagi sebentar.' });
                }
            } catch (error) {
                this.messages.push({ role: 'assistant', content: 'Koneksi terputus. Cek internet lalu coba lagi.' });
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
            this.messages = [{ role: 'assistant', content: 'Halo! Saya Manake Guide. Saya bisa bantu cek alat, jadwal, buffer sewa, dan cara pembayaran.' }];
            this.$nextTick(() => this.scrollToBottom());
        },
 
        useFaq(question, answer) {
            this.messages.push({ role: 'user', content: question });
            this.messages.push({ role: 'assistant', content: answer });
            this.$nextTick(() => this.scrollToBottom());
        }
    }"
    class="manake-chatbot-widget fixed bottom-6 right-6 z-[100]"
>
    <!-- Floating Button (only visible when chat panel is closed) -->
    <button
        type="button"
        x-show="!isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        @click="isOpen = true"
        aria-label="Buka Manake Guide"
        data-chatbot-toggle
        class="group relative flex h-14 w-14 items-center justify-center rounded-full shadow-2xl transition duration-300 hover:-translate-y-1 hover:scale-105 active:scale-95 chatbot-accent-bg"
    >
        <span class="absolute inset-0 rounded-full opacity-0 blur-md transition duration-300 group-hover:opacity-100" style="background-color: var(--chat-accent); filter: blur(8px);"></span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
    </button>
 
    <!-- Chat Window Panel -->
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-8 scale-95"
        x-cloak
        class="manake-chatbot-panel"
        @keydown.escape.window="isOpen = false"
    >
        <!-- Header -->
        <div class="flex items-center justify-between border-b p-3.5" style="background: var(--chat-surface); border-color: var(--chat-border); color: var(--chat-text);">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl border shadow-inner font-bold" style="background: var(--chat-surface-soft); border-color: var(--chat-border); color: var(--chat-accent);">
                    <span class="text-lg">M</span>
                </div>
                <div>
                    <h3 class="text-base font-bold leading-tight" style="font-size: 17px; color: var(--chat-text);">Manake Guide</h3>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-xs" style="font-size: 13px; color: var(--chat-muted);">Online • Bantuan sewa alat</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button @click="resetChat()" class="flex h-[38px] w-[38px] items-center justify-center rounded-xl transition hover:opacity-80" style="color: var(--chat-muted);" aria-label="Reset chat" title="Reset Chat">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
                <button @click="isOpen = false" class="flex h-[38px] w-[38px] items-center justify-center rounded-xl transition hover:opacity-80" style="color: var(--chat-muted);" aria-label="Tutup chat" title="Tutup Chat">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
 
        <!-- Chat Container -->
        <div
            x-ref="chatContainer"
            role="log"
            aria-live="polite"
            class="flex-1 overflow-y-auto p-4 scrollbar-thin flex flex-col gap-3.5"
            style="background: var(--chat-bg);"
        >
            <!-- Quick Chips FAQ -->
            <template x-if="messages.length === 1 && faqPreview.length > 0">
                <div class="p-3.5 rounded-2xl border" style="background: var(--chat-surface-soft); border-color: var(--chat-border);">
                    <p class="text-xs font-bold uppercase tracking-[0.15em]" style="color: var(--chat-accent);">Pertanyaan cepat</p>
                    <p class="mt-1 text-[13px]" style="color: var(--chat-muted);">Pilih topik atau ketik pertanyaan sendiri.</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <template x-for="(faq, index) in faqPreview" :key="`faq-${index}`">
                            <button
                                type="button"
                                class="inline-flex items-center rounded-full border px-3.5 py-1.5 text-xs font-semibold transition hover:opacity-80"
                                style="background: var(--chat-surface); border-color: var(--chat-border); color: var(--chat-text); font-size: 13px;"
                                @click="useFaq(faq.question, faq.answer)"
                                x-text="faq.question"
                            ></button>
                        </template>
                    </div>
                </div>
            </template>
 
            <!-- Messages List -->
            <template x-for="(msg, index) in messages" :key="index">
                <div
                    class="flex"
                    :class="msg.role === 'user' ? 'justify-end' : 'justify-start'"
                >
                    <div
                        class="transition duration-200"
                        :class="msg.role === 'user' ? 'chat-bubble-user' : 'chat-bubble-bot'"
                    >
                        <p x-text="msg.content" class="leading-relaxed whitespace-pre-wrap"></p>
                    </div>
                </div>
            </template>
 
            <!-- Loading Indicator -->
            <div x-show="isLoading" class="flex flex-col gap-1.5" x-transition>
                <span class="text-[10px] font-medium tracking-wider pl-1.5 animate-pulse" style="color: var(--chat-accent);">Menyiapkan jawaban...</span>
                <div class="flex items-center gap-1.5 self-start rounded-[1.35rem] border px-5 py-3.5" style="background: var(--chat-surface-soft); border-color: var(--chat-border);">
                    <div class="chatbot-loading-dot h-2 w-2 animate-bounce rounded-full" style="animation-delay: -0.3s"></div>
                    <div class="chatbot-loading-dot h-2 w-2 animate-bounce rounded-full" style="animation-delay: -0.15s"></div>
                    <div class="chatbot-loading-dot h-2 w-2 animate-bounce rounded-full"></div>
                </div>
            </div>
        </div>
 
        <!-- Composer & Input -->
        <div class="p-3.5 border-t" style="background: var(--chat-surface); border-color: var(--chat-border);">
            <form @submit.prevent="sendMessage()" data-skip-loader="true" class="flex items-center gap-2">
                <input
                    type="text"
                    id="chatbot-search-input"
                    name="chatbot_query"
                    x-model="userInput"
                    autocomplete="off"
                    placeholder="Tanya seputar sewa alat..."
                    aria-label="Tanya seputar sewa alat..."
                    data-chatbot-input
                    class="w-full px-4 text-sm transition focus:outline-none"
                    style="
                        height: 48px;
                        background: var(--chat-surface-soft);
                        border: 1px solid var(--chat-border);
                        color: var(--chat-text);
                        border-radius: 16px;
                    "
                    :disabled="isLoading"
                >
                <button
                    type="submit"
                    aria-label="Kirim pesan"
                    data-chatbot-send
                    class="flex items-center justify-center shrink-0 shadow-sm transition hover:scale-105 disabled:opacity-50"
                    style="
                        height: 48px;
                        width: 48px;
                        background: var(--chat-accent);
                        color: var(--chat-accent-text);
                        border-radius: 16px;
                    "
                    :disabled="isLoading || !userInput.trim()"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </form>
            <p class="mt-2 text-center text-xs" style="font-size: 11px; color: var(--chat-muted);">Manake Guide membantu soal katalog, jadwal, dan cara sewa.</p>
        </div>
    </div>
</div>
