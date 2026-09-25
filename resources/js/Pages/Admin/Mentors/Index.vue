<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    mentors: {
        type: Array,
        default: () => [],
    },
    departments: {
        type: Array,
        default: () => [],
    },
});

const searchQuery = ref('');
const selectedDepartment = ref('all');

// Form state for adding new mentor
const form = useForm({
    name: '',
    email: '',
    department: '',
    password: '',
    password_confirmation: '',
});

// Submit handler
const submitMentor = () => {
    form.post(route('admin.mentors.store'), {
        onSuccess: () => {
            form.reset('password', 'password_confirmation', 'name', 'email', 'department');
        },
    });
};

// Delete mentor handler
const deleteMentor = (mentor) => {
    if (confirm(`Are you sure you want to delete mentor "${mentor.name}"?`)) {
        router.delete(route('admin.mentors.destroy', mentor.id));
    }
};

// Department styling helper
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
    if (!dept) return 'Not Assigned';
    const match = props.departments.find((d) => d.value === dept);
    return match ? match.label : dept.replace(/_/g, ' ');
};

// Filtered mentors list
const filteredMentors = computed(() => {
    return props.mentors.filter((m) => {
        const matchesDept = selectedDepartment.value === 'all' || m.department === selectedDepartment.value;
        const q = searchQuery.value.trim().toLowerCase();
        const matchesQuery = !q || m.name?.toLowerCase().includes(q) || m.email?.toLowerCase().includes(q);
        return matchesDept && matchesQuery;
    });
});
</script>

<template>
    <Head title="Manage Mentors" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Link :href="route('dashboard')" class="text-xs font-bold text-gray-500 hover:text-indigo-600 transition border border-gray-300 rounded-md px-3 py-1.5 bg-white shadow-sm">
                        &larr; Back to Dashboard
                    </Link>
                    <h2 class="text-xl font-bold leading-tight text-gray-800">
                        Admin - Manage Mentors
                    </h2>
                </div>
                <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-800 self-start sm:self-auto">
                    Total Mentors: {{ mentors.length }}
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <!-- Flash Alerts -->
                <div v-if="$page.props.flash?.success" class="rounded-xl bg-green-50 p-4 border border-green-200 text-green-800 flex justify-between items-center shadow-sm">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-sm font-medium">{{ $page.props.flash.success }}</span>
                    </div>
                </div>

                <div v-if="$page.props.flash?.error" class="rounded-xl bg-red-50 p-4 border border-red-200 text-red-800 flex justify-between items-center shadow-sm">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span class="text-sm font-medium">{{ $page.props.flash.error }}</span>
                    </div>
                </div>

                <!-- Add New Mentor Form Card -->
                <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-2xl border border-gray-100">
                    <div class="border-b border-gray-100 pb-4 mb-5">
                        <h3 class="text-lg font-bold text-gray-900">Add New Mentor</h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Assign mentor credentials and classify them into their specialized technical department.
                        </p>
                    </div>

                    <form @submit.prevent="submitMentor" class="space-y-4 max-w-3xl">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Mentor Name -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Full Name *</label>
                                <input
                                    type="text"
                                    v-model="form.name"
                                    class="w-full rounded-xl border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="e.g. Rahul Sharma"
                                    required
                                />
                                <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <!-- Mentor Email -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Email Address *</label>
                                <input
                                    type="email"
                                    v-model="form.email"
                                    class="w-full rounded-xl border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="e.g. mentor@company.com"
                                    required
                                />
                                <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                            </div>
                        </div>

                        <!-- Department Selection -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Department Classification *</label>
                            <select
                                v-model="form.department"
                                class="w-full rounded-xl border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 font-medium"
                                required
                            >
                                <option value="" disabled>-- Select Department --</option>
                                <option v-for="dept in departments" :key="dept.value" :value="dept.value">
                                    {{ dept.label }}
                                </option>
                            </select>
                            <p v-if="form.errors.department" class="mt-1 text-xs text-red-600">{{ form.errors.department }}</p>
                        </div>

                        <!-- Password & Password Confirmation Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Password *</label>
                                <input
                                    type="password"
                                    v-model="form.password"
                                    class="w-full rounded-xl border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="••••••••"
                                    required
                                />
                                <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Confirm Password *</label>
                                <input
                                    type="password"
                                    v-model="form.password_confirmation"
                                    class="w-full rounded-xl border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="••••••••"
                                    required
                                />
                                <p v-if="form.errors.password_confirmation" class="mt-1 text-xs text-red-600">
                                    {{ form.errors.password_confirmation }}
                                </p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition"
                            >
                                <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                {{ form.processing ? 'Adding Mentor...' : '+ Add Mentor' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Existing Mentors Table Card with Department Filter -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-2xl border border-gray-100 p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Mentors Directory</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Filter mentors by department or search by name and email.</p>
                        </div>
                        <div class="relative w-full sm:w-72">
                            <input
                                type="text"
                                v-model="searchQuery"
                                placeholder="Search mentors..."
                                class="w-full rounded-xl border-gray-300 shadow-sm text-xs focus:border-indigo-500 focus:ring-indigo-500 pl-9 py-2"
                            />
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Department Filter Pills -->
                    <div class="flex flex-wrap items-center gap-1.5 pt-2 pb-1 border-b border-gray-100">
                        <button
                            type="button"
                            @click="selectedDepartment = 'all'"
                            :class="selectedDepartment === 'all' ? 'bg-indigo-600 text-white font-bold' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-3 py-1 rounded-lg text-xs transition cursor-pointer"
                        >
                            All Departments ({{ mentors.length }})
                        </button>
                        <button
                            v-for="dept in departments"
                            :key="dept.value"
                            type="button"
                            @click="selectedDepartment = dept.value"
                            :class="selectedDepartment === dept.value ? 'bg-indigo-600 text-white font-bold' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-3 py-1 rounded-lg text-xs transition cursor-pointer"
                        >
                            {{ dept.label }} ({{ mentors.filter(m => m.department === dept.value).length }})
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Mentor</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Department</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Email</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Joined Date</th>
                                    <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="mentor in filteredMentors" :key="mentor.id" class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-9 w-9 rounded-xl bg-purple-100 text-purple-700 font-bold flex items-center justify-center text-sm shrink-0">
                                                {{ mentor.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div class="ml-3 font-bold text-gray-900 text-sm">{{ mentor.name }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                                            :class="getDepartmentBadgeStyle(mentor.department)"
                                        >
                                            {{ getDepartmentLabel(mentor.department) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ mentor.email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                        {{ mentor.created_at ? new Date(mentor.created_at).toLocaleDateString() : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button
                                            @click="deleteMentor(mentor)"
                                            class="text-red-600 hover:text-red-800 text-xs font-bold transition focus:outline-none"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="filteredMentors.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                        No mentors found matching your selected department or search filter.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
