<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';

const props = defineProps({
    mentors: Array,
});

// Form state for adding new mentor
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

// Submit handler
const submitMentor = () => {
    form.post(route('admin.mentors.store'), {
        onSuccess: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};

// Delete mentor handler
const deleteMentor = (mentor) => {
    if (confirm(`Are you sure you want to delete mentor "${mentor.name}"?`)) {
        router.delete(route('admin.mentors.destroy', mentor.id));
    }
};
</script>

<template>
    <Head title="Manage Mentors" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('dashboard')" class="text-xs font-bold text-gray-500 hover:text-indigo-600 transition border border-gray-300 rounded-md px-3 py-1.5 bg-white shadow-sm">
                        &larr; Back to Dashboard
                    </Link>
                    <h2 class="text-xl font-bold leading-tight text-gray-800">
                        Admin - Manage Mentors
                    </h2>
                </div>
                <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-800">
                    Total Mentors: {{ mentors.length }}
                </span>
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

                <!-- Add New Mentor Form -->
                <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="border-b border-gray-100 pb-4 mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Add New Mentor</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Fill in the mentor's details. Once added, the mentor can log into the application directly using their email and password.
                        </p>
                    </div>

                    <form @submit.prevent="submitMentor" class="space-y-4 max-w-2xl">
                        <!-- Mentor Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input
                                type="text"
                                v-model="form.name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="e.g. Rahul Sharma"
                                required
                            />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <!-- Mentor Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input
                                type="email"
                                v-model="form.email"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="e.g. mentor@company.com"
                                required
                            />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                        </div>

                        <!-- Password & Password Confirmation Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Password</label>
                                <input
                                    type="password"
                                    v-model="form.password"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="••••••••"
                                    required
                                />
                                <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                <input
                                    type="password"
                                    v-model="form.password_confirmation"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                            >
                                <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                {{ form.processing ? 'Adding Mentor...' : 'Add Mentor' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Existing Mentors Table -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg border border-gray-100 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Existing Mentors</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Joined Date</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="mentor in mentors" :key="mentor.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ mentor.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ mentor.email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ mentor.created_at ? new Date(mentor.created_at).toLocaleDateString() : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button
                                            @click="deleteMentor(mentor)"
                                            class="text-red-600 hover:text-red-900 focus:outline-none"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="mentors.length === 0">
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No mentors added yet. Add a mentor using the form above.
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
