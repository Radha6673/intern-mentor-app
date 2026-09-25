<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    interns: {
        type: Array,
        default: () => [],
    },
    departments: {
        type: Array,
        default: () => [],
    },
});

// AI State & Generator
const aiTopic = ref('');
const aiDepartment = ref('');
const isGeneratingTask = ref(false);
const lastGeneratedDept = ref(null);
const selectedFilterDept = ref('all');

// Inertia Task Assign Form State
const form = useForm({
    intern_id: '',
    title: '',
    description: '',
    deadline: '',
});

// Selected intern computed
const selectedIntern = computed(() => {
    if (!form.intern_id) return null;
    return props.interns.find((i) => String(i.id) === String(form.intern_id));
});

// Filtered interns by department
const filteredInterns = computed(() => {
    if (selectedFilterDept.value === 'all') return props.interns;
    return props.interns.filter((i) => i.department === selectedFilterDept.value);
});

// Department styling helpers
const getDepartmentBadgeStyle = (dept) => {
    switch (dept) {
        case 'web_developer':
            return 'bg-blue-50 text-blue-700 border-blue-200';
        case 'android_developer':
            return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        case 'ios_developer':
            return 'bg-sky-50 text-sky-700 border-sky-200';
        case 'devops':
            return 'bg-amber-50 text-amber-700 border-amber-200';
        case 'ai_developer':
            return 'bg-purple-50 text-purple-700 border-purple-200';
        case 'business_analyst':
            return 'bg-rose-50 text-rose-700 border-rose-200';
        case 'data_analyst':
            return 'bg-teal-50 text-teal-700 border-teal-200';
        default:
            return 'bg-gray-100 text-gray-700 border-gray-200';
    }
};

const getDepartmentLabel = (dept) => {
    if (!dept) return 'General';
    const match = props.departments.find((d) => d.value === dept);
    return match ? match.label : dept.replace(/_/g, ' ');
};

// Generate AI Task tailored to Intern's Department
const generateAiTask = async () => {
    if (!aiTopic.value.trim() || isGeneratingTask.value) return;
    isGeneratingTask.value = true;
    lastGeneratedDept.value = null;

    const targetDept = selectedIntern.value?.department || aiDepartment.value;

    try {
        const response = await axios.post(route('ai.generate_task'), {
            topic: aiTopic.value,
            intern_id: form.intern_id || null,
            department: targetDept || null,
        });

        if (response.data && response.data.data) {
            if (response.data.data.title) form.title = response.data.data.title;
            if (response.data.data.description) form.description = response.data.data.description;
            lastGeneratedDept.value = response.data.department || targetDept;
        }
    } catch (err) {
        console.error('Error generating AI task:', err);
    } finally {
        isGeneratingTask.value = false;
    }
};

// Submit Task Assignment Handler
const submitTask = () => {
    form.post(route('mentor.tasks.store'), {
        onSuccess: () => {
            form.reset();
            aiTopic.value = '';
            lastGeneratedDept.value = null;
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
                            Assign Department Task to Intern
                        </h2>
                        <p class="text-xs text-gray-500 mt-1">
                            Assign tasks and use AI to automatically generate requirements according to the intern's department.
                        </p>
                    </div>
                </div>

                <Link
                    :href="route('mentor.tasks.history')"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-xl font-semibold text-xs text-white tracking-widest hover:bg-gray-700 active:bg-gray-900 shadow transition"
                >
                    View Task History & Submissions &rarr;
                </Link>
            </div>
        </template>

        <div class="py-10">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Task Assignment Form Card -->
                <div class="p-8 bg-white shadow-sm sm:rounded-2xl border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 border-b pb-3">New Task Assignment Form</h3>

                    <!-- AI Task Generator Panel with Department Allocation -->
                    <div class="mb-8 p-6 bg-gradient-to-br from-purple-50 via-indigo-50/50 to-white rounded-2xl border border-purple-200/80 shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-2xl">✨</span>
                                <div>
                                    <h4 class="text-sm font-bold text-purple-900">Department-Aware AI Task Generator</h4>
                                    <p class="text-xs text-purple-700">
                                        Type a topic idea, and AI will generate complete title, requirements & deliverables tailored to the intern's department!
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Department context indicator / override -->
                        <div v-if="selectedIntern" class="flex flex-wrap items-center gap-2 p-3 bg-white/90 rounded-xl border border-purple-200 text-xs shadow-inner">
                            <span class="text-gray-600 font-medium">Allocating to:</span>
                            <strong class="text-gray-900">{{ selectedIntern.name }}</strong>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border"
                                :class="getDepartmentBadgeStyle(selectedIntern.department)"
                            >
                                {{ getDepartmentLabel(selectedIntern.department) }}
                            </span>
                            <span class="text-purple-600 font-semibold ml-auto flex items-center gap-1">
                                <span>🎯</span> AI will tailor task stack & instructions for {{ getDepartmentLabel(selectedIntern.department) }}
                            </span>
                        </div>
                        <div v-else class="flex flex-wrap items-center gap-2 p-3 bg-white/90 rounded-xl border border-purple-200 text-xs shadow-inner">
                            <span class="text-purple-900 font-semibold">Select Target Department:</span>
                            <select
                                v-model="aiDepartment"
                                class="text-xs py-1.5 px-3 rounded-lg border-purple-200 bg-white text-purple-900 font-medium focus:ring-purple-500"
                            >
                                <option value="">-- General / Web Developer --</option>
                                <option v-for="dept in departments" :key="dept.value" :value="dept.value">
                                    {{ dept.label }}
                                </option>
                            </select>
                            <span class="text-gray-500 text-[11px]">
                                (Or select an intern below to auto-detect their department)
                            </span>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-2">
                            <input 
                                type="text" 
                                v-model="aiTopic" 
                                placeholder="e.g. Implement User Authentication REST API or Build Dashboard Analytics" 
                                class="flex-1 text-xs rounded-xl border-purple-200 bg-white focus:ring-purple-500 focus:border-purple-500 shadow-sm py-2.5"
                                @keydown.enter.prevent="generateAiTask"
                            />
                            <button 
                                type="button" 
                                @click="generateAiTask" 
                                :disabled="isGeneratingTask || !aiTopic.trim()" 
                                class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold rounded-xl text-xs shadow-md transition disabled:opacity-50 flex items-center justify-center gap-2 shrink-0 cursor-pointer"
                            >
                                <span v-if="isGeneratingTask" class="animate-spin text-sm">🌀</span>
                                <span v-else>🚀</span>
                                <span>Generate with AI</span>
                            </button>
                        </div>

                        <div v-if="lastGeneratedDept" class="text-xs font-semibold text-emerald-700 flex items-center gap-1.5 pt-1">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Task generated specifically for <strong>{{ getDepartmentLabel(lastGeneratedDept) }}</strong> department!</span>
                        </div>
                    </div>
                    
                    <form @submit.prevent="submitTask" class="space-y-6">
                        <!-- Select Intern with Department Filter -->
                        <div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1.5">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Select Intern *</label>
                                
                                <!-- Department Quick Filter for Interns -->
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="text-gray-500">Filter Interns:</span>
                                    <select
                                        v-model="selectedFilterDept"
                                        class="text-xs py-1 px-2.5 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 font-medium"
                                    >
                                        <option value="all">All Departments ({{ interns.length }})</option>
                                        <option v-for="dept in departments" :key="dept.value" :value="dept.value">
                                            {{ dept.label }} ({{ interns.filter(i => i.department === dept.value).length }})
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <select 
                                v-model="form.intern_id" 
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-medium"
                                required
                            >
                                <option value="" disabled>-- Select Intern to Assign Task --</option>
                                <option v-for="intern in filteredInterns" :key="intern.id" :value="intern.id">
                                    {{ intern.name }} &bull; [{{ getDepartmentLabel(intern.department) }}] &bull; ({{ intern.email }})
                                </option>
                            </select>
                            <p v-if="form.errors.intern_id" class="text-red-500 text-xs mt-1">{{ form.errors.intern_id }}</p>

                            <!-- Selected Intern Department Pill Indicator -->
                            <div v-if="selectedIntern" class="mt-2.5 flex items-center gap-2">
                                <span class="text-xs text-gray-500">Intern's Assigned Department:</span>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border"
                                    :class="getDepartmentBadgeStyle(selectedIntern.department)"
                                >
                                    {{ getDepartmentLabel(selectedIntern.department) }}
                                </span>
                            </div>
                        </div>

                        <!-- Task Title -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Task Title *</label>
                            <input 
                                type="text" 
                                v-model="form.title" 
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                placeholder="e.g. Implement User Authentication REST API"
                                required 
                            />
                            <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                        </div>

                        <!-- Task Description -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Task Description & Instructions *</label>
                            <textarea 
                                v-model="form.description" 
                                rows="6" 
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                placeholder="Detail out the scope, expectations, tech stack to use, and step-by-step instructions..."
                                required
                            ></textarea>
                            <p v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</p>
                        </div>

                        <!-- Deadline -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Deadline Date & Time</label>
                            <input 
                                type="datetime-local" 
                                v-model="form.deadline" 
                                class="mt-1 block w-full md:w-1/2 rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" 
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
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-md text-xs transition disabled:opacity-50 cursor-pointer"
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