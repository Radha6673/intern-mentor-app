<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PerformanceReportModal from '@/Components/PerformanceReportModal.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    interns: Array,
});

const searchQuery = ref('');
const showReportModal = ref(false);
const reportData = ref(null);
const loadingReport = ref(false);

// Computed filtered list of interns based on search query
const filteredInterns = computed(() => {
    if (!searchQuery.value.trim()) return props.interns;
    const q = searchQuery.value.toLowerCase();
    return props.interns.filter(
        (intern) =>
            intern.name.toLowerCase().includes(q) ||
            intern.email.toLowerCase().includes(q)
    );
});

// Promote intern handler
const promoteToMentor = (intern) => {
    if (confirm(`Are you sure you want to promote intern "${intern.name}" (${intern.email}) to a Mentor role?\n\nOnce promoted, they will log in as a Mentor.`)) {
        router.post(route('admin.interns.promote', intern.id));
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
                        Admin - Manage & Promote Interns
                    </h2>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        @click="openPerformanceReport()"
                        class="text-xs font-bold text-purple-700 hover:text-purple-900 border border-purple-300 bg-purple-50 rounded-md px-3 py-1.5 transition flex items-center shadow-sm"
                    >
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Overall Team Report
                    </button>
                    <Link :href="route('admin.mentors.index')" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 border border-indigo-200 bg-indigo-50 rounded-md px-3 py-1.5 transition">
                        View Mentors &rarr;
                    </Link>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                        Total Registered Interns: {{ interns.length }}
                    </span>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <!-- Success / Error Flash Alert -->
                <div v-if="$page.props.flash.success" class="rounded-md bg-green-50 p-4 border border-green-200 text-green-800 flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ $page.props.flash.success }}</span>
                    </div>
                </div>

                <div v-if="$page.props.flash.error" class="rounded-md bg-red-50 p-4 border border-red-200 text-red-800 flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>{{ $page.props.flash.error }}</span>
                    </div>
                </div>

                <!-- Info Banner -->
                <div class="bg-indigo-900 text-white rounded-xl p-5 shadow-sm border border-indigo-800 flex items-start gap-4">
                    <div class="p-2 rounded-lg bg-indigo-800 shrink-0">
                        <svg class="w-6 h-6 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base">Promote Intern to Mentor Role</h3>
                        <p class="text-xs text-indigo-200 mt-1 leading-relaxed">
                            Click <strong>"Promote to Mentor"</strong> next to any intern to grant them Mentor privileges. When promoted, their role updates to <code>mentor</code>, allowing them to log in and access the Mentor Portal directly.
                        </p>
                    </div>
                </div>

                <!-- Search Bar & Interns Table -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg border border-gray-100 p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Registered Interns Directory</h3>
                        
                        <div class="relative w-full sm:w-80">
                            <input
                                type="text"
                                v-model="searchQuery"
                                placeholder="Search intern by name or email..."
                                class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 pl-9"
                            />
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Intern</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Email Address</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Joined Date</th>
                                    <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-gray-500">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="intern in filteredInterns" :key="intern.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 shrink-0 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-sm shadow-inner">
                                                {{ intern.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="font-bold text-gray-900 text-sm">{{ intern.name }}</div>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    Intern
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                                        {{ intern.email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ intern.created_at ? new Date(intern.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <button
                                                @click="openPerformanceReport(intern.id)"
                                                class="inline-flex items-center px-3 py-1.5 border border-indigo-200 rounded-lg text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition shadow-sm"
                                            >
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                </svg>
                                                View Report
                                            </button>
                                            <button
                                                @click="promoteToMentor(intern)"
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white shadow-sm transition active:scale-95"
                                            >
                                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                                </svg>
                                                Promote to Mentor
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredInterns.length === 0">
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">
                                        No registered interns found matching your criteria.
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
