<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SocialAuthButtons from '@/Components/SocialAuthButtons.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

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

const social = computed(() => $page.props.socialAuth || {});
const hasSocial = computed(() => social.value.google || social.value.facebook);
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <!-- Heading -->
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Welcome back</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Sign in to save favorites, book tours &amp; message agents.
        </p>

        <div v-if="status" class="mt-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <!-- Social login -->
        <template v-if="hasSocial">
            <div class="mt-6">
                <SocialAuthButtons
                    :show-google="social.google"
                    :show-facebook="social.facebook"
                />
            </div>
            <div class="my-5 flex items-center gap-3">
                <span class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></span>
                <span class="text-xs text-gray-400">or continue with email</span>
                <span class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></span>
            </div>
        </template>

        <form @submit.prevent="submit" class="mt-2 space-y-4">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <InputLabel for="password" value="Password" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs font-medium text-[#70A83C] underline-offset-2 hover:underline dark:text-[#A8E46A]"
                    >
                        Forgot password?
                    </Link>
                </div>
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <label class="flex items-center">
                <Checkbox name="remember" v-model:checked="form.remember" />
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-300">Remember me</span>
            </label>

            <!-- Full-width primary CTA -->
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-xl bg-[#232126] px-6 py-3 text-sm font-bold text-[#A8E46A] shadow transition hover:bg-black disabled:opacity-50 dark:bg-[#A8E46A] dark:text-[#232126] dark:hover:brightness-110"
            >
                {{ form.processing ? 'Signing in…' : 'Log in' }}
            </button>
        </form>

        <!-- Signup prompt -->
        <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
            Don't have an account?
            <Link
                :href="route('register')"
                class="font-semibold text-[#70A83C] underline-offset-2 hover:underline dark:text-[#A8E46A]"
            >
                Create one
            </Link>
            — it takes seconds.
        </p>
    </GuestLayout>
</template>
