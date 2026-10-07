@props(['course'])

{{--
    Floating chat widget scoped to a single course. Drop this into your
    student-facing course/lesson view, e.g.:

        <x-course-chatbot :course="$course" />

    It only answers using that course's content (see App\Services\CourseChatbot).
--}}
<div x-data="{
        open: false,
        loading: false,
        messages: [],
        question: '',
        courseId: {{ $course->id }},
        async loadHistory() {
            try {
                const res = await fetch(`/courses/${this.courseId}/chatbot/history`, { headers: { 'Accept': 'application/json' } });
                if (res.ok) { this.messages = await res.json(); }
            } catch (e) {}
        },
        async send() {
            if (!this.question.trim() || this.loading) return;
            const q = this.question.trim();
            this.question = '';
            this.messages.push({ question: q, answer: null });
            this.loading = true;
            try {
                const res = await fetch(`/courses/${this.courseId}/chatbot/ask`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                    },
                    body: JSON.stringify({ question: q }),
                });
                const data = await res.json();
                this.messages[this.messages.length - 1].answer = res.ok
                    ? data.answer
                    : (data.error || 'Something went wrong — please try again.');
            } catch (e) {
                this.messages[this.messages.length - 1].answer = 'Could not reach the assistant — check your connection and try again.';
            } finally {
                this.loading = false;
                this.$nextTick(() => { const el = this.$refs.scrollArea; if (el) el.scrollTop = el.scrollHeight; });
            }
        }
     }"
     x-init="loadHistory()"
     class="fixed bottom-6 right-6 z-50"
     style="font-family: 'Public Sans', sans-serif;">

    <button @click="open = !open"
            class="rounded-full w-14 h-14 flex items-center justify-center shadow-lg transition-transform hover:scale-105"
            style="background:#0B6E6E; color:#fff;"
            aria-label="Open course assistant">
        <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-6l-4 4v-4z" />
        </svg>
        <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <div x-show="open" x-transition
         class="absolute bottom-20 right-0 w-80 sm:w-96 rounded-2xl shadow-2xl border flex flex-col overflow-hidden"
         style="background:#fff; border-color:#D5DCE1; max-height: 70vh;">
        <div class="px-4 py-3" style="background:#0B6E6E; color:#fff;">
            <div class="font-semibold text-sm">Course Assistant</div>
            <div class="text-xs opacity-80">Answers based on this course's content only</div>
        </div>

        <div x-ref="scrollArea" class="flex-1 overflow-y-auto p-4 space-y-3" style="min-height: 240px;">
            <template x-if="messages.length === 0">
                <p class="text-sm" style="color:#5B6B75;">Ask a question about this course's lessons and I'll do my best to help.</p>
            </template>

            <template x-for="(m, i) in messages" :key="i">
                <div class="space-y-1">
                    <div class="text-sm px-3 py-2 rounded-lg self-end" style="background:#DCEFEC; color:#10222B;" x-text="m.question"></div>
                    <div class="text-sm px-3 py-2 rounded-lg" style="background:#F3F5F7; color:#10222B;">
                        <template x-if="m.answer === null">
                            <span style="color:#5B6B75;">Thinking…</span>
                        </template>
                        <template x-if="m.answer !== null">
                            <span x-text="m.answer"></span>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <form @submit.prevent="send" class="p-3 border-t flex gap-2" style="border-color:#D5DCE1;">
            <input type="text" x-model="question" :disabled="loading"
                   placeholder="Ask about this course..."
                   class="flex-1 text-sm rounded-lg border px-3 py-2 focus:outline-none"
                   style="border-color:#D5DCE1;">
            <button type="submit" :disabled="loading"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-white disabled:opacity-50"
                    style="background:#0B6E6E;">
                Send
            </button>
        </form>
    </div>
</div>
