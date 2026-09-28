<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PerformanceReportModal from '@/Components/PerformanceReportModal.vue';
import { Head, router, Link } from '@inertiajs/vue3';
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

const searchQuery = ref('');
const selectedDepartment = ref('all');
const showReportModal = ref(false);
const reportData = ref(null);
const loadingReport = ref(false);

// Department badge colors
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

// Computed filtered list of interns
const filteredInterns = computed(() => {
    return props.interns.filter((intern) => {
        const matchesDept = selectedDepartment.value === 'all' || intern.department === selectedDepartment.value;
        const q = searchQuery.value.trim().toLowerCase();
        const matchesQuery = !q || intern.name?.toLowerCase().includes(q) || intern.email?.toLowerCase().includes(q);
        return matchesDept && matchesQuery;
    });
});

// Update intern department
const changeInternDepartment = (intern, newDept) => {
    if (!newDept || newDept === intern.department) return;
    router.patch(route('admin.interns.department', intern.id), {
        department: newDept,
    }, {
        preserveScroll: true,
    });
};

// Promote intern handler
const promoteToMentor = (intern) => {
    if (confirm(`Are you sure you want to promote intern "${intern.name}" (${intern.email}) to a Mentor role?\n\nOnce promoted, they will log in as a Mentor.`)) {
        router.post(route('admin.interns.promote', intern.id));
    }
};

// Delete intern handler
const deleteIntern = (intern) => {
    if (confirm(`Are you sure you want to delete intern "${intern.name}" (${intern.email})?\n\nAll their tasks, submissions, and messages will also be permanently deleted.`)) {
        router.delete(route('admin.interns.destroy', intern.id), {
            preserveScroll: true,
        });
    }
};

const openPerformanceReport = async (internId = null) => {
    showReportModal.value = true;
    loadingReport.value = true;
    reportData.value = null;
    try {
        const url = internId ? route('reports.performance', internId) : route('reports.performance');
        const response = await axios.get(url);
        reportData.value = response.data;
    } catch (error) {
        console.error('Failed to load performance report:', error);
    } finally {
        loadingReport.value = false;
    }
};
</script>

<template>
    <Head title="Admin - Manage Interns" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Link :href="route('dashboard')" class="text-xs font-bold text-gray-500 hover:text-indigo-600 transition border border-gray-300 rounded-md px-3 py-1.5 bg-white shadow-sm">
                        &larr; Back to Dashboard
                    </Link>
                    <h2 class="text-xl font-bold leading-tight text-gray-800">
                        Admin - Manage & Classify Interns
                    </h2>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        @click="openPerformanceReport()"
                        class="text-xs font-bold text-purple-700 hover:text-purple-900 border border-purple-300 bg-purple-50 rounded-xl px-3 py-1.5 transition flex items-center shadow-sm cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Team Report
                    </button>
                    <Link :href="route('admin.mentors.index')" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 border border-indigo-200 bg-indigo-50 rounded-xl px-3 py-1.5 transition">
                        View Mentors &rarr;
                    </Link>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                        Total Interns: {{ interns.length }}
                    </span>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <!-- Success / Error Flash Alert -->
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

                <!-- Info Banner -->
                <div class="bg-indigo-900 text-white rounded-2xl p-5 shadow-sm border border-indigo-800 flex items-start gap-4">
                    <div class="p-2 rounded-xl bg-indigo-800 shrink-0">
                        <svg class="w-6 h-6 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base">Department Classification & Mentorship Allocation</h3>
                        <p class="text-xs text-indigo-200 mt-1 leading-relaxed">
                            Each intern is classified into their technical department (Web Developer, Android, iOS, DevOps, AI Developer, Business Analyst, Data Analyst). Mentors assign department-specific tasks, and AI dynamically personalizes guidance according to each intern's department.
                        </p>
                    </div>
                </div>

                <!-- Search Bar & Interns Table Card -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-2xl border border-gray-100 p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Registered Interns Directory</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Filter by department or search by name and email.</p>
                        </div>
                        
                        <div class="relative w-full sm:w-80">
                            <input
                                type="text"
                                v-model="searchQuery"
                                placeholder="Search intern by name or email..."
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
                            All Departments ({{ interns.length }})
                        </button>
                        <button
                            v-for="dept in departments"
                            :key="dept.value"
                            type="button"
                            @click="selectedDepartment = dept.value"
                            :class="selectedDepartment === dept.value ? 'bg-indigo-600 text-white font-bold' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-3 py-1 rounded-lg text-xs transition cursor-pointer"
                        >
                            {{ dept.label }} ({{ interns.filter(i => i.department === dept.value).length }})
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Intern</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Department</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Email Address</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Joined Date</th>
                                    <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-gray-500">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="intern in filteredInterns" :key="intern.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 shrink-0 rounded-xl bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-sm shadow-inner">
                                                {{ intern.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="font-bold text-gray-900 text-sm">{{ intern.name }}</div>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    Intern
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- Department Dropdown / Badge -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <select
                                                :value="intern.department || ''"
                                                @change="changeInternDepartment(intern, $event.target.value)"
                                                class="text-xs font-semibold rounded-lg border-gray-200 shadow-sm py-1 pl-2.5 pr-7 focus:ring-indigo-500 focus:border-indigo-500"
                                                :class="getDepartmentBadgeStyle(intern.department)"
                                            >
                                                <option value="" disabled>Select Department</option>
                                                <option v-for="dept in departments" :key="dept.value" :value="dept.value">
                                                    {{ dept.label }}
                                                </option>
                                            </select>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                                        {{ intern.email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                        {{ intern.created_at ? new Date(intern.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <button
                                                @click="openPerformanceReport(intern.id)"
                                                class="inline-flex items-center px-3 py-1.5 border border-indigo-200 rounded-lg text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition shadow-sm cursor-pointer"
                                            >
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                </svg>
                                                Report
                                            </button>
                                            <button
                                                @click="promoteToMentor(intern)"
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white shadow-sm transition active:scale-95 cursor-pointer"
                                            >
                                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                                </svg>
                                                Promote
                                            </button>
                                            <button
                                                @click="deleteIntern(intern)"
                                                class="inline-flex items-center px-3 py-1.5 border border-rose-200 rounded-lg text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 transition shadow-sm active:scale-95 cursor-pointer"
                                            >
                                                <svg class="w-3.5 h-3.5 mr-1 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredInterns.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                        No registered interns found matching your department or search criteria.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Performance Report Modal -->
        <PerformanceReportModal
            :show="showReportModal"
            :reportData="reportData"
            :loading="loadingReport"
            @close="showReportModal = false"
        />
    </AuthenticatedLayout>
</template>
