<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    tasks: Array,
});

const searchQuery = ref('');
const statusFilter = ref('all');

// Inertia Task Review Form State
const reviewForm = useForm({
    status: '',
    feedback: '',
});

// Currently selected task for viewing/reviewing submission modal
const selectedTask = ref(null);

// Open Review / View Solution Modal
const openReviewModal = (task) => {
    selectedTask.value = task;
    reviewForm.feedback = task.submission && task.submission.feedback ? task.submission.feedback : '';
    reviewForm.status = '';
};

// Close Review Modal
const closeReviewModal = () => {
    selectedTask.value = null;
    reviewForm.reset();
};

// Submit Task Review Handler (Approve or Reject)
const submitReview = (statusChoice) => {
    reviewForm.status = statusChoice;
    reviewForm.post(route('mentor.tasks.review', selectedTask.value.id), {
        onSuccess: () => {
            closeReviewModal();
            alert(`Task submission ${statusChoice === 'approved' ? 'Approved' : 'Rejected'} successfully!`);
        },
    });
};

// Computed Filtered Tasks
const filteredTasks = computed(() => {
    return props.tasks.filter((task) => {
        const matchesSearch =
            task.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            task.intern.name.toLowerCase().includes(searchQuery.value.toLowerCase());

        if (!matchesSearch) return false;

        if (statusFilter.value === 'all') return true;
        if (statusFilter.value === 'reject') return task.status === 'reject' || task.status === 'rejected';
        return task.status === statusFilter.value;
    });
});
</script>

<template>
    <Head title="Assigned Tasks History & Submissions" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Link :href="route('dashboard')" class="text-xs font-bold text-gray-500 hover:text-indigo-600 transition border border-gray-300 rounded-md px-3 py-1.5 bg-white shadow-sm">
                        &larr; Dashboard
                    </Link>
                    <div>
                        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                            Task History & Submissions
                        </h2>
                        <p class="text-xs text-gray-500 mt-1">
                            View all tasks assigned to interns, check submissions, and review solutions.
                        </p>
                    </div>
                </div>

                <Link
                    :href="route('mentor.tasks.index')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 transition ease-in-out duration-150 shadow"
                >
                    + Assign New Task
                </Link>
            </div>
        </template>

        <div class="py-10">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Filters Bar -->
                <div class="bg-white p-6 shadow sm:rounded-lg flex flex-col md:flex-row items-center justify-between gap-4">
                    <!-- Search Input -->
                    <div class="w-full md:w-1/3">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Search Tasks</label>
                        <div class="relative">
                            <input 
                                type="text"
                                v-model="searchQuery"
                                placeholder="Search by title or intern..."
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 pl-9"
                            />
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Status Filter Tabs -->
                    <div class="w-full md:w-auto flex flex-wrap gap-2">
                        <button 
                            @click="statusFilter = 'all'"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold transition"
                            :class="statusFilter === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        >
                            All Tasks ({{ tasks.length }})
                        </button>
                        <button 
                            @click="statusFilter = 'submitted'"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold transition"
                            :class="statusFilter === 'submitted' ? 'bg-purple-600 text-white' : 'bg-purple-50 text-purple-700 hover:bg-purple-100'"
                        >
                            Submitted
                        </button>
                        <button 
                            @click="statusFilter = 'approved'"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold transition"
                            :class="statusFilter === 'approved' ? 'bg-green-600 text-white' : 'bg-green-50 text-green-700 hover:bg-green-100'"
                        >
                            Approved
                        </button>
                        <button 
                            @click="statusFilter = 'reject'"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold transition"
                            :class="statusFilter === 'reject' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100'"
                        >
                            Rejected
                        </button>
                        <button 
                            @click="statusFilter = 'pending'"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold transition"
                            :class="statusFilter === 'pending' ? 'bg-yellow-600 text-white' : 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100'"
                        >
                            Pending
                        </button>
                    </div>
                </div>

                <!-- Assigned Tasks Table -->
                <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Task Title</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Assigned To</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Deadline</th>
                                    <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="task in filteredTasks" :key="task.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-gray-900 text-sm">{{ task.title }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <div class="font-semibold text-gray-900">{{ task.intern.name }}</div>
                                        <div class="text-xs text-gray-500">{{ task.intern.email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span 
                                            class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full"
                                            :class="{
                                                'bg-yellow-100 text-yellow-800': task.status === 'pending',
                                                'bg-blue-100 text-blue-800': task.status === 'in_progress' || task.status === 'progress',
                                                'bg-purple-100 text-purple-800': task.status === 'submitted',
                                                'bg-green-100 text-green-800': task.status === 'approved',
                                                'bg-red-100 text-red-800': task.status === 'reject' || task.status === 'rejected',
                                            }"
                                        >
                                            {{ (task.status === 'reject' ? 'REJECTED' : task.status).replace('_', ' ').toUpperCase() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-500">
                                        {{ task.deadline ? new Date(task.deadline).toLocaleString() : 'No Deadline' }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <button 
                                            v-if="task.submission"
                                            @click="openReviewModal(task)"
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        >
                                            View Solution / Review
                                        </button>
                                        <span v-else class="text-xs text-gray-400 italic">No Submission</span>
                                    </td>
                                </tr>
                                <tr v-if="filteredTasks.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">
                                        No tasks found matching your filter criteria.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- SOLUTION & REVIEW MODAL -->
                <div v-if="selectedTask" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50 overflow-y-auto">
                    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6 space-y-4">
                        <div class="flex justify-between items-start border-b pb-3">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">
                                    Review Solution: {{ selectedTask.title }}
                                </h3>
                                <p class="text-xs text-gray-500 mt-1">
                                    Submitted by: <span class="font-semibold text-gray-700">{{ selectedTask.intern.name }}</span>
                                </p>
                            </div>
                            <span 
                                class="px-3 py-1 text-xs font-semibold rounded-full"
                                :class="{
                                    'bg-yellow-100 text-yellow-800': selectedTask.status === 'pending',
                                    'bg-purple-100 text-purple-800': selectedTask.status === 'submitted',
                                    'bg-green-100 text-green-800': selectedTask.status === 'approved',
                                    'bg-red-100 text-red-800': selectedTask.status === 'reject' || selectedTask.status === 'rejected',
                                }"
                            >
                                {{ (selectedTask.status === 'reject' ? 'REJECTED' : selectedTask.status).toUpperCase() }}
                            </span>
                        </div>

                        <!-- Task Details -->
                        <div class="bg-gray-50 p-4 rounded-md text-sm text-gray-700 space-y-1">
                            <p class="font-semibold text-gray-900">Task Requirements:</p>
                            <p class="whitespace-pre-line text-gray-600">{{ selectedTask.description }}</p>
                        </div>

                        <!-- Submission Content -->
                        <div v-if="selectedTask.submission" class="space-y-3">
                            <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-md space-y-3">
                                <div>
                                    <h4 class="text-xs font-bold text-indigo-900 uppercase tracking-wider">Solution Explanation</h4>
                                    <p class="text-sm text-gray-800 mt-1 whitespace-pre-line">{{ selectedTask.submission.explanation }}</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 border-t border-indigo-100 text-xs">
                                    <div>
                                        <span class="font-bold text-indigo-900">Tech Stack:</span>
                                        <p class="text-gray-700 font-medium">{{ selectedTask.submission.tech_stack }}</p>
                                    </div>
                                    <div v-if="selectedTask.submission.github_link">
                                        <span class="font-bold text-indigo-900">Repository / Demo:</span>
                                        <p>
                                            <a 
                                                :href="selectedTask.submission.github_link" 
                                                target="_blank" 
                                                class="text-indigo-600 underline font-medium hover:text-indigo-800 break-all"
                                            >
                                                {{ selectedTask.submission.github_link }}
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Mentor Feedback Input -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mentor Feedback / Remarks (Optional)</label>
                                <textarea 
                                    v-model="reviewForm.feedback" 
                                    rows="3" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    placeholder="Add feedback or suggestions for the intern..."
                                ></textarea>
                            </div>
                        </div>

                        <div v-else class="py-4 text-center text-gray-500">
                            No submission details found.
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-between items-center pt-4 border-t">
                            <button 
                                type="button" 
                                @click="closeReviewModal"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-semibold py-2 px-4 rounded"
                            >
                                Close
                            </button>

                            <div v-if="selectedTask.submission" class="flex gap-3">
                                <button 
                                    type="button" 
                                    @click="submitReview('reject')"
                                    :disabled="reviewForm.processing"
                                    class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2 px-4 rounded shadow disabled:opacity-50"
                                >
                                    Reject Solution
                                </button>
                                <button 
                                    type="button" 
                                    @click="submitReview('approved')"
                                    :disabled="reviewForm.processing"
                                    class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-2 px-4 rounded shadow disabled:opacity-50"
                                >
                                    Approve Solution
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
