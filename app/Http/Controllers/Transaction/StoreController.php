<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'amount' => ['required','numeric','gt:0'],
        ]);

        $sender = $request->user();
        $receiverId = (int) $validated['receiver_id'];

        if ($receiverId === $sender->id) {
            throw ValidationException::withMessages(['receiver_id' => 'You cannot send money to yourself.']);
        }

        $amount = round((float) $validated['amount'], 2);
        $commission = round($amount * 0.015, 2); // 1.5%
        $totalDebit = round($amount + $commission, 2);

        if ($sender->balance < $totalDebit) {
            return redirect()->back()->withErrors([
                'transactions' => 'Insufficient balance'
            ]);
        }

        try {
            DB::transaction(function () use ($sender, $receiverId, $amount, $commission, $totalDebit) {
                // Lock sender row for update
                $sender = User::where('id', $sender->id)->lockForUpdate()->first();

                if ($sender->balance < $totalDebit) {
                    throw ValidationException::withMessages([
                        'balance' => 'Insufficient balance',
                    ]);
                }

                // Lock receiver row for update
                $receiver = User::where('id', $receiverId)->lockForUpdate()->first();

                // Debit sender
                $sender->balance -= $totalDebit;
                $sender->save();

                // Credit receiver
                $receiver->balance += $amount;
                $receiver->save();

                // Record transaction
                Transaction::create([
                    'sender_id'      => $sender->id,
                    'receiver_id'    => $receiver->id,
                    'amount'         => $amount,
                    'commission_fee' => $commission,
                    'status'         => 'completed',
                ]);
            });

            return redirect()->back()->with('success', 'Transaction successful');
        } catch (ValidationException $e) {
            throw $e; // Inertia will handle and call onError
        } catch (Exception $e) {
            return redirect()->back()->withErrors([
                'transactions' => 'transactions failed. Please try again.'
            ]);
        }
    }
}
