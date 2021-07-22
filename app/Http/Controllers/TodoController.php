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
            $this->_send_noti(
                "Todo status is updated!",
                auth()->user()->name . " just have updated status of todo: " . $todo->title
            );

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

    private function _send_noti($title, $body) {
        $SERVER_API_KEY = 'AAAAOrk2Cog:APA91bEQj9mfwv2EaZy2s_Q9-wmRhPLw_wJVpUz-4-4pagYe-TikItlBEFR1gJTLkUyYtIyJPMMiTyAXCFMMhY4H3lDTBHQ0cNB5XpcgRUXM6MfaSwlKHwvxHDGHAsz4LW_8nEPWCVpe';
        $data = [
            "to"  => '/topics/all',
            "notification" => [
                "title" => $title,
                "body" => $body,
            ]
        ];

        $dataString = json_encode($data);
        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        return curl_exec($ch);
    }
}
