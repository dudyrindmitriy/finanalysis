<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoalController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0.01',
            'deadline' => 'required|date|after:today',
        ]);

        Goal::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'target_amount' => $request->target_amount,
            'deadline' => $request->deadline,
        ]);

        return response()->json(['success' => true]);
    }

    public function addMoney(Request $request)
    {
        $request->validate([
            'goal_id' => 'required|exists:goals,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $goal = Goal::where('id', $request->goal_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $newAmount = $goal->current_amount + $request->amount;

        if ($newAmount > $goal->target_amount) {
            $goal->current_amount = $goal->target_amount;
            $message = 'Цель достигнута! Вы добавили ' . $request->amount . ' ₽, но сумма не может превышать целевую.';
            $goal->completed = true;
            $goal->save();

            return response()->json([
                'success' => true,
                'message' => $message,
                'completed' => true,
                'current_amount' => $goal->current_amount
            ]);
        }

        $goal->current_amount = $newAmount;
        $goal->save();

        return response()->json([
            'success' => true,
            'completed' => $newAmount >= $goal->target_amount,
            'current_amount' => $goal->current_amount
        ]);
    }
}
