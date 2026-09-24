<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RealQAssessmentQuestion;
use App\Models\RealQAssessmentSchoolAssignment;
use App\Models\RealQAssessmentTopic;
use App\Models\StudentBoard;
use Illuminate\Http\Request;

class StudentBoardController extends Controller
{
    public function index()
    {
        $boards = StudentBoard::orderBy('name')->get()->map(function ($board) {
            $board->can_delete = !$this->isBoardInUse((int) $board->id);
            return $board;
        });

        return view('backend.realq_assessment.boards.index', compact('boards'));
    }

    public function create()
    {
        return view('backend.realq_assessment.boards.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_other' => 'nullable|in:0,1',
        ]);

        StudentBoard::create([
            'name' => $request->name,
            'is_other' => (int) $request->input('is_other', 0),
        ]);

        return redirect()->route('backend.realqassessment.boards.index')
            ->with('success', 'Board added successfully.');
    }

    public function edit($id)
    {
        $board = StudentBoard::findOrFail($id);

        return view('backend.realq_assessment.boards.edit', compact('board'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'board_id' => 'required|integer',
            'is_other' => 'nullable|in:0,1',
        ]);

        $board = StudentBoard::findOrFail($request->board_id);
        $board->name = $request->name;
        $board->is_other = (int) $request->input('is_other', 0);
        $board->save();

        return redirect()->route('backend.realqassessment.boards.index')
            ->with('success', 'Board updated successfully.');
    }

    public function destroy($id)
    {
        $board = StudentBoard::findOrFail($id);

        if ($this->isBoardInUse((int) $id)) {
            return redirect()->route('backend.realqassessment.boards.index')
                ->with('error', 'Board is in use and cannot be deleted.');
        }

        $board->delete();

        return redirect()->route('backend.realqassessment.boards.index')
            ->with('success', 'Board deleted successfully.');
    }

    private function isBoardInUse(int $boardId): bool
    {
        return RealQAssessmentSchoolAssignment::where('realq_assessment_assigned_board_id', $boardId)->exists()
            || RealQAssessmentTopic::where('board_id', $boardId)->exists()
            || RealQAssessmentQuestion::where('board_id', $boardId)->exists();
    }
}
