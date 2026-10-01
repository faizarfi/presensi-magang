<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index()
    {
        $year = request()->get('year', Carbon::now()->year);

        $holidays = Holiday::whereYear('holiday_date', $year)
            ->orderBy('holiday_date', 'asc')
            ->get();

        return view('admin.holidays.index', compact('holidays', 'year'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'holiday_date' => ['required', 'date', 'unique:holidays,holiday_date'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'holiday_date.required' => 'Tanggal libur wajib dipilih.',
            'holiday_date.unique' => 'Tanggal ini sudah terdaftar sebagai hari libur.',
            'name.required' => 'Nama hari libur wajib diisi.',
        ]);

        Holiday::create($request->only('holiday_date', 'name', 'description'));

        return back()->with('success', "Hari libur '{$request->name}' berhasil ditambahkan.");
    }

    public function destroy(Holiday $holiday)
    {
        $name = $holiday->name;
        $holiday->delete();

        return back()->with('success', "Hari libur '{$name}' berhasil dihapus.");
    }
}
