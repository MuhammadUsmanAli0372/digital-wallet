<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, usePage, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

// ✅ Get data from backend
const { props } = usePage()
const user = props.auth?.user || {}
const transactions = ref(props.transactions || [])
const balance = ref(props.balance || 0)

// 🔩 Modal visibility
const showModal = ref(false);

// 🧾 Form state
const form = useForm({
    receiver_id: '',
    amount: '',
});

// 📤 Handle form submission
const submit = () => {
    form.post(route('transactions.store'), {
        onSuccess: () => {
            toast.success('💸 Transaction successful!', { autoClose: 3000 })
            form.reset()
            showModal.value = false;
        },
        onError: () => {
            toast.error('❌ Transaction failed. Please try again.', { autoClose: 3000 })
        },
    })
}
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
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
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

                            <!-- Sender -->
                            <td class="px-4 py-2 text-sm">
                                {{ t.sender_id === user.id ? 'You' : t.sender?.name }}
                            </td>

                            <!-- Receiver -->
                            <td class="px-4 py-2 text-sm">
                                {{ t.receiver_id === user.id ? 'You' : t.receiver?.name }}
                            </td>

                            <!-- Amount -->
                            <td
                                class="px-4 py-2 text-sm font-medium"
                                :class="{
                                    'text-red-600': t.sender_id === user.id,
                                    'text-green-600': t.receiver_id === user.id,
                                }"
                            >
                                {{ t.sender_id === user.id ? '-' : '+' }} Rs. {{ t.amount }}
                            </td>

                            <!-- Type -->
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

                            <!-- Date -->
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
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            required
                        />
                        <div v-if="form.errors.receiver_id" class="text-sm text-red-600">
                            {{ form.errors.receiver_id }}
                        </div>
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount</label>
                        <input
                            v-model="form.amount"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            required
                        />
                        <div v-if="form.errors.amount" class="text-sm text-red-600">
                            {{ form.errors.amount }}
                        </div>
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
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                        >
                            {{ form.processing ? 'Sending...' : 'Send' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
