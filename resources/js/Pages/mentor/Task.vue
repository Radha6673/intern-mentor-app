<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    interns: Array,
});

// AI State & Generator
const aiTopic = ref('');
const isGeneratingTask = ref(false);

const generateAiTask = async () => {
    if (!aiTopic.value.trim() || isGeneratingTask.value) return;
    isGeneratingTask.value = true;
    try {
        const response = await axios.post(route('ai.generate_task'), {
            topic: aiTopic.value
        });
        if (response.data && response.data.data) {
            if (response.data.data.title) form.title = response.data.data.title;
            if (response.data.data.description) form.description = response.data.data.description;
        }
    } catch (err) {
        console.error('Error generating AI task:', err);
    } finally {
        isGeneratingTask.value = false;
    }
};

// Inertia Task Assign Form State
const form = useForm({
    intern_id: '',
    title: '',
    description: '',
    deadline: '',
});

// Submit Task Assignment Handler
const submitTask = () => {
    form.post(route('mentor.tasks.store'), {
        onSuccess: () => {
            form.reset();
            aiTopic.value = '';
            alert('Task Assigned Successfully!');
        },
    });
};
</script>

<template>
    <Head title="Assign New Task" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Link :href="route('dashboard')" class="text-xs font-bold text-gray-500 hover:text-indigo-600 transition border border-gray-300 rounded-md px-3 py-1.5 bg-white shadow-sm">
                        &larr; Dashboard
                    </Link>
                    <div>
                        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                            Assign Task to Intern
                        </h2>
                        <p class="text-xs text-gray-500 mt-1">
                            Create a new assignment for an intern with clear requirements and deadline.
                        </p>
                    </div>
                </div>

                <Link
                    :href="route('mentor.tasks.history')"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 transition ease-in-out duration-150 shadow"
                >
                    View Task History & Submissions &rarr;
                </Link>
            </div>
        </template>

        <div class="py-10">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Task Assignment Form -->
                <div class="p-8 bg-white shadow sm:rounded-lg border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 border-b pb-3">New Task Assignment Form</h3>

                    <!-- AI Task Generator Panel -->
                    <div class="mb-8 p-5 bg-gradient-to-br from-purple-50 via-indigo-50 to-white rounded-xl border border-purple-200/80 shadow-sm">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="text-xl">✨</span>
                            <h4 class="text-sm font-bold text-purple-900">AI Task Generator for Mentors</h4>
                        </div>
                        <p class="text-xs text-purple-700 mb-3">
                            Type a short topic or task idea, and AI will automatically generate a structured Task Title and detailed Description!
                        </p>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <input 
                                type="text" 
                                v-model="aiTopic" 
                                placeholder="e.g. Build User Authentication REST API with Laravel & JWT" 
                                class="flex-1 text-sm rounded-lg border-purple-200 bg-white focus:ring-purple-500 focus:border-purple-500 shadow-sm"
                                @keydown.enter.prevent="generateAiTask"
                            />
                            <button 
                                type="button" 
                                @click="generateAiTask" 
                                :disabled="isGeneratingTask || !aiTopic.trim()" 
                                class="px-4 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold rounded-lg text-xs shadow transition disabled:opacity-50 flex items-center justify-center gap-1.5 shrink-0 cursor-pointer"
                            >
                                <span v-if="isGeneratingTask" class="animate-spin text-sm">🌀</span>
                                <span v-else>🚀</span>
                                <span>Generate Task with AI</span>
                            </button>
                        </div>
                    </div>
                    
                    <form @submit.prevent="submitTask" class="space-y-6">
                        <!-- Select Intern -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Select Intern *</label>
                            <select 
                                v-model="form.intern_id" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                required
                            >
                                <option value="" disabled>-- Select Intern --</option>
                                <option v-for="intern in interns" :key="intern.id" :value="intern.id">
                                    {{ intern.name }} ({{ intern.email }})
                                </option>
                            </select>
                            <p v-if="form.errors.intern_id" class="text-red-500 text-xs mt-1">{{ form.errors.intern_id }}</p>
                        </div>

                        <!-- Task Title -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Task Title *</label>
                            <input 
                                type="text" 
                                v-model="form.title" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                placeholder="e.g. Implement User Authentication REST API"
                                required 
                            />
                            <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                        </div>

                        <!-- Task Description -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Task Description & Instructions *</label>
                            <textarea 
                                v-model="form.description" 
                                rows="5" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                placeholder="Detail out the scope, expectations, tech stack to use, and step-by-step instructions..."
                                required
                            ></textarea>
                            <p v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</p>
                        </div>

                        <!-- Deadline -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Deadline Date & Time</label>
                            <input 
                                type="datetime-local" 
                                v-model="form.deadline" 
                                class="mt-1 block w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" 
                            />
                            <p v-if="form.errors.deadline" class="text-red-500 text-xs mt-1">{{ form.errors.deadline }}</p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-between pt-4 border-t">
                            <Link 
                                :href="route('mentor.tasks.history')"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 underline"
                            >
                                View Previously Assigned Tasks
                            </Link>

                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-md shadow-md text-sm transition disabled:opacity-50"
                            >
                                Assign Task
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>