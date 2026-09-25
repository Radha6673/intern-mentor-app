<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PerformanceReportModal from '@/Components/PerformanceReportModal.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    stats: Object,
    recentData: Object,
    members: {
        type: Array,
        default: () => [],
    },
});

const user = computed(() => usePage().props.auth.user);

const showReportModal = ref(false);
const reportData = ref(null);
const loadingReport = ref(false);

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

const membersSectionTitle = computed(() => {
    if (user.value.role === 'intern') return 'My Mentors';
    if (user.value.role === 'mentor') return 'My Interns';
    return 'System Members Directory';
});

const membersSectionSubtitle = computed(() => {
    if (user.value.role === 'intern') return 'Quick access to your assigned mentors for guidance & chat.';
    if (user.value.role === 'mentor') return 'Quick access to your assigned interns & communication.';
    return 'Directory of all active mentors and interns.';
});

// Dynamic Role Badge Colors
const roleBadgeClass = computed(() => {
    switch (user.value.role) {
        case 'admin':
            return 'bg-gradient-to-r from-red-500 to-rose-600 text-white shadow-rose-500/30';
        case 'mentor':
            return 'bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-purple-500/30';
        case 'intern':
            return 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-emerald-500/30';
        default:
            return 'bg-gray-600 text-white';
    }
});

// Mentor Department Visual Metrics
const deptCompletionRate = computed(() => {
    return props.stats?.department?.completion_rate ?? 0;
});

const deptCircumference = 238.76;
const deptStrokeOffset = computed(() => {
    const rate = Math.min(100, Math.max(0, deptCompletionRate.value));
    return deptCircumference - (deptCircumference * rate) / 100;
});
</script>

<template>
    <Head title="Dashboard Overview" />

    <AuthenticatedLayout>
        <div class="py-8 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <!-- HERO WELCOME BANNER -->
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-8 shadow-2xl border border-indigo-500/20">
                    <!-- Background Glow Decorators -->
                    <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                        <div class="flex items-center gap-5">
                            <!-- Avatar Ring -->
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-500 via-purple-500 to-pink-500 p-0.5 shadow-xl flex items-center justify-center shrink-0">
                                <div class="w-full h-full bg-slate-900 rounded-[14px] flex items-center justify-center font-black text-2xl text-transparent bg-clip-text bg-gradient-to-tr from-indigo-300 to-white">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center gap-3 mb-1">
                                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                                        Welcome back, {{ user.name }}!
                                    </h1>
                                    <span class="px-3 py-0.5 rounded-full text-xs font-black uppercase tracking-widest shadow-lg" :class="roleBadgeClass">
                                        {{ user.role }}
                                    </span>
                                    <span v-if="user.department_label" class="px-3 py-0.5 rounded-full text-xs font-bold bg-white/10 text-indigo-200 border border-indigo-400/30">
                                        {{ user.department_label }}
                                    </span>
                                </div>
                                <p class="text-sm text-slate-300 font-medium">
                                    Here is what's happening across your platform workspace today.
                                </p>
                            </div>
                        </div>

                        <!-- Quick Navigation Action in Hero (Clean for Admin & Mentor) -->
                        <div v-if="user.role === 'intern'" class="flex flex-wrap gap-3 w-full md:w-auto">
                            <!-- Role-based Members Directory Link -->
                            <Link 
                                :href="route('members.index')"
                                class="inline-flex items-center px-4 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-500 text-white transition-all duration-200 shadow-lg shadow-indigo-600/30"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                My Mentors
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- ADMIN DASHBOARD METRICS -->
                <div v-if="user.role === 'admin'" class="space-y-8">
                    <!-- Performance Reports Quick Banner for Admin -->
                    <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 rounded-2xl p-6 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6 border border-indigo-500/20">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-indigo-200 shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold">Interns Performance Analytics & Reports</h3>
                                <p class="text-xs text-indigo-200 mt-0.5">Live completion rates, task breakdown, and progress tracking across all registered interns.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <button
                                type="button"
                                @click="openPerformanceReport()"
                                class="inline-flex items-center px-4 py-2.5 rounded-xl text-xs font-bold bg-white text-indigo-900 hover:bg-indigo-50 transition shadow-md cursor-pointer"
                            >
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View Overall Report
                            </button>
                            <Link
                                :href="route('admin.interns.index')"
                                class="inline-flex items-center px-4 py-2.5 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 text-white transition border border-white/10"
                            >
                                Manage Interns &rarr;
                            </Link>
                        </div>
                    </div>

                    <!-- Metric Cards Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <!-- Total Mentors -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Mentors</p>
                                    <h3 class="text-3xl font-extrabold text-gray-900 mt-2">{{ stats.mentors_count }}</h3>
                                </div>
                                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-gray-100">
                                <Link :href="route('admin.mentors.index')" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">
                                    Manage Mentors &rarr;
                                </Link>
                            </div>
                        </div>

                        <!-- Total Interns -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Registered Interns</p>
                                    <h3 class="text-3xl font-extrabold text-gray-900 mt-2">{{ stats.interns_count }}</h3>
                                </div>
                                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                                <span class="text-xs text-gray-400 font-medium">Active Platform Users</span>
                                <Link :href="route('admin.interns.index')" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                                    Interns & Reports &rarr;
                                </Link>
                            </div>
                        </div>

                        <!-- Total Tasks Created -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">System Total Tasks</p>
                                    <h3 class="text-3xl font-extrabold text-gray-900 mt-2">{{ stats.total_tasks_count }}</h3>
                                </div>
                                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-gray-100">
                                <span class="text-xs text-gray-400 font-medium">Assigned across all mentors</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Mentors Section -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Recently Added Mentors</h3>
                            <Link :href="route('admin.mentors.index')" class="text-xs font-bold text-indigo-600 hover:underline">
                                View All Mentors &rarr;
                            </Link>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Name</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Email</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Added On</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="mentor in recentData.recent_mentors" :key="mentor.id" class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-semibold text-gray-900">{{ mentor.name }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ mentor.email }}</td>
                                        <td class="px-4 py-3 text-right text-gray-500 text-xs">
                                            {{ new Date(mentor.created_at).toLocaleDateString() }}
                                        </td>
                                    </tr>
                                    <tr v-if="!recentData.recent_mentors || recentData.recent_mentors.length === 0">
                                        <td colspan="3" class="px-4 py-6 text-center text-gray-500">No mentors created yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- MENTOR DASHBOARD METRICS -->
                <div v-if="user.role === 'mentor'" class="space-y-8">
                    <!-- Metric Cards Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Total Assigned -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Assigned Tasks</p>
                                    <h3 class="text-3xl font-extrabold text-gray-900 mt-2">{{ stats.total_assigned_tasks }}</h3>
                                </div>
                                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Reviews -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Pending Review</p>
                                    <h3 class="text-3xl font-extrabold text-purple-700 mt-2">{{ stats.pending_reviews }}</h3>
                                </div>
                                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Approved -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Approved Tasks</p>
                                    <h3 class="text-3xl font-extrabold text-emerald-700 mt-2">{{ stats.approved_tasks }}</h3>
                                </div>
                                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Rejected -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Rejected Tasks</p>
                                    <h3 class="text-3xl font-extrabold text-rose-700 mt-2">{{ stats.rejected_tasks }}</h3>
                                </div>
                                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DEPARTMENT PERFORMANCE & VISUAL ANALYTICS -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8 space-y-6">
                        <!-- Header with Department Badge & Live Completion Rate Badge -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100">
                            <div class="flex items-center space-x-3.5">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-indigo-500 to-purple-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/20 shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-lg sm:text-xl font-extrabold text-gray-900">
                                            {{ stats.department?.label || 'Department' }} Performance
                                        </h3>
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/50">
                                            {{ stats.department?.total_interns || 0 }} Interns
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Real-time task completion and health analytics for interns in your department.
                                    </p>
                                </div>
                            </div>

                            <!-- Live Completion Rate Badge & Report Action -->
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-black bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-md shadow-emerald-500/25">
                                    <span class="w-2 h-2 rounded-full bg-white animate-pulse mr-2"></span>
                                    {{ stats.department?.completion_rate || 0 }}% Completed
                                </span>
                                <button
                                    type="button"
                                    @click="openPerformanceReport()"
                                    class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-xs font-bold bg-gray-100 hover:bg-gray-200 text-gray-700 transition cursor-pointer"
                                >
                                    Detailed Report &rarr;
                                </button>
                            </div>
                        </div>

                        <!-- 2-COLUMN VISUAL METRICS: Mini Donut Chart + Sleek Visual Breakdown -->
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                            
                            <!-- Left: Circular Mini Donut Progress Ring (4 cols) -->
                            <div class="lg:col-span-4 flex items-center justify-center p-4 bg-slate-50/70 rounded-2xl border border-slate-100">
                                <div class="flex items-center gap-6">
                                    <!-- Circular Donut SVG -->
                                    <div class="relative w-28 h-28 shrink-0 flex items-center justify-center">
                                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 96 96">
                                            <!-- Background circle -->
                                            <circle
                                                cx="48"
                                                cy="48"
                                                r="38"
                                                class="text-gray-200"
                                                stroke-width="8"
                                                stroke="currentColor"
                                                fill="transparent"
                                            />
                                            <!-- Animated Progress circle -->
                                            <circle
                                                cx="48"
                                                cy="48"
                                                r="38"
                                                stroke="url(#mentorDeptGradient)"
                                                stroke-width="8"
                                                stroke-linecap="round"
                                                fill="transparent"
                                                :stroke-dasharray="deptCircumference"
                                                :stroke-dashoffset="deptStrokeOffset"
                                                class="transition-all duration-1000 ease-out"
                                            />
                                            <!-- SVG Gradient Definition -->
                                            <defs>
                                                <linearGradient id="mentorDeptGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                                    <stop offset="0%" stop-color="#10b981" />
                                                    <stop offset="100%" stop-color="#06b6d4" />
                                                </linearGradient>
                                            </defs>
                                        </svg>

                                        <!-- Center Percentage Text -->
                                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                            <span class="text-2xl font-black text-gray-900 leading-none">
                                                {{ Math.round(stats.department?.completion_rate || 0) }}%
                                            </span>
                                            <span class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mt-0.5">
                                                Success
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Quick text stats beside donut -->
                                    <div class="space-y-1.5 text-xs">
                                        <div>
                                            <p class="text-gray-400 text-[10px] font-semibold uppercase tracking-wider">Total Tasks</p>
                                            <p class="text-lg font-black text-gray-900 leading-tight">{{ stats.department?.total_tasks || 0 }}</p>
                                        </div>
                                        <div>
                                            <p class="text-gray-400 text-[10px] font-semibold uppercase tracking-wider">Approved Deliverables</p>
                                            <p class="text-sm font-bold text-emerald-600 leading-tight">{{ stats.department?.approved_tasks || 0 }} Tasks</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Approved vs Rejected vs Pending Sleek Visual Breakdown (8 cols) -->
                            <div class="lg:col-span-8 space-y-4">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-gray-700">Status Distribution</span>
                                    <span class="text-gray-400">Department Overview</span>
                                </div>

                                <!-- Multi-segment Stacked Progress Bar -->
                                <div class="h-3.5 w-full bg-gray-100 rounded-full overflow-hidden flex shadow-inner p-0.5 gap-0.5">
                                    <div 
                                        :style="{ width: (stats.department?.approved_pct || 0) + '%' }" 
                                        class="h-full bg-gradient-to-r from-emerald-400 to-emerald-500 rounded-full transition-all duration-700" 
                                        :title="'Approved: ' + (stats.department?.approved_pct || 0) + '%'"
                                    ></div>
                                    <div 
                                        :style="{ width: (stats.department?.submitted_pct || 0) + '%' }" 
                                        class="h-full bg-gradient-to-r from-indigo-400 to-purple-500 rounded-full transition-all duration-700" 
                                        :title="'Under Review: ' + (stats.department?.submitted_pct || 0) + '%'"
                                    ></div>
                                    <div 
                                        :style="{ width: (stats.department?.pending_pct || 0) + '%' }" 
                                        class="h-full bg-gradient-to-r from-amber-400 to-yellow-500 rounded-full transition-all duration-700" 
                                        :title="'Pending Work: ' + (stats.department?.pending_pct || 0) + '%'"
                                    ></div>
                                    <div 
                                        :style="{ width: (stats.department?.rejected_pct || 0) + '%' }" 
                                        class="h-full bg-gradient-to-r from-rose-400 to-red-500 rounded-full transition-all duration-700" 
                                        :title="'Rejected / Revisions: ' + (stats.department?.rejected_pct || 0) + '%'"
                                    ></div>
                                </div>

                                <!-- 4 Status Metric Pills -->
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                                    <!-- Approved Pill -->
                                    <div class="p-3 rounded-xl bg-emerald-50/60 border border-emerald-100">
                                        <div class="flex items-center space-x-1.5 mb-1">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            <span class="text-[11px] font-bold text-emerald-800">Approved</span>
                                        </div>
                                        <div class="flex items-baseline justify-between">
                                            <span class="text-lg font-black text-emerald-900">{{ stats.department?.approved_tasks || 0 }}</span>
                                            <span class="text-[11px] font-semibold text-emerald-600">{{ stats.department?.approved_pct || 0 }}%</span>
                                        </div>
                                    </div>

                                    <!-- Submitted / In Review Pill -->
                                    <div class="p-3 rounded-xl bg-indigo-50/60 border border-indigo-100">
                                        <div class="flex items-center space-x-1.5 mb-1">
                                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                            <span class="text-[11px] font-bold text-indigo-800">In Review</span>
                                        </div>
                                        <div class="flex items-baseline justify-between">
                                            <span class="text-lg font-black text-indigo-900">{{ stats.department?.submitted_tasks || 0 }}</span>
                                            <span class="text-[11px] font-semibold text-indigo-600">{{ stats.department?.submitted_pct || 0 }}%</span>
                                        </div>
                                    </div>

                                    <!-- Pending Work Pill -->
                                    <div class="p-3 rounded-xl bg-amber-50/60 border border-amber-100">
                                        <div class="flex items-center space-x-1.5 mb-1">
                                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                            <span class="text-[11px] font-bold text-amber-800">Pending</span>
                                        </div>
                                        <div class="flex items-baseline justify-between">
                                            <span class="text-lg font-black text-amber-900">{{ stats.department?.pending_tasks || 0 }}</span>
                                            <span class="text-[11px] font-semibold text-amber-600">{{ stats.department?.pending_pct || 0 }}%</span>
                                        </div>
                                    </div>

                                    <!-- Rejected / Revision Pill -->
                                    <div class="p-3 rounded-xl bg-rose-50/60 border border-rose-100">
                                        <div class="flex items-center space-x-1.5 mb-1">
                                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                            <span class="text-[11px] font-bold text-rose-800">Rejected</span>
                                        </div>
                                        <div class="flex items-baseline justify-between">
                                            <span class="text-lg font-black text-rose-900">{{ stats.department?.rejected_tasks || 0 }}</span>
                                            <span class="text-[11px] font-semibold text-rose-600">{{ stats.department?.rejected_pct || 0 }}%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TASKS APPROACHING DEADLINE (NEXT 24-48 HOURS) -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8 space-y-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3.5">
                                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">
                                        Tasks Approaching Deadline (Next 24-48 Hours)
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Interns who have not submitted their work yet. Use the chat shortcut to follow up directly.
                                    </p>
                                </div>
                            </div>

                            <span 
                                v-if="recentData.upcoming_deadlines && recentData.upcoming_deadlines.length > 0"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800"
                            >
                                {{ recentData.upcoming_deadlines.length }} Approaching
                            </span>
                        </div>

                        <!-- Cards Grid for Upcoming Deadline Tasks -->
                        <div v-if="recentData.upcoming_deadlines && recentData.upcoming_deadlines.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div 
                                v-for="task in recentData.upcoming_deadlines" 
                                :key="task.id"
                                class="p-5 bg-gradient-to-b from-white to-gray-50/50 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                            >
                                <div>
                                    <!-- Intern Info & Urgency Badge -->
                                    <div class="flex items-center justify-between gap-2 mb-3">
                                        <div class="flex items-center space-x-2.5">
                                            <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 font-extrabold flex items-center justify-center text-xs shadow-sm">
                                                {{ task.intern.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-900 leading-tight">{{ task.intern.name }}</p>
                                                <p class="text-[10px] text-gray-400 font-medium">{{ task.intern.department_label || 'Intern' }}</p>
                                            </div>
                                        </div>

                                        <!-- Urgency Badge -->
                                        <span 
                                            v-if="task.is_urgent"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200 shadow-sm"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping mr-1"></span>
                                            Due in {{ task.hours_remaining }}h
                                        </span>
                                        <span 
                                            v-else
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200"
                                        >
                                            Due in {{ task.hours_remaining }}h
                                        </span>
                                    </div>

                                    <!-- Task Title -->
                                    <h4 class="text-xs font-bold text-gray-800 line-clamp-2 mb-3 leading-snug" :title="task.title">
                                        {{ task.title }}
                                    </h4>
                                </div>

                                <!-- Card Footer: Deadline date & Quick Chat Shortcut Icon -->
                                <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                                    <div class="flex items-center space-x-1.5 text-[11px] text-gray-500 font-medium">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>{{ task.formatted_date }}</span>
                                    </div>

                                    <!-- Quick Chat with Intern Shortcut Icon -->
                                    <Link 
                                        :href="route('chat.index', { user_id: task.intern.id })"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shadow-sm group"
                                        :title="'Open chat conversation with ' + task.intern.name"
                                    >
                                        <svg class="w-3.5 h-3.5 text-indigo-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                        <span>Chat</span>
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State if no deadlines in 48h -->
                        <div v-else class="p-8 bg-gray-50/70 rounded-2xl border border-dashed border-gray-200 text-center">
                            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h4 class="text-sm font-bold text-gray-900">All submissions on schedule!</h4>
                            <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                                No pending tasks have deadlines approaching in the next 48 hours for your department interns.
                            </p>
                        </div>
                    </div>

                    <!-- PENDING SUBMISSIONS WAITING FOR REVIEW (If any) -->
                    <div v-if="recentData.recent_submissions && recentData.recent_submissions.length > 0" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">Submissions Ready for Review</h4>
                                    <p class="text-xs text-gray-500">Intern solutions recently submitted for your approval.</p>
                                </div>
                            </div>
                            <Link :href="route('mentor.tasks.history')" class="text-xs font-bold text-purple-600 hover:text-purple-800">
                                View All Submissions &rarr;
                            </Link>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <div v-for="sub in recentData.recent_submissions" :key="sub.id" class="py-3 flex items-center justify-between gap-4">
                                <div class="flex items-center space-x-3 min-w-0">
                                    <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-700 font-bold flex items-center justify-center text-xs shrink-0">
                                        {{ sub.intern?.name?.charAt(0) || 'I' }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-gray-900 truncate">{{ sub.title }}</p>
                                        <p class="text-[11px] text-gray-500">By {{ sub.intern?.name }}</p>
                                    </div>
                                </div>
                                <Link 
                                    :href="route('mentor.tasks.history')" 
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-purple-50 text-purple-700 hover:bg-purple-600 hover:text-white transition shrink-0"
                                >
                                    Review &rarr;
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- INTERN DASHBOARD METRICS -->
                <div v-if="user.role === 'intern'" class="space-y-8">
                    <!-- Metric Cards Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Total My Tasks -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Tasks</p>
                                    <h3 class="text-3xl font-extrabold text-gray-900 mt-2">{{ stats.total_my_tasks }}</h3>
                                </div>
                                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Tasks -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Pending Action</p>
                                    <h3 class="text-3xl font-extrabold text-amber-700 mt-2">{{ stats.pending_tasks }}</h3>
                                </div>
                                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Submitted -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Under Review</p>
                                    <h3 class="text-3xl font-extrabold text-purple-700 mt-2">{{ stats.submitted_tasks }}</h3>
                                </div>
                                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Approved -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Approved Tasks</p>
                                    <h3 class="text-3xl font-extrabold text-emerald-700 mt-2">{{ stats.approved_tasks }}</h3>
                                </div>
                                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Intern Action Card -->
                    <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Ready to submit your task solution?</h3>
                            <p class="text-sm text-gray-500 mt-1">View your assigned task instructions and submit your code explanation & links.</p>
                        </div>
                        <Link 
                            :href="route('intern.tasks.index')"
                            class="px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition-all shrink-0"
                        >
                            Open My Tasks Portal &rarr;
                        </Link>
                    </div>
                </div>

            </div>
        </div>

        <!-- Performance Report Modal -->
        <PerformanceReportModal
            :show="showReportModal"
            :report-data="reportData"
            :loading="loadingReport"
            @close="showReportModal = false"
        />
    </AuthenticatedLayout>
</template>
