<?php

namespace App\Http\Controllers\trainer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\TodoModel;
use Illuminate\Support\Facades\Auth;

class TodoController extends Controller
{
   public function todo_index()
   {
        // $all_todo = TodoModel::all();
        $all_todo = TodoModel::where('trainer_id',Auth::id())->get();
        return view('trainer.todo.index',compact('all_todo'));
   }

   public function todo_insert(Request $req)
   {
      $validator = Validator::make($req->all(), [
          'todo_name' => 'required'
      ]);
      if ($validator->fails())
      {
        return response()->json(['error'=>$validator->errors()]);
      }
      else
      {
          $todo=new TodoModel();
          $todo->trainer_id = Auth::id();
          $todo->todo_name=$req->todo_name;
          $todo->save();
          return response()->json(['success'=>'Successfully Insert']);
      }
   }

   public function todo_delete($id)
   {
     $todo=TodoModel::find($id);

     $delete=$todo->delete();

     if($delete)
     {
        $notification=array(
            'message'=>'Todo Successfully deleted!',
            'success'=>'success',
        );
        return redirect()->back()->with($notification);
     }
   }

   public function todo_edit(Request $req)
   {
      $id=$req->id;
      $todo=TodoModel::find($id);

      echo json_encode($todo);
   }

   public function todo_update(Request $req)
   {

        $validator = Validator::make($req->all(), [
        'todo_name' => 'required'
        ]);
        if ($validator->fails())
        {
        return response()->json(['error'=>$validator->errors()]);
        }
        else
        {
            $id=$req->todo_id;
            $todo=TodoModel::find($id);
            $todo->todo_name=$req->todo_name;
            $todo->save();
            return response()->json(['success'=>'Successfully Update']);
        }
   }

   public function todo_check(Request $req)
   {
       $id=$req->id;
       $action=$req->action;
       $todo=TodoModel::find($id);
       if($action =='checked')
       {
          $todo->todo_done='1';
       }
       else
       {
         $todo->todo_done='0';
       }

       $success=$todo->save();

       if($success)
       {
         echo json_encode('success');
       }
   }

}
