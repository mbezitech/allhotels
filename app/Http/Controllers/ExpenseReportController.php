<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseReportController extends Controller
{
    /**
     * Display expense reports and summaries
     */
    public function index(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // Default date range: current month
        $startDate = $request->get('start_date', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        
        // Hotel filter
        $selectedHotelId = $hotelId;
        if ($isSuperAdmin && $request->has('hotel_id') && $request->hotel_id) {
            $selectedHotelId = $request->hotel_id;
        }
        
        // Build query
        $query = Expense::query();
        
        if (!$isSuperAdmin) {
            $query->where('hotel_id', $hotelId);
        } elseif ($selectedHotelId) {
            $query->where('hotel_id', $selectedHotelId);
        }
        
        $query->whereBetween('expense_date', [$startDate, $endDate])
            ->with(['category', 'addedBy', 'hotel']);
        
        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('expense_category_id', $request->category_id);
        }
        
        // Filter by payment method
        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }
        
        $expenses = $query->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculate totals
        $totalAmount = $expenses->sum('amount');
        $totalCount = $expenses->count();
        
        // Totals by category
        $totalsByCategory = $expenses->groupBy('expense_category_id')
            ->map(function ($group) {
                return [
                    'category' => $group->first()->category,
                    'count' => $group->count(),
                    'total' => $group->sum('amount'),
                ];
            })
            ->sortByDesc('total')
            ->values();
        
        // Totals by payment method
        $totalsByPaymentMethod = $expenses->groupBy('payment_method')
            ->map(function ($group, $method) {
                return [
                    'method' => ucfirst($method),
                    'count' => $group->count(),
                    'total' => $group->sum('amount'),
                ];
            })
            ->sortByDesc('total')
            ->values();
        
        // Totals by date (for chart)
        $totalsByDate = $expenses->groupBy(function ($expense) {
            return $expense->expense_date->format('Y-m-d');
        })
            ->map(function ($group, $date) {
                return [
                    'date' => $date,
                    'count' => $group->count(),
                    'total' => $group->sum('amount'),
                ];
            })
            ->sortBy('date')
            ->values();
        
        // Get all hotels for super admin filter
        $hotels = $isSuperAdmin ? Hotel::orderBy('name')->get() : collect();
        
        // Get categories for filter
        $categories = $selectedHotelId
            ? ExpenseCategory::where('hotel_id', $selectedHotelId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
            : collect();
        
        // Log report view
        logActivity('report_viewed', null, "Expense Report viewed - Range: {$startDate} to {$endDate}", [
            'report_type' => 'expenses',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'hotel_id' => $selectedHotelId,
        ]);
        
        return view('expense-reports.index', compact(
            'expenses',
            'totalAmount',
            'totalCount',
            'totalsByCategory',
            'totalsByPaymentMethod',
            'totalsByDate',
            'startDate',
            'endDate',
            'hotels',
            'categories',
            'isSuperAdmin',
            'selectedHotelId'
        ));
    }

    /**
     * Export expenses to PDF or Excel
     */
    public function export(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        $format = $request->get('format', 'pdf'); // pdf or excel
        
        $startDate = $request->get('start_date', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        
        // Hotel filter
        $selectedHotelId = $hotelId;
        if ($isSuperAdmin && $request->has('hotel_id') && $request->hotel_id) {
            $selectedHotelId = $request->hotel_id;
        }
        
        // Build query
        $query = Expense::query();
        
        if (!$isSuperAdmin) {
            $query->where('hotel_id', $hotelId);
        } elseif ($selectedHotelId) {
            $query->where('hotel_id', $selectedHotelId);
        }
        
        $query->whereBetween('expense_date', [$startDate, $endDate])
            ->with(['category', 'addedBy', 'hotel']);
        
        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('expense_category_id', $request->category_id);
        }
        
        // Filter by payment method
        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }
        
        $expenses = $query->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $hotel = $selectedHotelId ? Hotel::find($selectedHotelId) : null;
        
        // Log export
        logActivity('report_exported', null, "Expense Report exported ({$format}) - Range: {$startDate} to {$endDate}", [
            'report_type' => 'expenses',
            'format' => $format,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'hotel_id' => $selectedHotelId,
        ]);
        
        // For now, return a simple CSV export
        // In production, you'd use a library like DomPDF or PhpSpreadsheet
        if ($format === 'excel' || $format === 'csv') {
            return $this->exportCsv($expenses, $hotel, $startDate, $endDate);
        } else {
            // PDF export would require a PDF library
            // For now, redirect to the report page with a message
            return redirect()->route('expense-reports.index', $request->except('format'))
                ->with('info', 'PDF export will be available soon. Please use CSV/Excel export for now.');
        }
    }

    /**
     * Export expenses to CSV
     */
    private function exportCsv($expenses, $hotel, $startDate, $endDate)
    {
        $filename = 'expenses_' . ($hotel ? $hotel->slug : 'all') . '_' . $startDate . '_to_' . $endDate . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($expenses, $hotel, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Date',
                'Category',
                'Description',
                'Amount',
                'Payment Method',
                'Added By',
                'Hotel'
            ]);
            
            // Data rows
            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->expense_date->format('Y-m-d'),
                    $expense->category->name ?? 'N/A',
                    $expense->description,
                    number_format($expense->amount, 2),
                    ucfirst($expense->payment_method),
                    $expense->addedBy->name ?? 'N/A',
                    $expense->hotel->name ?? 'N/A',
                ]);
            }
            
            // Summary row
            fputcsv($file, []);
            fputcsv($file, ['Total', '', '', number_format($expenses->sum('amount'), 2), '', '', '']);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
