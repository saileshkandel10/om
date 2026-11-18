<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuizCategoryRequest;
use App\Http\Requests\Admin\UpdateQuizCategoryRequest;
use App\Models\Quiz;
use App\Models\QuizCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QuizCategoryController extends Controller
{
    /**
     * Display a listing of the resource with stats, search & reorder.
     */
    public function index(Request $request): View
    {
        $query = QuizCategory::withCount('quizzes');

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderBy('display_order', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => QuizCategory::count(),
            'active' => QuizCategory::where('is_active', true)->count(),
            'inactive' => QuizCategory::where('is_active', false)->count(),
            'total_quizzes' => Quiz::count(),
        ];

        return view('admin.pharmacist-quiz.categories.index', compact('categories', 'stats'));
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreQuizCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['color'] = $data['color'] ?: '#CD0B6F';

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('quiz_categories', 'public');
        }

        QuizCategory::create($data);

        return redirect()->route('admin.pharmacist-quiz.categories.index')
            ->with('success', 'Quiz Category created successfully.');
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateQuizCategoryRequest $request, QuizCategory $category): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            if ($category->image_path && Storage::disk('public')->exists($category->image_path)) {
                Storage::disk('public')->delete($category->image_path);
            }
            $data['image_path'] = $request->file('image')->store('quiz_categories', 'public');
        }

        $category->update($data);

        return redirect()->route('admin.pharmacist-quiz.categories.index')
            ->with('success', 'Quiz Category updated successfully.');
    }

    /**
     * Remove the specified category (Safe deletion check).
     */
    public function destroy(QuizCategory $category): RedirectResponse
    {
        $quizzesCount = $category->quizzes()->count();
        $questionsCount = $category->questions()->count();

        if ($quizzesCount > 0 || $questionsCount > 0) {
            $msg = "Cannot delete category '{$category->name}' because it contains ";
            $parts = [];
            if ($quizzesCount > 0) {
                $parts[] = "{$quizzesCount} quiz(zes)";
            }
            if ($questionsCount > 0) {
                $parts[] = "{$questionsCount} question(s)";
            }
            $msg .= implode(' and ', $parts).'. Please reassign or delete them first.';

            return redirect()->route('admin.pharmacist-quiz.categories.index')
                ->with('error', $msg);
        }

        if ($category->image_path && Storage::disk('public')->exists($category->image_path)) {
            Storage::disk('public')->delete($category->image_path);
        }

        $category->delete(); // Soft deletes

        return redirect()->route('admin.pharmacist-quiz.categories.index')
            ->with('success', "Quiz Category '{$category->name}' deleted successfully.");
    }

    /**
     * Toggle active status of a category.
     */
    public function toggleStatus(QuizCategory $category): RedirectResponse
    {
        $category->update([
            'is_active' => ! $category->is_active,
        ]);

        $status = $category->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Category '{$category->name}' has been {$status}.");
    }
}
