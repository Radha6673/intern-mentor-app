<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PerformanceReportModal from '@/Components/PerformanceReportModal.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    members: {
        type: Array,
        default: () => [],
    },
    userRole: {
        type: String,
        required: true,
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

const promoteToMentor = (member) => {
    if (confirm(`Are you sure you want to promote/change "${member.name}" (${member.email}) to a Mentor role?\n\nOnce changed, they will log in and have full Mentor access.`)) {
        router.post(route('admin.interns.promote', member.id), {}, {
            preserveScroll: true,
        });
    }
};

const pageTitle = computed(() => {
    if (props.userRole === 'intern') return 'My Mentors';
    if (props.userRole === 'mentor') return 'My Assigned Interns';
    return 'All Members';
});

const subTitle = computed(() => {
    if (props.userRole === 'intern') return 'Browse expert mentors by department and connect for technical guidance & reviews.';
    if (props.userRole === 'mentor') return 'Browse interns by technical department, track tasks, and collaborate.';
    return 'System member directory.';
});

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
    if (!dept) return 'General';
    const match = props.departments.find((d) => d.value === dept);
    return match ? match.label : dept.replace(/_/g, ' ');
};

const filteredMembers = computed(() => {
    return props.members.filter((member) => {
        const matchesDept = selectedDepartment.value === 'all' || member.department === selectedDepartment.value;
        const q = searchQuery.value.trim().toLowerCase();
        const matchesQuery = !q ||
            member.name?.toLowerCase().includes(q) ||
            member.email?.toLowerCase().includes(q) ||
            member.department?.toLowerCase().includes(q);
        return matchesDept && matchesQuery;
    });
});
</script>

<template>
    <Head :title="pageTitle" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 leading-tight">
                        {{ pageTitle }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ subTitle }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <button
                        v-if="userRole === 'admin' || userRole === 'mentor'"
                        type="button"
                        @click="openPerformanceReport()"
                        class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white transition shadow-sm shadow-purple-600/20 shrink-0 cursor-pointer"
                    >
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Performance Report
                    </button>
                    <div class="relative flex-1 sm:w-72">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by name, email or department..."
                            class="w-full pl-10 pr-4 py-2 text-xs rounded-xl border border-gray-200 bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500 transition shadow-sm"
                        />
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Department Filter Pill Bar -->
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mr-2">Filter by Department:</span>
                <button
                    type="button"
                    @click="selectedDepartment = 'all'"
                    :class="selectedDepartment === 'all' ? 'bg-indigo-600 text-white font-bold' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer"
                >
                    All ({{ members.length }})
                </button>
                <button
                    v-for="dept in departments"
                    :key="dept.value"
                    type="button"
                    @click="selectedDepartment = dept.value"
                    :class="selectedDepartment === dept.value ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/20' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer"
                >
                    {{ dept.label }} ({{ members.filter(m => m.department === dept.value).length }})
                </button>
            </div>

            <!-- Empty State -->
            <div v-if="filteredMembers.length === 0" class="text-center py-16 bg-white rounded-2xl border border-gray-100 shadow-sm">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-gray-800">No members found</h4>
                <p class="text-xs text-gray-500 mt-1">Try selecting a different department filter or search term.</p>
            </div>

            <!-- Members Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="member in filteredMembers"
                    :key="member.id"
                    class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition duration-200 flex flex-col justify-between group"
                >
                    <div>
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-600 text-white font-black text-lg flex items-center justify-center shadow-md shadow-indigo-500/20 group-hover:scale-105 transition-transform">
                                    {{ member.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 text-base leading-tight group-hover:text-indigo-600 transition-colors">
                                        {{ member.name }}
                                    </h3>
                                    <span class="inline-block px-2 py-0.5 mt-1 text-[10px] font-bold rounded-md uppercase tracking-wider"
                                          :class="{
                                              'bg-purple-100 text-purple-700': member.role === 'mentor',
                                              'bg-emerald-100 text-emerald-700': member.role === 'intern',
                                              'bg-amber-100 text-amber-700': member.role === 'admin',
                                          }">
                                        {{ member.role }}
                                    </span>
                                </div>
                            </div>

                            <!-- Department Badge -->
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border shrink-0"
                                :class="getDepartmentBadgeStyle(member.department)"
                            >
                                {{ getDepartmentLabel(member.department) }}
                            </span>
                        </div>

                        <p class="text-xs text-gray-500 flex items-center gap-2 mb-4">
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span class="truncate">{{ member.email }}</span>
                        </p>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-gray-400 font-medium shrink-0">
                            Joined {{ member.created_at ? new Date(member.created_at).toLocaleDateString() : 'Active' }}
                        </span>

                        <div class="flex items-center gap-1.5 flex-wrap justify-end">
                            <!-- Change to Mentor button (Admin Only for Interns) -->
                            <button
                                v-if="userRole === 'admin' && member.role === 'intern'"
                                type="button"
                                @click="promoteToMentor(member)"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold border border-purple-200 text-purple-700 bg-purple-50 hover:bg-purple-100 transition shadow-sm cursor-pointer"
                                title="Change role from Intern to Mentor"
                            >
                                <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                Make Mentor
                            </button>

                            <!-- Performance Report button for Interns -->
                            <button
                                v-if="member.role === 'intern' && (userRole === 'admin' || userRole === 'mentor')"
                                type="button"
                                @click="openPerformanceReport(member.id)"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition shadow-sm cursor-pointer"
                                title="View intern performance report"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Report
                            </button>

                            <Link
                                :href="route('chat.index', { user_id: member.id })"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 hover:bg-indigo-500 text-white transition shadow-sm shadow-indigo-600/30"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                Chat
                            </Link>
                        </div>
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
