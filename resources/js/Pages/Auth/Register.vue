<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
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

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const social = computed(() => $page.props.socialAuth || {});
const hasSocial = computed(() => social.value.google || social.value.facebook);
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <!-- Heading -->
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Create your account</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Save favorites, book tours &amp; get property updates.
        </p>

        <!-- Social signup -->
        <template v-if="hasSocial">
            <div class="mt-6">
                <SocialAuthButtons
                    :show-google="social.google"
                    :show-facebook="social.facebook"
                />
            </div>
            <div class="my-5 flex items-center gap-3">
                <span class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></span>
                <span class="text-xs text-gray-400">or sign up with email</span>
                <span class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></span>
            </div>
        </template>

        <form @submit.prevent="submit" class="mt-2 space-y-4">
            <!-- Account type -->
            <div>
                <InputLabel for="role" value="I am a..." />
                <select
                    id="role"
                    v-model="form.role"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                >
                    <option value="buyer">Buyer — looking for property</option>
                    <option value="agent">Agent — list &amp; manage properties for Edeni Realtors</option>
                    <option value="seller">Seller — selling my property</option>
                    <option value="tenant">Tenant — looking to rent</option>
                </select>
                <InputError class="mt-2" :message="form.errors.role" />
            </div>

            <!-- Agent-only fields -->
            <div v-if="isAgent" class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/20">
                <div class="mb-3 text-sm font-medium text-emerald-800 dark:text-emerald-300">
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
            <div>
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
            <div>
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
            <div>
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
            <div>
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

            <!-- Full-width primary CTA -->
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-xl bg-[#232126] px-6 py-3 text-sm font-bold text-[#A8E46A] shadow transition hover:bg-black disabled:opacity-50 dark:bg-[#A8E46A] dark:text-[#232126] dark:hover:brightness-110"
            >
                {{ form.processing ? 'Creating account…' : 'Create account' }}
            </button>
        </form>

        <!-- Login prompt -->
        <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
            Already registered?
            <Link
                :href="route('login')"
                class="font-semibold text-[#70A83C] underline-offset-2 hover:underline dark:text-[#A8E46A]"
            >
                Log in
            </Link>
        </p>
    </GuestLayout>
</template>
