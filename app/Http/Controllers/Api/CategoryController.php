<?php

namespace App\Http\Controllers\Api;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    protected $category;

    public function __construct()
    {
        $this->category = new Category();
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = $this->category->query();

            if ($request->filled('search')) {
                $searchTerm = $request->input('search');
                $query->where(function ($subQuery) use ($searchTerm) {
                    $subQuery->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('description', 'like', "%{$searchTerm}%");
                });
            }

            $categories = $query->paginate($request->get('per_page', 15));

            return MessageResponseJson::paginated('Categories retrieved successfully', $categories);
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError('Failed to retrieve categories', [$th->getMessage()]);
        }
    }
}
