<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class BukuController extends Controller
{
    public function index()
    {
        return response()->json(Buku::all(), 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'tahun_terbit' => 'required|integer',
            'harga' => 'required|numeric',
        ]);

        // ALUR 2: Log Level WARNING - Validasi Gagal (Zero-PII)
        if ($validator->fails()) {
            Log::warning('Validasi gagal saat penambahan data buku baru.', [
                'actor' => [
                    'user_id' => $request->user()?->id ?? 'unauthenticated',
                    'ip_address' => $request->ip(),
                    'user_agent' => substr($request->userAgent() ?? '', 0, 100),
                ],
                'action' => 'buku.create_validation',
                'reason' => 'VALIDATION_FAILED',
                'invalid_fields' => array_keys($validator->errors()->messages()),
                'timestamp' => now()->toIso8601String(),
            ]);

            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if ($request->boolean('simulate_db_error')) {
                throw new \Illuminate\Database\QueryException(
                    'mysql',
                    'insert into `bukus` ...',
                    [],
                    new \PDOException('SQLSTATE[HY000] [2002] Connection refused', 2002)
                );
            }

            $buku = Buku::create($validator->validated());

            // ALUR 1: Log Level INFO - Sukses Menyimpan Data (Zero-PII)
            Log::info('Data buku baru berhasil disimpan ke dalam katalog.', [
                'actor' => [
                    'user_id' => $request->user()?->id ?? 'anonymous',
                    'email_masked' => $request->user() ? preg_replace('/(?<=.{2}).(?=.*@)/', '*', $request->user()->email) : 'guest',
                    'ip_address' => $request->ip(),
                ],
                'action' => 'buku.create',
                'resource' => [
                    'buku_id' => $buku->id,
                    'judul' => $buku->judul,
                    'penulis' => $buku->penulis,
                ],
                'status' => 'SUCCESS',
                'timestamp' => now()->toIso8601String(),
            ]);

            return response()->json([
                'message' => 'Buku berhasil ditambahkan',
                'data' => $buku
            ], 201);
        } catch (Throwable $e) {
            // ALUR 3: Log Level ERROR - Kegagalan Transaksi Basis Data (Zero-PII)
            Log::error('Koneksi basis data terputus atau query gagal dieksekusi.', [
                'actor' => [
                    'user_id' => $request->user()?->id ?? 'system',
                    'ip_address' => $request->ip(),
                ],
                'action' => 'database.query_execution',
                'reason' => 'DATABASE_CONNECTION_ERROR',
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan sistem saat menyimpan data buku.'
            ], 500);
        }
    }

    public function show($id)
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return response()->json(['message' => 'Buku tidak ditemukan'], 404);
        }

        return response()->json($buku, 200);
    }

    public function update(Request $request, $id)
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return response()->json(['message' => 'Buku tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'judul' => 'sometimes|required|string|max:255',
            'penulis' => 'sometimes|required|string|max:255',
            'penerbit' => 'sometimes|required|string|max:255',
            'tahun_terbit' => 'sometimes|required|integer',
            'harga' => 'sometimes|required|numeric',
        ]);

        $buku->update($validated);

        return response()->json([
            'message' => 'Buku berhasil diperbarui',
            'data' => $buku
        ], 200);
    }

    public function destroy($id)
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return response()->json(['message' => 'Buku tidak ditemukan'], 404);
        }

        $buku->delete();

        return response()->json(['message' => 'Buku berhasil dihapus'], 200);
    }
}