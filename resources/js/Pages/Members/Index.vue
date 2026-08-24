<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    members: {
        type: Array,
        default: () => [],
    },
    userRole: {
        type: String,
        required: true,
    },
});

const searchQuery = ref('');

const pageTitle = computed(() => {
    if (props.userRole === 'intern') return 'My Mentors';
    if (props.userRole === 'mentor') return 'My Assigned Interns';
    return 'All Members';
});

const subTitle = computed(() => {
    if (props.userRole === 'intern') return 'List of available mentors for guidance and communication.';
    if (props.userRole === 'mentor') return 'List of assigned interns under your mentorship.';
    return 'System member directory.';
});

const filteredMembers = computed(() => {
    if (!searchQuery.value.trim()) return props.members;
    const q = searchQuery.value.toLowerCase();
    return props.members.filter(
        (member) =>
            member.name?.toLowerCase().includes(q) ||
            member.email?.toLowerCase().includes(q)
    );
});
</script>

<template>
    <Head :title="pageTitle" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 leading-tight">
                        {{ pageTitle }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ subTitle }}
                    </p>
                </div>
                <div class="relative w-full sm:w-64">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search members..."
                        class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition"
                    />
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </template>

        <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div v-if="filteredMembers.length === 0" class="text-center py-12 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-gray-500 dark:text-gray-400 font-medium">No members found</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="member in filteredMembers"
                    :key="member.id"
                    class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition duration-200 flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold text-lg flex items-center justify-center">
                                {{ member.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-lg">
                                    {{ member.name }}
                                </h3>
                                <span class="inline-block px-2.5 py-0.5 text-xs font-semibold rounded-full capitalize"
                                      :class="{
                                          'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300': member.role === 'mentor',
                                          'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300': member.role === 'intern',
                                          'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300': member.role === 'admin',
                                      }">
                                    {{ member.role }}
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300 flex items-center gap-2 mb-4">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 002-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ member.email }}
                        </p>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700/50 flex justify-end">
                        <Link
                            :href="route('chat.index', { user_id: member.id })"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white transition shadow-sm"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            Message
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
