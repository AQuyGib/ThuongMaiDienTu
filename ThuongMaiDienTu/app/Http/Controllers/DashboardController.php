<?php

namespace App\Http\Controllers;

use App\Models\Cashbook;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Tổng sổ quỹ toàn thời gian ───────────────────────
        $totalIncome  = Cashbook::ofType('Income')->sum('amount');
        $totalExpense = Cashbook::ofType('Expense')->sum('amount');
        $balance      = $totalIncome - $totalExpense;

        // ── Tháng hiện tại ────────────────────────────────────
        $month = now()->month;
        $year  = now()->year;

        $incomeThisMonth  = Cashbook::ofType('Income')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('amount');

        $expenseThisMonth = Cashbook::ofType('Expense')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('amount');

        // ── 5 giao dịch gần nhất ─────────────────────────────
        $recentTransactions = Cashbook::orderBy('created_at', 'desc')->take(5)->get();

        // ── Tổng số giao dịch ─────────────────────────────────
        $totalTransactions = Cashbook::count();

        return view('Dashboard.Dashboard', compact(
            'totalIncome',
            'totalExpense',
            'balance',
            'incomeThisMonth',
            'expenseThisMonth',
            'recentTransactions',
            'totalTransactions'
        ));
    }
}