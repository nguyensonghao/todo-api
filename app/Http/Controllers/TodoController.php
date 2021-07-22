<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Helpers\Response;
use Validator;

use App\Http\Requests;
use App\Todo;

class TodoController extends Controller
{
    public function add(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'description' => 'required',
            'image' => 'mimes:jpeg,jpg,png,gif|required|max:10000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 400);
        }

        $payload = $request->all();
        $user_id = auth()->user()->id;
        $image = $request->file('image');
        $file_name = $user_id . '_' . time() . '.' . $image->getClientOriginalExtension();
        $path = public_path('/uploads/todos');
        if ($image->move($path, $file_name)) {
            $todo = new Todo();
            $todo->title = $payload['title'];
            $todo->description = $payload['description'];
            $todo->user_id = $user_id;
            $todo->image = '/uploads/todos/' . $file_name;
            if ($todo->save()) {
                return response()->json([
                    'data' => $todo
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Have an error on server.'
                ], 500);
            }
        } else {
            return response()->json([
                'message' => 'Have an error on server.'
            ], 500);
        }
    }

    public function all() {
        $list = Todo::where("user_id", auth()->user()->id)->get();
        return response()->json([
            'data' => $list
        ], 200);
    }

    public function update_status(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 400);
        }

        $payload = $request->all();
        $todo = Todo::where([
            'id' => $payload['id'],
            'user_id' => auth()->user()->id
        ])->first();

        if (!$todo) {
            return response()->json([
                'message' => 'Not found this todo.'
            ], 400);
        }

        $todo->status = $payload['status'];
        if ($todo->save()) {
            return response()->json([
                'data' => $todo
            ], 200);
        } else {
            return response()->json([
                'message' => 'Have an error on server.'
            ], 500);
        }
    }

    public function remove($id) {
        $todo = Todo::where([
            'id' => $id,
            'user_id' => auth()->user()->id
        ])->first();

        if ($todo) {
            if ($todo->delete()) {
                return response()->json([
                    'message' => 'Deleted todo.'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Have an error on server.'
                ], 500);
            }
        } else {
            return response()->json([
                'message' => 'Not found this todo.'
            ], 400);
        }
    }
}
