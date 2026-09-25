<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Portal Login" />

        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Header Title -->
            <div class="text-center mb-6">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                    Welcome Back
                </h2>
            </div>

            <!-- Status Session Alert -->
            <div v-if="status" class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ status }}
            </div>

            <!-- Error Flash Alert -->
            <div v-if="$page.props.flash?.error" class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-700 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ $page.props.flash.error }}
            </div>

            <!-- Login Form -->
            <form @submit.prevent="submit" class="space-y-5">
                <!-- Email Field -->
                <div>
                    <InputLabel for="email" value="Email Address" class="text-xs font-bold uppercase text-gray-600 tracking-wider" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                        </div>
                        <TextInput
                            id="email"
                            type="email"
                            class="pl-10 block w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm transition-all"
                            v-model="form.email"
                            placeholder="name@example.com"
                            required
                            autofocus
                            autocomplete="username"
                        />
                    </div>
                    <InputError class="mt-1.5" :message="form.errors.email" />
                </div>

                <!-- Password Field -->
                <div>
                    <InputLabel for="password" value="Password" class="text-xs font-bold uppercase text-gray-600 tracking-wider" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <TextInput
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            class="pl-10 pr-10 block w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm transition-all"
                            v-model="form.password"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
                        >
                            <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a9.04 9.04 0 012.122-.363c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m-4.286-4.286a3 3 0 11-4.243-4.243"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"></path>
                            </svg>
                        </button>
                    </div>
                    <InputError class="mt-1.5" :message="form.errors.password" />
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center cursor-pointer select-none">
                        <Checkbox name="remember" v-model:checked="form.remember" class="rounded text-indigo-600 focus:ring-indigo-500" />
                        <span class="ms-2 text-gray-600 font-medium">Remember me</span>
                    </label>

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="font-semibold text-indigo-600 hover:text-indigo-800 transition-colors"
                    >
                        Forgot password?
                    </Link>
                </div>

                <!-- Submit Button -->
                <div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        :class="[
                            'w-full py-3 px-4 rounded-xl font-extrabold text-sm text-white shadow-lg transition-all duration-200 flex items-center justify-center gap-2',
                            form.processing
                                ? 'bg-indigo-400 cursor-not-allowed'
                                : 'bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 shadow-indigo-500/25 active:scale-[0.99]'
                        ]"
                    >
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ form.processing ? 'Authenticating...' : 'Sign In to Portal' }}</span>
                        <svg v-if="!form.processing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </div>

                <!-- Divider -->
                <div class="relative my-5">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white px-3 text-gray-400 font-bold tracking-wider">Or continue with</span>
                    </div>
                </div>

                <!-- Google OAuth Button -->
                <div>
                    <a
                        :href="route('auth.google')"
                        class="w-full py-3 px-4 rounded-xl font-bold text-sm text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 hover:border-gray-300 shadow-sm transition-all duration-200 flex items-center justify-center gap-3 active:scale-[0.99]"
                    >
                        <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                        </svg>
                        <span>Sign in with Google</span>
                    </a>
                </div>

                <!-- Register Link -->
                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-500">
                        Don't have an account yet?
                        <Link :href="route('register')" class="font-bold text-indigo-600 hover:text-indigo-500 ml-1 underline">
                            Create an account
                        </Link>
                    </p>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>

