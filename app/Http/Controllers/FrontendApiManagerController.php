<?php
namespace App\Http\Controllers;

use App\Specials;
use Illuminate\Support\Facades\DB;
use Validator;
use App\Token;
use App\Device;
use App\Http\Controllers\Controller;
use App\Manager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class FrontendApiManagerController extends Controller{


    public function getManagersList()
    {
        $managers_array = array();
        $managers = Manager::query()->get();
        if($managers->count()){
            foreach($managers as $manager){
                $managers_array[] = array(
                    'id' => $manager->object_id,
                    'name' => $manager->name,
                    'email' => $manager->email,
                    'country' => $manager->country,
                );
            }
        }

        return response()->json($managers_array);
    }


    public function getManager($id)
    {
        $manager = Manager::findOrNew($id);
        $manager->id = $manager->object_id;
        $tokens = Token::query()->where('manager', '=', $manager->object_id)->get();
        $manager->used_tokens = 0;
        if($tokens->count()){
            $t = array();
            foreach($tokens as $token){
                $t[] = $token->object_id;
            }

            $specials = Specials::query()->whereIn('token', $t)->get();
            $manager->used_tokens = $specials->count();
        }
        $manager->reserved_tokens = $tokens->count();

        return response()->json($manager);
    }


    public function deleteManager($id)
    {
        Manager::findOrNew($id)->delete();
        return response()->json(array('status' => 'success'));
    }


    public function createManager(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'country' => 'required|max:65',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            $this->returnJSON(array('error' => $validator->errors()->first(), 'status' => 'error'));
            exit();
        }

        $data = $request->all();
        $data['object_id'] = $this->generateId();
        $manager = Manager::create($data);

        if ($manager) {
            $this->returnJSON(array('id' => $manager->object_id, 'status' => 'success'));
            exit();
        }

        $this->returnJSON(array('error' => 'undefined error', 'status' => 'error'));
        exit();
    }


    public function updateManager(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required',
            'country' => 'required|max:65',
            'email' => 'required|email',
        ]);

        if($validator->fails()){
            return response()->json(array('error' => $validator->errors()->first(), 'status' => 'error'));
        }

        $manager = Manager::findOrNew($request->input('id'));
        $manager->name = $request->input('name');
        $manager->country = $request->input('country');
        $manager->email = $request->input('email');
        $manager->save();

        return response()->json(array('id' => $manager->object_id, 'status' => 'success'));
    }

}