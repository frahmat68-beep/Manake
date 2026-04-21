<div
    x-data="{
        isOpen: false,
        messages: [
            { role: 'assistant', content: 'Halo! Saya Manake Guide. Ada yang bisa saya bantu terkait sewa alat hari ini?' }
        ],
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
            if (confirm('Hapus riwayat chat?')) {
                fetch('{{ route('chatbot.reset') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                this.messages = [{ role: 'assistant', content: 'Halo! Saya Manake Guide. Ada yang bisa saya bantu?' }];
            }
        }
    }"
    class="fixed bottom-6 right-6 z-[100]"
>
    <!-- Floating Button -->
    <button
        @click="isOpen = !isOpen"
        class="group relative flex h-14 w-14 items-center justify-center rounded-full bg-blue-600 text-white shadow-2xl transition hover:scale-110 active:scale-95"
        :class="isOpen ? 'bg-slate-800 rotate-90' : ''"
    >
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
        class="absolute bottom-16 right-0 flex w-[350px] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl backdrop-blur-xl sm:w-[400px]"
        style="height: 500px;"
    >
        <!-- Header -->
        <div class="flex items-center justify-between bg-blue-600 p-4 text-white">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-md">
                    <span class="text-lg font-bold">M</span>
                </div>
                <div>
                    <h3 class="text-sm font-bold">Manake Guide</h3>
                    <div class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-green-400 animate-pulse"></span>
                        <span class="text-[10px] text-blue-100">AI Intelligent Assistant</span>
                    </div>
                </div>
            </div>
            <button @click="resetChat()" class="rounded-lg p-1 hover:bg-white/10" title="Reset Chat">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>

        <!-- Chat Container -->
        <div
            x-ref="chatContainer"
            class="flex-1 overflow-y-auto bg-slate-50 p-4 space-y-4"
        >
            <template x-for="(msg, index) in messages" :key="index">
                <div
                    class="flex"
                    :class="msg.role === 'user' ? 'justify-end' : 'justify-start'"
                >
                    <div
                        class="max-w-[80%] rounded-2xl px-4 py-2.5 text-sm shadow-sm"
                        :class="msg.role === 'user' 
                            ? 'bg-blue-600 text-white rounded-tr-none' 
                            : 'bg-white text-slate-700 border border-slate-100 rounded-tl-none'"
                    >
                        <p x-text="msg.content" class="leading-relaxed whitespace-pre-wrap"></p>
                    </div>
                </div>
            </template>

            <!-- Loading Indicator -->
            <div x-show="isLoading" class="flex justify-start" x-transition>
                <div class="flex items-center gap-1 rounded-2xl bg-white border border-slate-100 px-4 py-3 shadow-sm">
                    <div class="h-1.5 w-1.5 animate-bounce rounded-full bg-blue-600"></div>
                    <div class="h-1.5 w-1.5 animate-bounce rounded-full bg-blue-600 [animation-delay:0.2s]"></div>
                    <div class="h-1.5 w-1.5 animate-bounce rounded-full bg-blue-600 [animation-delay:0.4s]"></div>
                </div>
            </div>
        </div>

        <!-- Input -->
        <div class="border-t border-slate-100 bg-white p-4">
            <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                <input
                    type="text"
                    x-model="userInput"
                    placeholder="Tanya seputar sewa alat..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    :disabled="isLoading"
                >
                <button
                    type="submit"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white transition hover:bg-blue-700 disabled:opacity-50"
                    :disabled="isLoading || !userInput.trim()"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </form>
            <p class="mt-2 text-center text-[10px] text-slate-400">Dimotori oleh NVIDIA AI • Manake Studio</p>
        </div>
    </div>
</div>
