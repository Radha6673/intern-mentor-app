<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    tasks: Array,
});

// Currently selected task for modal submission
const selectedTask = ref(null);

// Inertia Submission Form State
const form = useForm({
    explanation: '',
    tech_stack: '',
    github_link: '',
});

// AI State & Helpers
const isPolishingExplanation = ref(false);
const aiGuideModalTask = ref(null);
const aiGuideData = ref(null);
const isLoadingAiGuide = ref(false);

const polishExplanation = async () => {
    if (!form.explanation.trim() || isPolishingExplanation.value) return;
    isPolishingExplanation.value = true;
    try {
        const response = await axios.post(route('ai.polish'), {
            text: form.explanation,
            context: 'submission'
        });
        if (response.data && response.data.polished_text) {
            form.explanation = response.data.polished_text;
        }
    } catch (err) {
        console.error('Error polishing submission explanation:', err);
    } finally {
        isPolishingExplanation.value = false;
    }
};

const openAiGuideModal = async (task) => {
    aiGuideModalTask.value = task;
    aiGuideData.value = null;
    isLoadingAiGuide.value = true;
    try {
        const response = await axios.post(route('ai.task_assistant'), {
            title: task.title,
            description: task.description,
            department: task.intern?.department || null,
        });
        if (response.data && response.data.data) {
            aiGuideData.value = response.data.data;
        }
    } catch (err) {
        console.error('Error loading AI task guide:', err);
    } finally {
        isLoadingAiGuide.value = false;
    }
};

const closeAiGuideModal = () => {
    aiGuideModalTask.value = null;
    aiGuideData.value = null;
};

// Modal Open handler
const openSubmissionModal = (task) => {
    selectedTask.value = task;
    form.explanation = task.submission ? task.submission.explanation : '';
    form.tech_stack = task.submission ? task.submission.tech_stack : '';
    form.github_link = task.submission ? task.submission.github_link : '';
};

// Modal Close handler
const closeModal = () => {
    selectedTask.value = null;
    form.reset();
};

// Form Submit Handler
const submitSolution = () => {
    form.post(route('intern.tasks.submit', selectedTask.value.id), {
        onSuccess: () => {
            closeModal();
            alert('Task Solution Submitted Successfully!');
        },
    });
};
</script>

<template>
    <Head title="My Tasks" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('dashboard')" class="text-xs font-bold text-gray-500 hover:text-indigo-600 transition border border-gray-300 rounded-md px-3 py-1.5 bg-white shadow-sm">
                    &larr; Back to Dashboard
                </Link>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">
                    Intern Portal - My Assigned Tasks
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Tasks Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div 
                        v-for="task in tasks" 
                        :key="task.id" 
                        class="p-6 bg-white shadow sm:rounded-lg border-l-4 space-y-3 flex flex-col justify-between"
                        :class="{
                            'border-yellow-400': task.status === 'pending',
                            'border-blue-500': task.status === 'in_progress' || task.status === 'progress',
                            'border-purple-500': task.status === 'submitted',
                            'border-green-500': task.status === 'approved',
                            'border-red-500': task.status === 'reject' || task.status === 'rejected',
                        }"
                    >
                        <div>
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-lg font-bold text-gray-900">{{ task.title }}</h3>
                                <span 
                                    class="px-2 py-1 text-xs font-semibold rounded-full"
                                    :class="{
                                        'bg-yellow-100 text-yellow-800': task.status === 'pending',
                                        'bg-purple-100 text-purple-800': task.status === 'submitted',
                                        'bg-green-100 text-green-800': task.status === 'approved',
                                        'bg-red-100 text-red-800': task.status === 'reject' || task.status === 'rejected',
                                    }"
                                >
                                    {{ (task.status === 'reject' ? 'REJECTED' : task.status).replace('_', ' ').toUpperCase() }}
                                </span>
                            </div>

                            <p class="text-xs text-gray-500 mb-3">
                                Assigned By: <span class="font-medium text-gray-700">{{ task.mentor.name }}</span>
                            </p>

                            <p class="text-sm text-gray-600 mb-4 whitespace-pre-line">{{ task.description }}</p>

                            <div class="text-xs text-gray-500 mb-3">
                                <strong>Deadline:</strong> {{ task.deadline ? new Date(task.deadline).toLocaleString() : 'No Deadline' }}
                            </div>

                            <!-- Mentor Feedback Display -->
                            <div v-if="task.submission && task.submission.feedback" class="p-3 bg-amber-50 border border-amber-200 rounded text-xs text-amber-900 mb-3">
                                <strong>Mentor Feedback:</strong>
                                <p class="mt-1 whitespace-pre-line text-amber-800">{{ task.submission.feedback }}</p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-3 border-t mt-auto flex flex-wrap items-center justify-between gap-2">
                            <button 
                                @click="openSubmissionModal(task)"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2 px-4 rounded-lg shadow transition"
                            >
                                {{ task.submission ? 'Edit Submission' : 'Submit Task Solution' }}
                            </button>

                            <button 
                                @click="openAiGuideModal(task)"
                                class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white text-xs font-bold py-2 px-3.5 rounded-lg shadow transition flex items-center gap-1.5"
                                title="Get AI Summary & Step-by-step guidance"
                            >
                                <span>✨</span>
                                <span>AI Task Guide & Steps</span>
                            </button>
                        </div>
                    </div>

                    <div v-if="tasks.length === 0" class="col-span-2 p-6 bg-white text-center text-gray-500 shadow sm:rounded-lg">
                        No tasks assigned to you yet!
                    </div>
                </div>

                <!-- SUBMISSION MODAL -->
                <div v-if="selectedTask" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
                    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full p-6 border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">
                            Submit Solution: {{ selectedTask.title }}
                        </h3>
                        <p class="text-xs text-gray-500 mb-4">Provide details about how you completed this task.</p>

                        <form @submit.prevent="submitSolution" class="space-y-4">
                            <!-- Explanation -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-sm font-semibold text-gray-700">How did you solve it? (Explanation)</label>
                                    <button 
                                        type="button" 
                                        @click="polishExplanation" 
                                        :disabled="isPolishingExplanation || !form.explanation.trim()" 
                                        class="px-2.5 py-1 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 text-xs font-bold rounded-lg transition disabled:opacity-40 flex items-center gap-1"
                                        title="Format explanation professionally with AI"
                                    >
                                        <span v-if="isPolishingExplanation" class="animate-spin">🌀</span>
                                        <span v-else>✨</span>
                                        <span>AI Polish Explanation</span>
                                    </button>
                                </div>
                                <textarea 
                                    v-model="form.explanation" 
                                    rows="4" 
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    placeholder="Explain your approach, logic used, and step-by-step process..."
                                    required
                                ></textarea>
                                <p v-if="form.errors.explanation" class="text-red-500 text-xs mt-1">{{ form.errors.explanation }}</p>
                            </div>

                            <!-- Tech Stack Used -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tech Stack & Tools Used</label>
                                <input 
                                    type="text" 
                                    v-model="form.tech_stack" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="e.g. Laravel 11, Vue 3, Tailwind CSS, MySQL"
                                    required
                                />
                                <p v-if="form.errors.tech_stack" class="text-red-500 text-xs mt-1">{{ form.errors.tech_stack }}</p>
                            </div>

                            <!-- GitHub / Project Link -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">GitHub Repository / Live Demo URL</label>
                                <input 
                                    type="url" 
                                    v-model="form.github_link" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="https://github.com/username/project"
                                />
                                <p v-if="form.errors.github_link" class="text-red-500 text-xs mt-1">{{ form.errors.github_link }}</p>
                            </div>

                            <!-- Modal Buttons -->
                            <div class="flex justify-end gap-3 pt-4 border-t">
                                <button 
                                    type="button" 
                                    @click="closeModal"
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-semibold py-2 px-4 rounded"
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-4 rounded shadow disabled:opacity-50"
                                >
                                    Submit Solution
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- AI TASK GUIDE & STEPS MODAL -->
                <div v-if="aiGuideModalTask" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50 overflow-y-auto">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 border border-gray-100 relative my-8">
                        <!-- Header -->
                        <div class="flex items-center justify-between border-b pb-4 mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-lg shadow-sm">
                                    ✨
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-base font-bold text-gray-900">AI Task Guide & Suggestions</h3>
                                        <span v-if="$page.props.auth.user?.department_label" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            {{ $page.props.auth.user.department_label }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 truncate max-w-md">{{ aiGuideModalTask.title }}</p>
                                </div>
                            </div>
                            <button @click="closeAiGuideModal" class="text-gray-400 hover:text-gray-600 font-bold text-lg p-1.5 rounded-lg hover:bg-gray-100 transition">
                                ✕
                            </button>
                        </div>

                        <!-- Loading State -->
                        <div v-if="isLoadingAiGuide" class="py-12 flex flex-col items-center justify-center space-y-3 text-indigo-600">
                            <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                            <p class="text-sm font-semibold text-gray-600">Analyzing task & generating step-by-step guidance...</p>
                        </div>

                        <!-- Data Content -->
                        <div v-else-if="aiGuideData" class="space-y-5">
                            <!-- Simplified Summary Card -->
                            <div class="p-4 rounded-xl bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-800 mb-1 flex items-center gap-1.5">
                                    <span>💡</span> Simplified Task Summary
                                </h4>
                                <p class="text-sm text-gray-800 leading-relaxed font-medium">
                                    {{ aiGuideData.summary }}
                                </p>
                            </div>

                            <!-- Actionable Steps -->
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 mb-3 flex items-center gap-1.5">
                                    <span>📝</span> Step-by-Step Completion Guide
                                </h4>
                                <div class="space-y-2">
                                    <div 
                                        v-for="(step, idx) in aiGuideData.steps" 
                                        :key="idx"
                                        class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200/80 hover:bg-white transition"
                                    >
                                        <span class="w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
                                            {{ idx + 1 }}
                                        </span>
                                        <p class="text-xs sm:text-sm text-gray-700 font-medium leading-relaxed">
                                            {{ step }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Useful Tips -->
                            <div v-if="aiGuideData.tips && aiGuideData.tips.length" class="p-4 rounded-xl bg-amber-50/80 border border-amber-200/80">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-amber-900 mb-2 flex items-center gap-1.5">
                                    <span>🚀</span> Mentor Tips & Best Practices
                                </h4>
                                <ul class="space-y-1 text-xs text-amber-900 list-disc list-inside font-medium">
                                    <li v-for="(tip, tIdx) in aiGuideData.tips" :key="tIdx">
                                        {{ tip }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Footer Close -->
                        <div class="pt-4 border-t mt-6 flex justify-end">
                            <button 
                                @click="closeAiGuideModal" 
                                class="px-5 py-2 bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold rounded-lg shadow transition"
                            >
                                Got It! Close
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>