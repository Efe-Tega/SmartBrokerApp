<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\PackagePlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InvestmentManagement extends Controller
{
    public function allInvestmentPlan()
    {
        $investmentPlans = PackagePlan::latest()->get();

        return response()->json(['date' => $investmentPlans]);
    }

    public function addInvestmentPlan(Request $request)
    {
        $request->validate([
            'plan_name' => 'required|string|unique:package_plans,plan_name',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|min:0',
            'roi' => 'required',
            'duration' => 'required',
            // 'total_return' => 'required',
            'features' => 'required|array',
            'features.*' => 'string'
        ]);

        $dailyReturn = ($request->min_amount / 100) * $request->roi;
        $totalProfit = $dailyReturn * $request->duration;
        $totalReturn = $totalProfit + $request->amount;

        $investmentPlan = PackagePlan::insert([
            'plan_name' => $request->plan_name,
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'roi' => $request->roi,
            'duration' => $request->duration,
            'total_return' => $totalReturn,
            // 'features' => implode(', ', $request->features),
            'features' => json_encode($request->features),
            'created_at' => Carbon::now()
        ]);

        return response()->json([
            'data' => $investmentPlan,
            'message' => 'Plan added successfully',
            'return' => $totalReturn
        ]);
    }

    public function editInvestmentPlan($id)
    {
        $investmentPlan = PackagePlan::findOrFail($id);

        return response()->json(["data" => $investmentPlan]);
    }

    public function updateInvestmentPlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_name' => 'required',
            'min_amount' => 'required',
            'max_amount' => 'required',
            'roi' => 'required',
            'duration' => 'required',
            // 'total_return' => 'required',
            'features' => 'required|array',
            'features.*' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()]);
        }

        $id = $request->id;

        $updatedPlan = PackagePlan::findOrFail($id)->update([
            'plan_name' => $request->plan_name,
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'roi' => $request->roi,
            'duration' => $request->duration,
            'features' => json_encode($request->features),
        ]);

        return response()->json(["data" => $updatedPlan, "message" => "Investment plan updated"]);
    }

    public function deleteInvestmentPlan($id)
    {
        PackagePlan::findOrFail($id)->delete();

        return response()->json(['message' => 'Investment plan deleted']);
    }
}
