<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternProfile;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class InternManagementController extends Controller
{
    public function index()
    {
        $interns = User::where('role', 'intern')
            ->with(['internProfile', 'attendances'])
            ->orderBy('name')
            ->paginate(10);

        return view('admin.interns.index', compact('interns'));
    }

    public function create()
    {
        $divisions = [
            'Subbagian Umum',
            'Statistik Sosial',
            'Statistik Produksi',
            'Statistik Distribusi',
            'Neraca Wilayah & Analisis Statistik',
            'Integrasi Pengolahan & Diseminasi Statistik (IPDS)',
        ];

        return view('admin.interns.create', compact('divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:20'],
            'nim_nisn' => ['nullable', 'string', 'max:50'],
            'institution' => ['required', 'string', 'max:255'],
            'major' => ['required', 'string', 'max:255'],
            'division' => ['required', 'string', 'max:255'],
            'mentor_name' => ['nullable', 'string', 'max:255'],
            'duration_months' => ['nullable', 'integer', 'min:1', 'max:12'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'address' => ['nullable', 'string'],
        ], [
            'name.required' => 'Nama lengkap peserta wajib diisi.',
            'email.required' => 'Email peserta wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar dalam sistem.',
            'institution.required' => 'Asal kampus / sekolah wajib diisi.',
            'major.required' => 'Jurusan / program studi wajib diisi.',
            'division.required' => 'Seksi penempatan di BPS wajib dipilih.',
            'start_date.required' => 'Tanggal mulai magang wajib diisi.',
            'end_date.required' => 'Tanggal selesai magang wajib diisi.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'intern',
            'phone' => $request->phone,
        ]);

        InternProfile::create([
            'user_id' => $user->id,
            'nim_nisn' => $request->nim_nisn,
            'institution' => $request->institution,
            'major' => $request->major,
            'division' => $request->division,
            'mentor_name' => $request->mentor_name,
            'duration_months' => $request->duration_months,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'address' => $request->address,
        ]);

        return redirect()->route('admin.interns.index')
            ->with('success', "Peserta magang {$user->name} berhasil didaftarkan ke sistem.");
    }

    public function show(User $intern)
    {
        if ($intern->role !== 'intern') {
            abort(404);
        }

        $intern->load(['internProfile', 'attendances' => function ($q) {
            $q->orderBy('date', 'desc');
        }]);

        $stats = AttendanceService::getInternStatistics($intern->id);

        return view('admin.interns.show', compact('intern', 'stats'));
    }

    public function edit(User $intern)
    {
        if ($intern->role !== 'intern') {
            abort(404);
        }

        $intern->load('internProfile');

        $divisions = [
            'Subbagian Umum',
            'Statistik Sosial',
            'Statistik Produksi',
            'Statistik Distribusi',
            'Neraca Wilayah & Analisis Statistik',
            'Integrasi Pengolahan & Diseminasi Statistik (IPDS)',
        ];

        return view('admin.interns.edit', compact('intern', 'divisions'));
    }

    public function update(Request $request, User $intern)
    {
        if ($intern->role !== 'intern') {
            abort(404);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $intern->id],
            'password' => ['nullable', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:20'],
            'nim_nisn' => ['nullable', 'string', 'max:50'],
            'institution' => ['required', 'string', 'max:255'],
            'major' => ['required', 'string', 'max:255'],
            'division' => ['required', 'string', 'max:255'],
            'mentor_name' => ['nullable', 'string', 'max:255'],
            'duration_months' => ['nullable', 'integer', 'min:1', 'max:12'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'address' => ['nullable', 'string'],
        ]);

        $intern->name = $request->name;
        $intern->email = $request->email;
        $intern->phone = $request->phone;

        if ($request->filled('password')) {
            $intern->password = Hash::make($request->password);
        }

        $intern->save();

        $intern->internProfile()->updateOrCreate(
            ['user_id' => $intern->id],
            [
                'nim_nisn' => $request->nim_nisn,
                'institution' => $request->institution,
                'major' => $request->major,
                'division' => $request->division,
                'mentor_name' => $request->mentor_name,
                'duration_months' => $request->duration_months,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'address' => $request->address,
            ]
        );

        return redirect()->route('admin.interns.index')
            ->with('success', "Data peserta magang {$intern->name} berhasil diperbarui.");
    }

    public function destroy(User $intern)
    {
        if ($intern->role !== 'intern') {
            abort(404);
        }

        $name = $intern->name;
        $intern->delete();

        return redirect()->route('admin.interns.index')
            ->with('success', "Data peserta magang {$name} berhasil dihapus.");
    }

    public function resetDevice(User $intern)
    {
        if ($intern->role !== 'intern') {
            abort(404);
        }

        $intern->update([
            'device_id' => null,
            'device_name' => null,
            'device_locked_at' => null,
        ]);

        return back()->with('success', "Kunci perangkat peserta {$intern->name} berhasil di-reset. Peserta dapat mendaftarkan perangkat HP barunya saat login.");
    }
}
