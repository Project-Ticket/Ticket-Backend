<?php

namespace App\Http\Controllers\Api;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use GuzzleHttp\Psr7\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WilayahController extends Controller
{
    private $baseUrl = 'https://www.emsifa.com/api-wilayah-indonesia/api';

    /**
     * Get all provinces
     */
    public function getProvinces(): JsonResponse
    {
        try {
            $response = Http::get("{$this->baseUrl}/provinces.json");

            if ($response->successful()) {
                return MessageResponseJson::success(
                    'Data provinsi berhasil diambil',
                    $response->json()
                );
            }

            return MessageResponseJson::serverError('Gagal mengambil data provinsi');
        } catch (\Exception $e) {
            return  MessageResponseJson::serverError(
                'Terjadi kesalahan: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get regencies by province id
     */
    public function getRegencies($provinceId): JsonResponse
    {
        try {
            $response = Http::get("{$this->baseUrl}/regencies/{$provinceId}.json");

            if ($response->successful()) {
                return MessageResponseJson::success(
                    'Data kabupaten/kota berhasil diambil',
                    $response->json()
                );
            }

            return MessageResponseJson::serverError('Gagal mengambil data kabupaten/kota');
        } catch (\Exception $e) {
            return MessageResponseJson::serverError(
                'Terjadi kesalahan: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get districts by regency id
     */
    public function getDistricts($regencyId): JsonResponse
    {
        try {
            $response = Http::get("{$this->baseUrl}/districts/{$regencyId}.json");

            if ($response->successful()) {
                return MessageResponseJson::success(
                    'Data kecamatan berhasil diambil',
                    $response->json()
                );
            }

            return MessageResponseJson::serverError('Gagal mengambil data kecamatan');
        } catch (\Exception $e) {
            return MessageResponseJson::serverError(
                'Terjadi kesalahan: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get villages by district id
     */
    public function getVillages($districtId): JsonResponse
    {
        try {
            $response = Http::get("{$this->baseUrl}/villages/{$districtId}.json");

            if ($response->successful()) {
                return  MessageResponseJson::success(
                    'Data kelurahan/desa berhasil diambil',
                    $response->json()
                );
            }

            return MessageResponseJson::serverError('Gagal mengambil data kelurahan/desa');
        } catch (\Exception $e) {
            return MessageResponseJson::serverError(
                'Terjadi kesalahan: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get complete data for a specific province (with regencies)
     */
    public function getProvinceWithRegencies($provinceId): JsonResponse
    {
        try {
            $provinceResponse = Http::get("{$this->baseUrl}/provinces.json");
            $regenciesResponse = Http::get("{$this->baseUrl}/regencies/{$provinceId}.json");

            if ($provinceResponse->successful() && $regenciesResponse->successful()) {
                $provinces = $provinceResponse->json();
                $province = collect($provinces)->firstWhere('id', $provinceId);

                if (!$province) {
                    return MessageResponseJson::notFound(
                        'Provinsi tidak ditemukan'
                    );
                }

                return MessageResponseJson::success(
                    'Data provinsi dengan kabupaten/kota berhasil diambil',
                    [
                        'province' => $province,
                        'regencies' => $regenciesResponse->json()
                    ]
                );
            }

            return  MessageResponseJson::serverError(
                'Gagal mengambil data provinsi atau kabupaten/kota'
            );
        } catch (\Exception $e) {
            return MessageResponseJson::serverError(
                'Terjadi kesalahan: ' . $e->getMessage()
            );
        }
    }

    /**
     * Search provinces by name
     */
    public function searchProvinces(Request $request): JsonResponse
    {
        try {
            $query = $request->input('search', '');

            if (empty($query)) {
                return  MessageResponseJson::badRequest(
                    'Parameter pencarian tidak boleh kosong'
                );
            }

            $response = Http::get("{$this->baseUrl}/provinces.json");

            if ($response->successful()) {
                $provinces = $response->json();
                $filtered = collect($provinces)->filter(function ($province) use ($query) {
                    return stripos($province['name'], $query) !== false;
                });

                return MessageResponseJson::success(
                    'Data provinsi berhasil ditemukan',
                    $filtered->values()->all()
                );
            }

            return MessageResponseJson::serverError('Gagal mengambil data provinsi untuk pencarian');
        } catch (\Exception $e) {
            return MessageResponseJson::serverError(
                'Terjadi kesalahan: ' . $e->getMessage()
            );
        }
    }
}
