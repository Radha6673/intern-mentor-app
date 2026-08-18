<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    interns: Array,
});

const searchQuery = ref('');

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
</script>

<template>
    <Head title="Registered Interns List" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Link :href="route('dashboard')" class="text-xs font-bold text-gray-500 hover:text-indigo-600 transition border border-gray-300 rounded-md px-3 py-1.5 bg-white shadow-sm">
                        &larr; Dashboard
                    </Link>
                    <div>
                        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                            Interns Directory
                        </h2>
                        <p class="text-xs text-gray-500 mt-1">
                            View registered interns, track task metrics, and assign new work.
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
                
                <!-- Search & Overview Stats Bar -->
                <div class="bg-white p-6 shadow sm:rounded-lg flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="w-full md:w-1/3">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Search Interns</label>
                        <div class="relative">
                            <input 
                                type="text"
                                v-model="searchQuery"
                                placeholder="Search by name or email..."
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 pl-9"
                            />
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 text-sm text-gray-600">
                        <div>
                            <span class="text-xs text-gray-400 block uppercase font-semibold">Total Registered Interns</span>
                            <span class="text-2xl font-extrabold text-indigo-600">{{ interns.length }}</span>
                        </div>
                    </div>
                </div>

                <!-- Interns Table -->
                <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Intern Details</th>
                                    <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Total Tasks</th>
                                    <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Pending</th>
                                    <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Submitted</th>
                                    <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Approved</th>
                                    <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Rejected</th>
                                    <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr 
                                    v-for="intern in filteredInterns" 
                                    :key="intern.id"
                                    class="hover:bg-gray-50 transition-colors"
                                >
                                    <!-- User Avatar & Info -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0 rounded-full bg-indigo-100 flex items-center justify-center font-bold text-indigo-700 text-sm">
                                                {{ intern.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-gray-900">{{ intern.name }}</div>
                                                <div class="text-xs text-gray-500">{{ intern.email }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Total Tasks Badge -->
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800">
                                            {{ intern.total_tasks }}
                                        </span>
                                    </td>

                                    <!-- Pending Tasks Badge -->
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span 
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                            :class="intern.pending_tasks > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-50 text-gray-400'"
                                        >
                                            {{ intern.pending_tasks }}
                                        </span>
                                    </td>

                                    <!-- Submitted Tasks Badge -->
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span 
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                            :class="intern.submitted_tasks > 0 ? 'bg-purple-100 text-purple-800' : 'bg-gray-50 text-gray-400'"
                                        >
                                            {{ intern.submitted_tasks }}
                                        </span>
                                    </td>

                                    <!-- Approved Tasks Badge -->
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span 
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                            :class="intern.approved_tasks > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-50 text-gray-400'"
                                        >
                                            {{ intern.approved_tasks }}
                                        </span>
                                    </td>

                                    <!-- Rejected Tasks Badge -->
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span 
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                            :class="intern.rejected_tasks > 0 ? 'bg-red-100 text-red-800' : 'bg-gray-50 text-gray-400'"
                                        >
                                            {{ intern.rejected_tasks }}
                                        </span>
                                    </td>

                                    <!-- Action -->
                                    <td class="px-6 py-4 text-right whitespace-nowrap text-sm font-medium">
                                        <Link 
                                            :href="route('mentor.tasks.index')"
                                            class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs underline"
                                        >
                                            Assign Task
                                        </Link>
                                    </td>
                                </tr>

                                <tr v-if="filteredInterns.length === 0">
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500 text-sm">
                                        No registered interns found matching your search.
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
