<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    recentData: Object,
    members: {
        type: Array,
        default: () => [],
    },
});

const user = computed(() => usePage().props.auth.user);

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
                                </div>
                                <p class="text-sm text-slate-300 font-medium">
                                    Here is what's happening across your platform workspace today.
                                </p>
                            </div>
                        </div>

                        <!-- Quick Navigation Action in Hero -->
                        <div class="flex flex-wrap gap-3 w-full md:w-auto">
                            <!-- Role-based Members Directory Link -->
                            <Link 
                                :href="route('members.index')"
                                class="inline-flex items-center px-4 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-500 text-white transition-all duration-200 shadow-lg shadow-indigo-600/30"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                {{ user.role === 'intern' ? 'My Mentors' : (user.role === 'mentor' ? 'My Interns' : 'Members Directory') }}
                            </Link>

                            <!-- Admin Quick Action -->
                            <Link 
                                v-if="user.role === 'admin'"
                                :href="route('admin.mentors.index')"
                                class="inline-flex items-center px-4 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white transition-all duration-200 shadow-lg shadow-emerald-600/30"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Manage Mentors
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- ADMIN DASHBOARD METRICS -->
                <div v-if="user.role === 'admin'" class="space-y-8">
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
                                <span class="text-xs text-gray-400 font-medium">Platform Mentors</span>
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
                            <div class="mt-4 pt-3 border-t border-gray-100">
                                <span class="text-xs text-gray-400 font-medium">Active Platform Users</span>
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

                    <!-- Quick Navigation Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <Link :href="route('mentor.tasks.index')" class="group p-6 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all">
                            <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>  
                             
                               
                            <h4 class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">Assign New Task</h4>
                            <p class="text-xs text-gray-500 mt-1">Create new task assignments for interns with deadline.</p>
                        </Link>

                        <Link :href="route('mentor.tasks.history')" class="group p-6 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all">
                            <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <h4 class="font-bold text-gray-900 group-hover:text-purple-600 transition-colors">Task History & Submissions</h4>
                            <p class="text-xs text-gray-500 mt-1">Review intern solution submissions & approve or reject with feedback.</p>
                        </Link>

                        <Link :href="route('members.index')" class="group p-6 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all">
                            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h4 class="font-bold text-gray-900 group-hover:text-emerald-600 transition-colors">Interns Directory</h4>
                            <p class="text-xs text-gray-500 mt-1">View assigned interns list and role members.</p>
                        </Link>
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

                        <!-- Approved Tasks -->
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
    </AuthenticatedLayout>
</template>
