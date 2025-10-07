<script setup>
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import { onMounted, onBeforeUnmount, ref, watch, computed } from 'vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// ✅ Get data from backend
const { props } = usePage();
const user = props.auth?.user || {};
const transactions = ref(props.transactions || []);
const balance = ref(props.balance || 0);

// 🧠 Real-time updates via Echo
onMounted(() => {
    const channel = window.Echo.private(`transactions.${user.id}`);

    channel.listen('TransactionCreated', (event) => {
        if (event.balance) {
            if (event.balance.sender && event.transaction.sender_id === user.id) {
                balance.value = event.balance.sender;
            } else if (event.balance.receiver && event.transaction.receiver_id === user.id) {
                balance.value = event.balance.receiver;
            }
        }

        transactions.value.unshift(event.transaction);

        if (event.transaction.receiver_id === user.id) {
            toast.success(`💰 You received Rs. ${event.transaction.amount} from ${event.transaction.sender.name}`);
        } else if (event.transaction.sender_id === user.id) {
            toast.info(`📤 You sent Rs. ${event.transaction.amount} to ${event.transaction.receiver.name}`);
        }
    });
});

onBeforeUnmount(() => {
    window.Echo.leave(`transactions.${user.id}`);
});

// 🔩 Modal visibility
const showModal = ref(false);

// 🧾 Form state
const form = useForm({
    receiver_id: '',
    amount: '',
});

// 🧮 Real-time form validation states
const errors = ref({
    receiver_id: '',
    amount: '',
});

// ✅ Validation rules
const validateForm = () => {
    errors.value = { receiver_id: '', amount: '' };

    if (!form.receiver_id) {
        errors.value.receiver_id = 'Receiver ID is required.';
    } else if (form.receiver_id == user.id) {
        errors.value.receiver_id = 'You cannot send money to yourself.';
    }

    if (!form.amount) {
        errors.value.amount = 'Amount is required.';
    } else if (isNaN(form.amount) || Number(form.amount) <= 0) {
        errors.value.amount = 'Enter a valid positive amount.';
    } else if (Number(form.amount) > balance.value) {
        errors.value.amount = 'Insufficient balance.';
    }

    return !errors.value.receiver_id && !errors.value.amount;
};

// 💸 Formatted currency preview
const formattedAmount = computed(() => {
    if (!form.amount) return '';
    return new Intl.NumberFormat('en-PK', {
        style: 'currency',
        currency: 'PKR',
        minimumFractionDigits: 2,
    }).format(form.amount);
});

// 📤 Handle form submission
const submit = () => {
    if (!validateForm()) {
        toast.error('⚠️ Please fix the highlighted errors.');
        return;
    }

    form.post(route('transactions.store'), {
        onSuccess: () => {
            toast.success('💸 Transaction successful!', { autoClose: 3000 });
            form.reset();
            showModal.value = false;
        },
        onError: () => {
            toast.error('❌ Transaction failed. Please try again.', { autoClose: 3000 });
        },
    });
};
</script>

<template>
    <Head title="Transactions" />

    <AuthenticatedLayout>
        <!-- 🧭 Header -->
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Transactions</h2>
                <button
                    @click="showModal = true"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition"
                >
                    + New Transaction
                </button>
            </div>
        </template>

        <div class="py-10 max-w-5xl mx-auto space-y-8">
            <!-- 💰 Current Balance -->
            <div class="bg-white shadow-sm rounded-lg p-6 text-gray-800">
                <h3 class="text-lg font-semibold mb-2">Current Balance</h3>
                <p class="text-3xl font-bold text-green-600">
                    Rs. {{ Number(balance).toLocaleString() }}
                </p>
            </div>

            <!-- 📜 Transaction History -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Transaction History</h3>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">#</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Sender</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Receiver</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Amount</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Type</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Date</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="(t, index) in transactions" :key="t.id">
                            <td class="px-4 py-2 text-sm">{{ index + 1 }}</td>
                            <td class="px-4 py-2 text-sm">
                                {{ t.sender_id === user.id ? 'You' : t.sender?.name }}
                            </td>
                            <td class="px-4 py-2 text-sm">
                                {{ t.receiver_id === user.id ? 'You' : t.receiver?.name }}
                            </td>
                            <td
                                class="px-4 py-2 text-sm font-medium"
                                :class="{
                                    'text-red-600': t.sender_id === user.id,
                                    'text-green-600': t.receiver_id === user.id,
                                }"
                            >
                                {{ t.sender_id === user.id ? '-' : '+' }} Rs. {{ t.amount }}
                            </td>
                            <td class="px-4 py-2 text-sm">
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-semibold"
                                    :class="{
                                        'bg-green-100 text-green-700': t.receiver_id === user.id,
                                        'bg-red-100 text-red-700': t.sender_id === user.id,
                                    }"
                                >
                                    {{ t.receiver_id === user.id ? 'Received' : 'Sent' }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-500">
                                {{ new Date(t.created_at).toLocaleString() }}
                            </td>
                        </tr>

                        <tr v-if="transactions.length === 0">
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                No transactions yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 💸 Transaction Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50"
        >
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md relative">
                <button
                    @click="showModal = false"
                    class="absolute top-2 right-2 text-gray-400 hover:text-gray-600"
                >
                    ✕
                </button>

                <h3 class="text-lg font-semibold mb-4">Send Money</h3>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Receiver ID -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Receiver ID</label>
                        <input
                            v-model="form.receiver_id"
                            type="number"
                            class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            required
                        />
                        <p v-if="errors.receiver_id" class="text-sm text-red-600 mt-1">
                            {{ errors.receiver_id }}
                        </p>
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount</label>
                        <input
                            v-model="form.amount"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            required
                        />
                        <p v-if="formattedAmount" class="text-sm text-gray-500 mt-1">
                            ({{ formattedAmount }})
                        </p>
                        <p v-if="errors.amount" class="text-sm text-red-600 mt-1">
                            {{ errors.amount }}
                        </p>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-end space-x-2 mt-6">
                        <button
                            type="button"
                            @click="showModal = false"
                            class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Sending...' : 'Send' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
