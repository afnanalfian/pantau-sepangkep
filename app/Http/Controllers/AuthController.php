<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Kode akses hardcode -> role.
     * Sesuai permintaan: bukan username/password, tapi kode rahasia per role.
     */
    protected array $kodeAkses = [
        'AdminGanteng7309#' => ['role' => 'admin', 'label' => 'Administrator'],
        'IndaHebat7309' => ['role' => 'inda', 'label' => 'Instruktur Daerah (INDA)'],
        'MiciBusuk7309' => ['role' => 'anomali', 'label' => 'Admin Anomali'],
        'SoraJojo7309' => ['role' => 'qg', 'label' => 'Admin Quality Gate'],
        'statistikpangkep' => ['role' => 'pegawai', 'label' => 'Pegawai'],
    ];

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'kode' => 'required|string',
        ], [
            'kode.required' => 'Kode akses wajib diisi.',
        ]);

        $kode = $request->input('kode');

        if (!array_key_exists($kode, $this->kodeAkses)) {
            return back()->withErrors(['kode' => 'Kode akses tidak dikenali. Silakan periksa kembali.'])->withInput();
        }

        $akun = $this->kodeAkses[$kode];

        $request->session()->regenerate();
        session([
            'role' => $akun['role'],
            'role_label' => $akun['label'],
            'login_at' => now(),
        ]);

        return redirect()->route('pegawai.home');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['role', 'role_label', 'login_at']);
        $request->session()->regenerate();

        return redirect()->route('landing')->with('status', 'Anda telah keluar.');
    }
}
