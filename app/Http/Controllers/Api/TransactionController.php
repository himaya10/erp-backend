<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * List all transactions with optional filters.
     */
    public function index(Request $request)
    {
        $query = Transaction::with('user');

        if ($request->has('type') && in_array($request->type, ['income', 'expense'])) {
            $query->where('type', $request->type);
        }

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        return response()->json($transactions);
    }

    /**
     * Create a manual transaction entry.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'reference_id' => 'nullable|string|max:255',
        ]);

        $transaction = Transaction::create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'amount' => $request->amount,
            'reference_id' => $request->reference_id,
        ]);

        return response()->json($transaction->load('user'), 201);
    }

    /**
     * Show a single transaction.
     */
    public function show(Transaction $transaction)
    {
        return response()->json($transaction->load('user'));
    }

    /**
     * Financial summary: total income, total expenses, net profit/loss.
     */
    public function summary(Request $request)
    {
        $incomeQuery = Transaction::where('type', 'income');
        $expenseQuery = Transaction::where('type', 'expense');

        if ($request->has('from_date')) {
            $incomeQuery->whereDate('created_at', '>=', $request->from_date);
            $expenseQuery->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $incomeQuery->whereDate('created_at', '<=', $request->to_date);
            $expenseQuery->whereDate('created_at', '<=', $request->to_date);
        }

        $totalIncome = $incomeQuery->sum('amount');
        $totalExpenses = $expenseQuery->sum('amount');

        return response()->json([
            'total_income' => round($totalIncome, 2),
            'total_expenses' => round($totalExpenses, 2),
            'net_profit' => round($totalIncome - $totalExpenses, 2),
        ]);
    }
}
