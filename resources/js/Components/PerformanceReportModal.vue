<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    reportData: Object,
    loading: Boolean,
});

const emit = defineEmits(['close']);

const internalSelectedReport = ref(null);

watch(() => props.show, (newVal) => {
    if (!newVal) {
        internalSelectedReport.value = null;
    }
});

watch(() => props.reportData, () => {
    internalSelectedReport.value = null;
});

const activeReport = computed(() => {
    return internalSelectedReport.value || props.reportData;
});

const isSingleReport = computed(() => {
    return activeReport.value && activeReport.value.intern;
});

const isTeamReport = computed(() => {
    return !internalSelectedReport.value && props.reportData && props.reportData.summary;
});

const getCompletionColorClass = (rateNum) => {
    if (rateNum >= 75) return 'text-emerald-600 bg-emerald-50 border-emerald-200';
    if (rateNum >= 50) return 'text-amber-600 bg-amber-50 border-amber-200';
    return 'text-rose-600 bg-rose-50 border-rose-200';
};

const getProgressBarColor = (rateNum) => {
    if (rateNum >= 75) return 'bg-emerald-500';
    if (rateNum >= 50) return 'bg-amber-500';
    return 'bg-rose-500';
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'approved':
            return { label: 'Approved', class: 'bg-emerald-100 text-emerald-800 border-emerald-200' };
        case 'submitted':
            return { label: 'Submitted', class: 'bg-purple-100 text-purple-800 border-purple-200' };
        case 'pending':
        case 'in_progress':
            return { label: 'Pending', class: 'bg-amber-100 text-amber-800 border-amber-200' };
        case 'reject':
        case 'rejected':
            return { label: 'Rejected', class: 'bg-rose-100 text-rose-800 border-rose-200' };
        default:
            return { label: status, class: 'bg-gray-100 text-gray-800' };
    }
};

const printReport = () => {
    window.print();
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity"
        @click.self="emit('close')"
    >
        <div class="relative w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 transform transition-all my-8 max-h-[90vh] flex flex-col">
            
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 text-white p-6 shrink-0 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2.5 bg-white/10 rounded-xl backdrop-blur-md border border-white/10">
                        <svg class="w-6 h-6 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold tracking-tight">
                            {{ isSingleReport ? 'Individual Intern Performance Report' : 'Team Performance Overview' }}
                        </h2>
                        <p class="text-xs text-indigo-200 mt-0.5" v-if="isSingleReport && activeReport?.intern">
                            Comprehensive task metrics & completion report for <span class="font-semibold text-white underline">{{ activeReport.intern.name }}</span>
                        </p>
                        <p class="text-xs text-indigo-200 mt-0.5" v-else-if="isTeamReport">
                            Combined performance summary across all registered interns
                        </p>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <button
                        v-if="internalSelectedReport"
                        type="button"
                        @click="internalSelectedReport = null"
                        class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-bold flex items-center transition border border-white/10 cursor-pointer"
                        title="Back to team overview"
                    >
                        &larr; Team Overview
                    </button>
                    <button
                        @click="printReport"
                        class="p-2 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-semibold flex items-center transition border border-white/10 cursor-pointer"
                        title="Print Report"
                    >
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print
                    </button>
                    <button
                        @click="emit('close')"
                        class="p-2 rounded-lg bg-white/10 hover:bg-white/20 text-white transition border border-white/10 cursor-pointer"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="p-12 text-center flex-1 flex flex-col items-center justify-center">
                <svg class="animate-spin h-10 w-10 text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-sm font-medium text-gray-600">Generating live performance metrics report...</p>
            </div>

            <!-- Report Body -->
            <div v-else-if="activeReport" class="p-6 overflow-y-auto space-y-6 flex-1">
                
                <!-- Single Intern Report View -->
                <template v-if="isSingleReport">
                    
                    <!-- Intern Header Info & Completion Gauge Card -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2 bg-slate-50 border border-slate-200 rounded-xl p-5 flex items-center space-x-4">
                            <div class="w-14 h-14 rounded-full bg-indigo-600 text-white text-xl font-bold flex items-center justify-center shadow-md">
                                {{ activeReport.intern.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ activeReport.intern.name }}</h3>
                                <p class="text-xs text-gray-500 font-medium">{{ activeReport.intern.email }}</p>
                                <div class="mt-2 inline-flex items-center text-[11px] font-semibold text-gray-500 bg-white border border-gray-200 rounded-md px-2.5 py-1">
                                    <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Joined: {{ activeReport.intern.joined_at }}
                                </div>
                            </div>
                        </div>

                        <!-- Completion Rate Highlight -->
                        <div class="border rounded-xl p-5 flex flex-col justify-between shadow-sm" :class="getCompletionColorClass(activeReport.completion_rate_num)">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider">Completion Rate</span>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded border" :class="getCompletionColorClass(activeReport.completion_rate_num)">
                                    {{ activeReport.completion_rate_num >= 75 ? 'Excellent' : (activeReport.completion_rate_num >= 50 ? 'Good' : 'Needs Improvement') }}
                                </span>
                            </div>
                            <div class="my-2 flex items-baseline">
                                <span class="text-4xl font-black tracking-tight">{{ activeReport.completion_rate }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                <div class="h-2 rounded-full transition-all duration-500" :class="getProgressBarColor(activeReport.completion_rate_num)" :style="{ width: activeReport.completion_rate_num + '%' }"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Metrics Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-3.5 text-center">
                            <span class="block text-xs font-bold text-gray-500 uppercase">Total Tasks</span>
                            <span class="text-2xl font-black text-gray-900 mt-1 block">{{ activeReport.total_tasks }}</span>
                        </div>

                        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3.5 text-center">
                            <span class="block text-xs font-bold text-emerald-700 uppercase">Approved</span>
                            <span class="text-2xl font-black text-emerald-800 mt-1 block">{{ activeReport.approved_tasks }}</span>
                        </div>

                        <div class="bg-purple-50 border border-purple-200 rounded-xl p-3.5 text-center">
                            <span class="block text-xs font-bold text-purple-700 uppercase">Submitted</span>
                            <span class="text-2xl font-black text-purple-800 mt-1 block">{{ activeReport.submitted_tasks }}</span>
                        </div>

                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3.5 text-center">
                            <span class="block text-xs font-bold text-amber-700 uppercase">Pending</span>
                            <span class="text-2xl font-black text-amber-800 mt-1 block">{{ activeReport.pending_tasks }}</span>
                        </div>

                        <div class="bg-rose-50 border border-rose-200 rounded-xl p-3.5 text-center">
                            <span class="block text-xs font-bold text-rose-700 uppercase">Rejected</span>
                            <span class="text-2xl font-black text-rose-800 mt-1 block">{{ activeReport.rejected_tasks }}</span>
                        </div>
                    </div>

                    <!-- Assigned Tasks Table -->
                    <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                            <h4 class="font-bold text-sm text-gray-800">Task Log History ({{ activeReport.tasks.length }})</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-xs">
                                <thead class="bg-gray-100 text-gray-600 font-bold uppercase">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left">Task Title</th>
                                        <th class="px-4 py-2.5 text-left">Mentor</th>
                                        <th class="px-4 py-2.5 text-center">Status</th>
                                        <th class="px-4 py-2.5 text-left">Deadline</th>
                                        <th class="px-4 py-2.5 text-left">Assigned Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <tr v-for="task in activeReport.tasks" :key="task.id" class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-semibold text-gray-900">{{ task.title }}</td>
                                        <td class="px-4 py-3 text-gray-600 font-medium">{{ task.mentor_name }}</td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border" :class="getStatusBadge(task.status).class">
                                                {{ getStatusBadge(task.status).label }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-500">{{ task.deadline }}</td>
                                        <td class="px-4 py-3 text-gray-500">{{ task.created_at }}</td>
                                    </tr>
                                    <tr v-if="activeReport.tasks.length === 0">
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-400">No tasks assigned yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </template>

                <!-- Team Performance Summary Report View -->
                <template v-else-if="isTeamReport">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 text-center">
                            <span class="text-xs font-bold text-indigo-700 uppercase">Total Registered Interns</span>
                            <span class="text-3xl font-black text-indigo-900 mt-1 block">{{ reportData.summary.total_interns }}</span>
                        </div>

                        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center">
                            <span class="text-xs font-bold text-emerald-700 uppercase">Avg Completion Rate</span>
                            <span class="text-3xl font-black text-emerald-900 mt-1 block">{{ reportData.summary.avg_completion_rate }}</span>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
                            <span class="text-xs font-bold text-blue-700 uppercase">Total Tasks Assigned</span>
                            <span class="text-3xl font-black text-blue-900 mt-1 block">{{ reportData.summary.total_tasks_assigned }}</span>
                        </div>

                        <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 text-center">
                            <span class="text-xs font-bold text-purple-700 uppercase">Total Approved Tasks</span>
                            <span class="text-3xl font-black text-purple-900 mt-1 block">{{ reportData.summary.total_approved_tasks }}</span>
                        </div>
                    </div>

                    <!-- All Interns Performance Table -->
                    <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                            <h4 class="font-bold text-sm text-gray-800">All Interns Performance Metrics</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-xs">
                                <thead class="bg-gray-100 text-gray-600 font-bold uppercase">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Intern</th>
                                        <th class="px-4 py-3 text-center">Total Tasks</th>
                                        <th class="px-4 py-3 text-center">Approved</th>
                                        <th class="px-4 py-3 text-center">Submitted</th>
                                        <th class="px-4 py-3 text-center">Pending</th>
                                        <th class="px-4 py-3 text-center">Rejected</th>
                                        <th class="px-4 py-3 text-center">Completion Rate</th>
                                        <th class="px-4 py-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <tr v-for="rep in reportData.reports" :key="rep.intern.id" class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-gray-900">{{ rep.intern.name }}</div>
                                            <div class="text-[10px] text-gray-500">{{ rep.intern.email }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center font-semibold">{{ rep.total_tasks }}</td>
                                        <td class="px-4 py-3 text-center font-semibold text-emerald-600">{{ rep.approved_tasks }}</td>
                                        <td class="px-4 py-3 text-center font-semibold text-purple-600">{{ rep.submitted_tasks }}</td>
                                        <td class="px-4 py-3 text-center font-semibold text-amber-600">{{ rep.pending_tasks }}</td>
                                        <td class="px-4 py-3 text-center font-semibold text-rose-600">{{ rep.rejected_tasks }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2.5 py-1 rounded font-bold border text-xs" :class="getCompletionColorClass(rep.completion_rate_num)">
                                                {{ rep.completion_rate }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <button
                                                type="button"
                                                @click="internalSelectedReport = rep"
                                                class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg text-[11px] font-bold transition cursor-pointer"
                                            >
                                                View Detail &rarr;
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </template>

            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between shrink-0">
                <div class="text-xs text-gray-500 font-medium flex items-center">
                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Report Generated At: <span class="font-semibold text-gray-700 ml-1">{{ activeReport ? activeReport.generated_at || (activeReport.summary ? activeReport.summary.generated_at : '') : '' }}</span>
                </div>
                <button
                    @click="emit('close')"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg text-xs font-bold transition shadow-sm"
                >
                    Close Report
                </button>
            </div>

        </div>
    </div>
</template>
