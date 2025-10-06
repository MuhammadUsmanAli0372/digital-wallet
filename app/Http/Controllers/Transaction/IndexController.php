<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();

        // Fetch transactions related to this user (sent or received)
        $transactions = Transaction::with(['sender', 'receiver'])
            ->where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->latest()
            ->get();

        return Inertia::render('Transaction', [
            'transactions' => $transactions,
            'balance' => $user->balance, // assuming you have a balance column
        ]);
    }
}
