<?php

namespace App\Http\Controllers;

use App\Http\Resources\DiagnosisResource;
use App\Models\Diagnosis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiagnosisController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Diagnosis::query()->orderBy('name', 'asc');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_letter')) {
            $query->where('category_letter', strtoupper(trim($request->input('category_letter'))));
        }

        $perPage = (int) $request->input('per_page', 50);
        $diagnoses = $query->paginate($perPage);

        return $this->success(
            DiagnosisResource::collection($diagnoses),
            'Diagnoses catalog retrieved successfully.'
        );
    }
}
