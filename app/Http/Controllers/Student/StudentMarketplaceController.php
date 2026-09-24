<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceProduct;
use App\Models\MarketplaceProductImage;
use App\Models\Students;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class StudentMarketplaceController extends Controller
{
    const PRODUCT_IMAGE_PATH = 'student/marketplace';
    const PRODUCT_IMAGE_FALLBACK_PATH = 'marketplace-uploads';
    const MAX_PRODUCTS = 20;

    public function index()
    {
        $products = $this->studentProducts();

        return view('student.marketplace.index', [
            'products' => $products,
            'publishedCount' => $products->where('status', 1)->count(),
            'maxProducts' => self::MAX_PRODUCTS,
            'currencies' => DB::table('currencies')->orderBy('code')->get(['id', 'code']),
        ]);
    }

    public function products()
    {
        $products = $this->studentProducts();

        return response()->json([
            'success' => true,
            'message' => 'Products fetched successfully.',
            'data' => [
                'products' => $products,
                'total' => $products->count(),
                'published' => $products->where('status', 1)->count(),
            ],
        ]);
    }

    public function save(Request $request)
    {
        $student = $this->currentStudent();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found.'], 422);
        }

        $isUpdate = $request->filled('id');
        $studentProductCount = MarketplaceProduct::where('student_id', $student->id)->count();

        if (!$isUpdate && $studentProductCount >= self::MAX_PRODUCTS) {
            return response()->json([
                'success' => false,
                'message' => 'You can add up to ' . self::MAX_PRODUCTS . ' products only.',
            ], 422);
        }

        $product = $request->filled('id')
            ? $this->studentProduct($student, $request->id)
            : new MarketplaceProduct(['student_id' => $student->id]);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $hasExistingImages = $product->exists && $product->images()->exists();

        $validated = $request->validate([
            'id' => 'nullable|integer',
            'name' => 'required|string|max:60',
            'currency_id' => 'required|integer|exists:currencies,id',
            'story' => 'required|string|max:500',
            'description' => 'required|string|max:500',
            'special_feature' => 'required|string|max:300',
            'price' => 'required|numeric|min:0',
            'product_images' => $hasExistingImages ? 'nullable|array' : 'required|array|min:1',
            'product_images.*' => 'image|mimes:png,jpg,jpeg,webp|max:5120',
            'remove_image_ids' => 'nullable|array',
            'remove_image_ids.*' => 'integer',
        ]);

        $removeImageIds = collect($validated['remove_image_ids'] ?? []);

        if ($product->exists) {
            $keptExistingCount = $product->images()->whereNotIn('id', $removeImageIds)->count();
            $newImagesCount = count($request->file('product_images', []));

            if (($keptExistingCount + $newImagesCount) < 1) {
                return response()->json(['success' => false, 'message' => 'Please keep at least one product image.'], 422);
            }
        }

        $product->fill(collect($validated)->except(['id', 'product_images', 'remove_image_ids'])->toArray());

        if (!$product->exists) {
            $product->status = 0;
        }

        $product->save();

        if ($removeImageIds->isNotEmpty()) {
            $imagesToRemove = $product->images()->whereIn('id', $removeImageIds)->get();

            foreach ($imagesToRemove as $image) {
                $this->deleteImageFile($image);
                $image->delete();
            }
        }

        $tenantId = tenant() ? tenant()->tenant_id : null;

        foreach ($request->file('product_images', []) as $file) {
            $fileName = Str::random(20) . '.' . strtolower($file->getClientOriginalExtension());

            if ($tenantId) {
                $file->storeAs($tenantId . '/' . self::PRODUCT_IMAGE_PATH, $fileName, 'tenant_uploads');
                $imagePath = 'tenants/' . $tenantId . '/' . self::PRODUCT_IMAGE_PATH . '/' . $fileName;
            } else {
                $file->move(public_path(self::PRODUCT_IMAGE_FALLBACK_PATH), $fileName);
                $imagePath = self::PRODUCT_IMAGE_FALLBACK_PATH . '/' . $fileName;
            }

            $product->images()->create(['image_path' => $imagePath]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product saved as draft.',
            'product' => $product->fresh('images'),
        ]);
    }

    public function edit($id)
    {
        $student = $this->currentStudent();
        $product = $student ? $this->studentProduct($student, $id, ['images']) : null;

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $product->makeHidden(['created_at', 'updated_at']);
        $product->images->makeHidden(['created_at', 'updated_at']);

        return response()->json(['success' => true, 'product' => $product]);
    }

    public function downloadImage($id)
    {
        $student = $this->currentStudent();

        $image = $student
            ? MarketplaceProductImage::whereHas('product', function ($query) use ($student) {
                $query->where('student_id', $student->id);
            })->find($id)
            : null;

        if (!$image || !file_exists(public_path($image->image_path))) {
            abort(404);
        }

        return response()->download(public_path($image->image_path));
    }

    public function destroy($id)
    {
        $student = $this->currentStudent();
        $product = $student ? $this->studentProduct($student, $id, ['images']) : null;

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        foreach ($product->images as $image) {
            $this->deleteImageFile($image);
        }

        $product->images()->delete();
        $product->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted.']);
    }

    public function publish($id)
    {
        $student = $this->currentStudent();
        $product = $student ? $this->studentProduct($student, $id) : null;

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        if ((int) $product->status !== 1) {
            $product->update(['status' => 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product published successfully.',
        ]);
    }

    private function deleteImageFile(MarketplaceProductImage $image)
    {
        $path = public_path($image->image_path);

        if (File::exists($path)) {
            File::delete($path);
        }
    }

    private function currentStudent()
    {
        return Students::where('user_id', Session::get('user_id'))->first();
    }

    private function studentProduct(Students $student, $id, array $with = [])
    {
        return MarketplaceProduct::where('id', $id)->where('student_id', $student->id)->with($with)->first();
    }

    private function studentProducts()
    {
        $student = $this->currentStudent();

        if (!$student) {
            return collect();
        }

        return MarketplaceProduct::where('student_id', $student->id)
            ->select(['id', 'name', 'status', 'updated_at'])
            ->with('images:id,marketplace_product_id,image_path')
            ->latest('updated_at')
            ->get();
    }
}
