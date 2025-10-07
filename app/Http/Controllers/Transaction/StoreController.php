<?php

namespace App\Http\Controllers\Transaction;

use App\Events\TransactionCreated;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $sender = $request->user();
        $receiverId = (int) $validated['receiver_id'];

        if ($receiverId === $sender->id) {
            throw ValidationException::withMessages([
                'receiver_id' => 'You cannot send money to yourself.',
            ]);
        }

        $amount = round((float) $validated['amount'], 2);
        $commission = round($amount * 0.015, 2);
        $totalDebit = round($amount + $commission, 2);

        // Pre-check to avoid unnecessary DB locks if already insufficient
        if ($sender->balance < $totalDebit) {
            return back()->withErrors(['transactions' => 'Insufficient balance.']);
        }

        try {
            $transaction = DB::transaction(function () use ($sender, $receiverId, $amount, $commission, $totalDebit) {
                // Lock sender row
                $lockedSender = User::where('id', $sender->id)->lockForUpdate()->first();

                if ($lockedSender->balance < $totalDebit) {
                    throw ValidationException::withMessages(['balance' => 'Insufficient balance.']);
                }

                // Lock receiver row
                $receiver = User::where('id', $receiverId)->lockForUpdate()->first();

                // Perform updates
                $lockedSender->decrement('balance', $totalDebit);
                $receiver->increment('balance', $amount);

                // Record transaction
                $transaction = Transaction::create([
                    'sender_id'      => $lockedSender->id,
                    'receiver_id'    => $receiver->id,
                    'amount'         => $amount,
                    'commission_fee' => $commission,
                    'status'         => 'completed',
                ]);

                // Dispatch event after commit
                TransactionCreated::dispatch($transaction);

                return $transaction;
            });

            return back()->with('success', 'Transaction successful');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            report($e);
            return back()->withErrors(['transactions' => 'Transaction failed. Please try again.']);
        }
    }
}
