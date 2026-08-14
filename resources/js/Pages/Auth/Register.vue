<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SocialAuthButtons from '@/Components/SocialAuthButtons.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'buyer',
    company_name: '',
    phone: '',
    license_number: '',
});

const isAgent = computed(() => form.role === 'agent');

const social = computed(() => $page.props.socialAuth || {});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <!-- Social signup (Google / Facebook) -->
        <template v-if="social.google || social.facebook">
            <SocialAuthButtons
                :show-google="social.google"
                :show-facebook="social.facebook"
            />
            <div class="my-5 flex items-center gap-3">
                <span class="h-px flex-1 bg-gray-200 dark:bg-gray-600"></span>
                <span class="text-xs text-gray-400">or sign up with email</span>
                <span class="h-px flex-1 bg-gray-200 dark:bg-gray-600"></span>
            </div>
        </template>

        <form @submit.prevent="submit">
            <!-- Account type -->
            <div class="mt-4">
                <InputLabel for="role" value="I am a..." />

                <select
                    id="role"
                    v-model="form.role"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                >
                    <option value="buyer">Buyer — looking for property</option>
                    <option value="agent">Agent — list &amp; manage properties for Edeni Realtors</option>
                    <option value="seller">Seller — selling my property</option>
                    <option value="tenant">Tenant — looking to rent</option>
                </select>
                <InputError class="mt-2" :message="form.errors.role" />
            </div>

            <!-- Agent-only fields -->
            <div v-if="isAgent" class="mt-4 rounded-lg border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 p-4">
                <div class="text-sm font-medium text-emerald-800 dark:text-emerald-300 mb-3">
                    🏢 Agent details — you'll operate under Edeni Realtors (company-owned listings)
                </div>

                <div>
                    <InputLabel for="company_name" value="Company name (optional)" />
                    <TextInput
                        id="company_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.company_name"
                        autocomplete="organization"
                    />
                    <InputError class="mt-2" :message="form.errors.company_name" />
                </div>

                <div class="mt-4">
                    <InputLabel for="phone" value="Phone (WhatsApp number)" />
                    <TextInput
                        id="phone"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.phone"
                        placeholder="+255..."
                        autocomplete="tel"
                    />
                    <InputError class="mt-2" :message="form.errors.phone" />
                </div>

                <div class="mt-4">
                    <InputLabel for="license_number" value="License / BRELA number (optional)" />
                    <TextInput
                        id="license_number"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.license_number"
                    />
                    <InputError class="mt-2" :message="form.errors.license_number" />
                </div>
            </div>

            <!-- Name -->
            <div class="mt-4">
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <!-- Email -->
            <div class="mt-4">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-gray-600 dark:text-gray-400 underline hover:text-gray-900 dark:hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                >
                    Already registered?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
