<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AIToolSubcategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Models\AITool;
use App\Models\ModuleSetting;

class GenerateAIToolController extends Controller
{
    public function aiToolList()
    {
        return view('backend.ai_tools.aiToolList');
    }

    public function getAiTool(Request $request)
    {
        $prototypes = AIToolSubcategory::with('tool')->select('id', 'name', 'description', 'status');

        return datatables()->of($prototypes)
            ->addColumn('status', function ($row) {
                return $row->status ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                $actionbtn = '<div class="ActionBtns">';

                // Edit button
                $actionbtn .= '<a href="' . route('backend.aiTooledit.aiToolEdit', $row->id) . '" 
                      class="btn btn-block btn-info btn-sm">
                      <i class="fas fa-edit"></i>
                   </a>';

                // Delete button
                $actionbtn .= '<a href="' . route('backend.aiTooldelete.aiToolDelete', $row->id) . '" 
                      class="btn btn-block btn-danger btn-sm delete-prototype"
                      id="deletePrototype"
                      data-id="' . $row->id . '"
                      data-url="' . route('backend.aiTooldelete.aiToolDelete', $row->id) . '">
                      <i class="fas fa-trash"></i>
                   </a>';

                $actionbtn .= '</div>';

                return $actionbtn;
            })
            ->rawColumns(['image', 'status', 'action'])
            ->make(true);
    }


    public function aiToolCreate()
    {
        $tools = AITool::all();
        return view('backend.ai_tools.addAITools', compact('tools'));
    }

    public function aiToolStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'required|string',
            'ai_tool_id' => 'required|exists:ai_tools,id',
            'status' => 'required|in:0,1',
            'display_order' => 'nullable|integer',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = Str::uuid() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('asset/prototypes');

            // Create folder if not exists
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $image->move($destinationPath, $imageName);
            $imagePath = 'asset/prototypes/' . $imageName;
        }

        // Save to database
        $subcategory = new AIToolSubcategory();
        $subcategory->name = $request->name;
        $subcategory->image = $imagePath;
        $subcategory->description = $request->description;
        $subcategory->ai_tool_id = $request->ai_tool_id;
        $subcategory->status = $request->status;
        $subcategory->display_order = $request->display_order ?? $subcategory->getNextDisplayOrderId();
        $subcategory->save();

        if ($subcategory->ai_tool_id == 1 && $subcategory->status == 1) {
            ModuleSetting::updateOrCreate(
                ['key' => 'prototype_my_idea'],
                ['value' => 1]
            );
        }

        if ($subcategory->ai_tool_id == 2 && $subcategory->status == 1) {
            ModuleSetting::updateOrCreate(
                ['key' => 'businessplan'],
                ['value' => 1]
            );
        }

        return redirect()->route('backend.aiToollist.aiToolList')->with('success', 'Prototype created successfully!');
    }
    public function aiToolEdit($id)
    {
        $tools = AITool::all();
        $subcategory = AIToolSubcategory::findOrFail($id);

        return view('backend.ai_tools.editAITools', compact('tools', 'subcategory'));
    }
    public function aiToolUpdate(Request $request, $id)
    {
        $prototype = AIToolSubcategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'required|string',
            'ai_tool_id' => 'required|exists:ai_tools,id',
            'status' => 'required|in:0,1',
            'display_order' => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            if ($prototype->image && file_exists(public_path($prototype->image))) {
                File::delete(public_path($prototype->image));
            }

            $image = $request->file('image');
            $imageName = Str::uuid() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('asset/prototypes'); // match store()
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $image->move($destinationPath, $imageName);
            $prototype->image = 'asset/prototypes/' . $imageName;
        }

        $prototype->update([
            'name' => $request->name,
            'description' => $request->description,
            'ai_tool_id' => $request->ai_tool_id,
            'status' => $request->status,
            'display_order' => $request->display_order ?? $prototype->getNextDisplayOrderId(),
            'image' => $prototype->image, // update image if changed
        ]);

        if ($prototype->ai_tool_id == 1) {
            ModuleSetting::updateOrCreate(
                ['key' => 'prototype_my_idea'],
                ['value' => $prototype->status == 1 ? 1 : 0]
            );
        }

        if ($prototype->ai_tool_id == 2) {
            ModuleSetting::updateOrCreate(
                ['key' => 'businessplan'],
                ['value' => $prototype->status == 1 ? 1 : 0]
            );
        }

        return redirect()->route('backend.aiToollist.aiToolList')->with('success', 'Prototype updated successfully!');
    }

    public function aiToolDelete($id)
    {
        $prototype = AIToolSubcategory::findOrFail($id);

        if ($prototype->image && file_exists(public_path($prototype->image))) {
            File::delete(public_path($prototype->image));
        }

        $prototype->delete();
        return response()->json(['success' => true]);
    }

    public function deleteAiToolImage(Request $request)
    {
        $subcategory = AIToolSubcategory::findOrFail($request->id);

        if (!empty($subcategory->image)) {
            $filePath = public_path($subcategory->image);
            if (file_exists($filePath)) {
                @unlink($filePath); // delete the file
            }
            $subcategory->image = null; // clear DB reference
            $subcategory->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'AI Tool image deleted successfully!'
        ]);
    }
}
