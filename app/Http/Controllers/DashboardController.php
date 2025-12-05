<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $recentTransactions = Transaction::where('user_id', $userId)
            ->with('category')
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        $oneYearAgo = Carbon::now()->subMonths(6)->startOfMonth();

        // Агрегация расходов за последние 6 месяцев
        $monthlyExpensesRaw = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->where('date', '>=', $oneYearAgo)
            ->select(
                DB::raw('DATE_FORMAT(date, "%Y-%m") as month_year'),
                DB::raw('SUM(amount) as total_expense')
            )
            ->groupBy('month_year')
            ->orderBy('month_year', 'asc')
            ->get();

        // Формируем данные для графика расходов
        $expenseLabels = [];
        $expenseData = [];
        $dataMap = $monthlyExpensesRaw->keyBy('month_year');

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthYearKey = $date->format('Y-m');
            $expenseLabels[] = $date->translatedFormat('M');
            $expenseData[] = $dataMap->has($monthYearKey) ? $dataMap[$monthYearKey]->total_expense : 0;
        }

        // Доходы по категориям - чисто через Eloquent и коллекции
        $expenseByCategory = Transaction::with('category')
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->whereNotNull('category_id')
            ->get()
            ->filter(function ($transaction) {
                return $transaction->amount > 0;
            })
            ->groupBy('category.name')
            ->map(function ($transactions, $categoryName) {
                $firstTransaction = $transactions->first();
                $total = $transactions->sum('amount');
                return [
                    'category_name' => $categoryName,
                    'total_amount' => $total,
                    'category_color' => $firstTransaction->category->color // ← берем цвет из БД
                ];
            })
            ->filter(fn($item) => $item['total_amount'] > 0)
            ->sortByDesc('total_amount')
            ->values();

        // Формируем данные для круговой диаграммы РАСХОДОВ
        $expenseCategories = [];
        $expenseCategoryAmounts = [];
        $expenseCategoryColors = [];

        foreach ($expenseByCategory as $item) {
            $expenseCategories[] = $item['category_name'];
            $expenseCategoryAmounts[] = $item['total_amount'];
            $expenseCategoryColors[] = $item['category_color']; // ← используем цвет из БД
        }
        $chatHistory = [];
        if ($userId) {
            $cacheKey = 'giga_chat_history_' . $userId;
            $chatHistory = Cache::get($cacheKey, []);
        }
        return view('home', [
            'recentTransactions' => $recentTransactions,
            'expenseLabels' => $expenseLabels,
            'expenseData' => $expenseData,
            'expenseCategories' => $expenseCategories, // ← изменено
            'expenseCategoryAmounts' => $expenseCategoryAmounts, // ← изменено
            'expenseCategoryColors' => $expenseCategoryColors, // ← изменено
            'chatHistory' => $chatHistory,
        ]);
    }
}
