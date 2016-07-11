<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\URL;
use Intervention\Image\Facades\Image;
use Log;
use Validator;
use App\Batch;
use App\Token;
use App\Specials;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use Webpatser\Uuid\Uuid;

class FrontendApiSpecialController extends Controller{

    private function generateName(){
        return Uuid::generate().'-'.(int)microtime(true);
    }

    //helper
    private function returnSpecial($specials){
        $specials_array = array();

        if($specials->count()) {
            foreach ($specials as $special) {
                if (empty($special->image) || strpos($special->image,'http://') !== false) {
                    $image = $special->image;
                } else {
                    $image = url('/').'/img/'.$special->image;
                }
                $specials_array[] = array(
                    'active' => ($special->active) ? true : false,
                    'createdAt' => date('c', strtotime($special->created_at)),
                    'endsAt' => date('c', strtotime($special->ends_at)),
                    'id' => $special->object_id,
                    'image' => $image,
                    'name' => $special->name,
                    'status' => $special->status,
                    'store_name' => $special->store,
                    'website' => $special->site,
                    'views' => $special->views_count
                );
            }
        }
        return $specials_array;
    }


    public function declineSpecial($id){
        $special = Specials::findOrNew($id);
        if($special->object_id){
            $special->status = 2;
            $special->save();
            return response()->json(array('success'=> true));
        }
        return response()->json(array('success'=> false, 'message'=> 'special is not found'));
    }


    //update special
    public function reviewSpecial(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required|max:110',
            'store' => 'required',
            'address' => 'required',
            //'website' => 'required|url', //website
            'phone' => 'required',
            'valid_for' => 'numeric',
        ]);

        if($validator->fails()){
            return response()->json(array('error' => $validator->errors()->first(), 'status' => 'error'));
        }

        $valid_for = $request->input('valid_for');
        if($valid_for < 1) $valid_for = 1;
        if ($valid_for > 7 && !in_array($valid_for, array(14, 21, 30, 60, 90, 120))) $valid_for = 7;

        $special = Specials::findOrNew($request->input('id'));
        if(!$special->object_id){
            return response()->json(array('error' => 'special not found', 'status' => 'error'));
        }

        $special->name = $request->input('name');
        $special->description = str_replace(PHP_EOL, ' ', $request->input('description'));
        $special->store = $request->input('store');
        $special->store_logo_bg = $request->input('store_logo_bg');
        $special->address = $request->input('address');
        $special->site = $request->input('website');
        $special->active = 1;
        $special->phone = $request->input('phone');
        $special->status = 1;
        $special->valid_for = $valid_for;
        $special->country = $request->input('country');
        $special->country_code = $request->input('country_code');
        $special->location_latitude = floatval($_POST['latitude']);
        $special->location_longitude = floatval($_POST['longitude']);
        $special->ends_at = date("Y-m-d H:i:s", time() + $valid_for * 24 * 60 * 60);
        $special->activated_at = date("Y-m-d H:i:s");

        // save images
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if($request->file('image')->getClientSize() > 1024*1024*4) return response()->json(array('error' => 'image_too_big', 'status' => 'error'));

            $name = $this->generateName();
            $path = '../public/img/'.$name;
            //Image::configure(array('driver' => 'imagick'));
            Image::make($request->file('image')->getRealPath())->encode('jpg', 80)->resize(600, 600)->save($path.'-600.jpg');
            Image::make($request->file('image')->getRealPath())->encode('jpg', 80)->resize(320, 320)->save($path.'-320.jpg');
            Image::make($request->file('image')->getRealPath())->encode('jpg', 80)->resize(100, 100)->save($path.'-100.jpg');

            $special->image = $name.'-600.jpg';
            $special->image600 = $name.'-600.jpg';
            $special->image320 = $name.'-320.jpg';
            $special->image100 = $name.'-100.jpg';
        }

        if($request->hasFile('store_logo') && $request->file('store_logo')->isValid()){
            $name = $this->generateName().'-store_logo.jpg';
            $path = '../public/img/'.$name;
            Image::make($request->file('store_logo')->getRealPath())->encode('jpg', 80)->save($path);

            $special->store_logo = $name;
        }

        $special->save();

        return response()->json(array('id' => $special->object_id, 'status' => 'success'));
    }


    public function getSpecials(Request $request, $param = NULL){
        $specials_array = array();
        $limit = ($request->input('limit')) ? $request->input('limit') : 20;
        $offset = ($request->input('offset')) ? $request->input('offset') : 0;

        $specialsAll = Specials::query()->orderBy('created_at', 'desc')->skip($offset)->take($limit)->get();
        $specialsActive = Specials::query()->where('status', '=', 1)->where('ends_at', '>', date("Y-m-d H:i:s"))->orderBy('created_at', 'desc')->skip($offset)->take($limit)->get();
        $specialsQueued = Specials::query()->where('status', '=', 0)->orderBy('created_at', 'desc')->skip($offset)->take($limit)->get();
        $specialsDeclined = Specials::query()->where('status', '=', 2)->orderBy('created_at', 'desc')->skip($offset)->take($limit)->get();
        $specialsExpired = Specials::query()->where('ends_at', '<', date("Y-m-d H:i:s"))->orderBy('created_at', 'desc')->skip($offset)->take($limit)->get();

        if($param == 'all'){
            $data = json_encode($this->returnSpecial($specialsAll), JSON_UNESCAPED_SLASHES);
        } elseif($param == 'active') {
            $data = json_encode($this->returnSpecial($specialsActive), JSON_UNESCAPED_SLASHES);
        } elseif($param == 'queued' && $request->input('offset')) {
            $data = json_encode($this->returnSpecial($specialsQueued), JSON_UNESCAPED_SLASHES);
        } elseif($param == 'declined') {
            $data = json_encode($this->returnSpecial($specialsDeclined), JSON_UNESCAPED_SLASHES);
        } elseif($param == 'expired') {
            $data = json_encode($this->returnSpecial($specialsExpired), JSON_UNESCAPED_SLASHES);
        } else {
            $data = json_encode(array('all' => $this->returnSpecial($specialsAll), 'queued' => $this->returnSpecial($specialsQueued), 'active' => $this->returnSpecial($specialsActive), 'declined' => $this->returnSpecial($specialsDeclined), 'expired' => $this->returnSpecial($specialsExpired)), JSON_UNESCAPED_SLASHES);
        }

        header('Content-type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-Length:' . mb_strlen($data));
        echo $data;
        exit;
        //return response()->json(array('all' => $this->returnSpecial($specialsAll), 'queued' => $this->returnSpecial($specialsQueued), 'active' => $this->returnSpecial($specialsActive), 'declined' => $this->returnSpecial($specialsDeclined), 'expired' => $this->returnSpecial($specialsExpired)), 200, [], JSON_UNESCAPED_UNICODE);

    }


    public function getSpecial($id){
        $special = Specials::findOrNew($id);

        if(!$special->object_id){
            return response()->json(array('success' => false, 'message' => 'special not found'));
        }
        if (!empty($special->image) && (strpos($special->image, 'http://') === false)) {
            $special->image = url('/').'/img/'.$special->image;
            $special->image320 = url('/').'/img/'.$special->image320;
        }
        if (!empty($special->store_logo) && (strpos($special->store_logo, 'http://') === false)) {
            $special->store_logo = url('/').'/img/'.$special->store_logo;
        }

        $special->id = $special->object_id;
        $special->createdAt = date('c', strtotime($special->created_at));
        $special->endsAt = date('c', strtotime($special->ends_at));
        $special->storeName = $special->store;
        $special->addres = $special->address;
        $special->website = $special->site;
        $special->validFor = (int)$special->valid_for; // for API
        $special->active = ($special->active) ? true : false;
        return response()->json($special, 200, [], JSON_UNESCAPED_UNICODE);
    }


    public function importSpecials(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'specials' => 'required|array',
        ]);


        if($validator->fails()){
            return $this->returnJSON(array('error' => $validator->errors()->first(), 'status' => 'error'));
        }

        $success = array();
        $specials = $request->input('specials');
        foreach($specials as $special){

            $validator = Validator::make($special, [
                'name' => 'required|string|max:26',
                'description' => 'required|string|max:110',
                'address' => 'required|string',
                'phone' => 'required|string',
                'logo_bg' => 'required|string',
                //'let_admin_choose_image' => 'required|boolean',
                'batch_id' => 'required|string|max:16',
                'token_id' => 'required|string|max:16',
                'phone' => 'required|string',
                'website' => 'required|url',
            ]);

            if($validator->fails()){
                return $this->returnJSON(array('error' => $validator->errors()->first(), 'status' => 'error'));
            }

            $batch = Batch::findOrNew($special['batch_id']);

            if(!$batch->object_id){
                return $this->returnJSON(array('error' => 'batch not found', 'status' => 'error'));
            }

            $tokens = Token::query()->where('batch', '=', $batch->object_id)->where('object_id', '=', $special['token_id'])->get();

            if(!$tokens->count()){
                return $this->returnJSON(array('error' => 'token lookap failed', 'status' => 'error'));
            }

            $specialsNotDeclined = Specials::query()->where('status', '!=', 2)->where('token', '=', $tokens[0]->object_id)->get();

            if($specialsNotDeclined->count()){
                return $this->returnJSON(array('error' => 'special lookap failed', 'status' => 'error'));
            }

            $data = array();

            if ($special['valid_for'] < 1) $special['valid_for'] = 1;
            if ($special['valid_for'] > 7) {
                if (!in_array($special['valid_for'], array(14, 21, 30))) $special['valid_for'] = 7;
            }

            if(isset($special['image'])){
                $name = $this->generateName();
                $path = '../public/img/'.$name;
                //Image::configure(array('driver' => 'imagick'));
                Image::make($special['image'])->encode('jpg', 80)->resize(600, 600)->save($path.'-600.jpg');
                Image::make($special['image'])->encode('jpg', 80)->resize(320, 320)->save($path.'-320.jpg');
                Image::make($special['image'])->encode('jpg', 80)->resize(100, 100)->save($path.'-100.jpg');

                $data['image'] = $name.'-600.jpg';
                $data['image600'] = $name.'-600.jpg';
                $data['image320'] = $name.'-320.jpg';
                $data['image100'] = $name.'-100.jpg';
            }

            if(isset($special['logo'])){
                $name = $this->generateName().'-store_logo.jpg';
                Image::make($special['logo'])->encode('jpg', 80)->save('../public/img/'.$name);

                $data['store_logo'] = $name;
            }



            $data['store_logo_bg'] = $special['logo_bg'];
            $data['object_id'] = $this->generateId();
            $data['name'] = $special['name'];
            $data['description'] = str_replace(PHP_EOL, ' ', $special['description']);
            $data['store'] = $special['store'];
            $data['address'] = $special['address'];
            $data['site'] = $special['website'];
            $data['phone'] = $special['phone'];
            $data['batch'] = $batch->object_id;
            $data['token'] = $tokens[0]->object_id;

            $data['status'] = (isset($special['status'])? intval($special['status']) : 1);
            $data['active'] = 1;
            $data['valid_for'] = $special['valid_for'];
            $data['country'] = $special['country'];
            $data['country_code'] = $special['country_code'];
            $data['batch'] = $batch->object_id;
            $data['token'] = $tokens[0]->object_id;
            $data['manager_country'] = $tokens[0]->object_id;
            $data['let_admin_choose_image'] = 0;
            $data['location_latitude'] = floatval($special['latitude']);
            $data['location_longitude'] = floatval($special['longitude']);
            $data['ends_at'] = date("c", time() + 365 /* one year, updates on special approving */ * 24 * 60 * 60);

            Specials::create($data);
            $success[] = $data['token'];

        }

        return $this->returnJSON(array('errors' => array(), 'success' => $success));
        exit;

    }


    //=========================PUBLIC PART ==============================//
    public function addSpecial(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'name' => 'required',
            'description' => 'required|max:110',
            'store' => 'required',
            'address' => 'required',
            'website' => 'url',
            'phone' => 'required',
            'valid_for' => 'required',
            'batch_id' => 'required',
            'token_id' => 'required',
        ]);

        if($validator->fails()){
            return response()->json(array('error' => $validator->errors()->first(), 'status' => 'error'));
        }

        $batch = Batch::findOrNew($request->input('batch_id'));
        if (!$batch->object_id || !$batch->active) {
            return response()->json(array('success' => false, 'status' => 'error', 'error' => 'Batch not found or inactive'));
        }

        $token = Token::findOrNew($request->input('token_id'));
        if (!$token->object_id) {
            return response()->json(array('success' => false, 'status' => 'error', 'error' => 'Token not found'));
        }

        $specials = Specials::query()->where('status', '!=', 2)->where('token', '=', $token->object_id)->get();

        if($specials->count()){
            return response()->json(array('success' => false, 'status' => 'error', 'error' => 'Token already used'));
        }

        $valid_for = $request->input('valid_for');
        if($valid_for < 1) $valid_for = 1;
        if ($valid_for > 7 && !in_array($valid_for, array(14, 21, 30))) $valid_for = 7;

        $data = array();
        if($request->input('reuse_id')){
            $old_special = Specials::findOrNew($request->input('reuse_id'));
            if($old_special->object_id){
                if($request->input('image')){  //use old images
                    $data['image'] = $old_special->image;
                    $data['image600'] = $old_special->image600;
                    $data['image320'] = $old_special->image320;
                    $data['image100'] = $old_special->image100;
                }
                if($request->input('store_logo')){
                    $data['store_logo'] = $old_special->store_logo;
                }
            }
        }

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if($request->file('image')->getClientSize() > 1024*1024*4) return response()->json(array('error' => 'image_too_big', 'status' => 'error'));

            $name = $this->generateName();
            $path = '../public/img/'.$name;
            //Image::configure(array('driver' => 'imagick'));
            Image::make($request->file('image')->getRealPath())->encode('jpg', 80)->resize(600, 600)->save($path.'-600.jpg');
            Image::make($request->file('image')->getRealPath())->encode('jpg', 80)->resize(320, 320)->save($path.'-320.jpg');
            Image::make($request->file('image')->getRealPath())->encode('jpg', 80)->resize(100, 100)->save($path.'-100.jpg');

            $data['image'] = $name.'-600.jpg';
            $data['image600'] = $name.'-600.jpg';
            $data['image320'] = $name.'-320.jpg';
            $data['image100'] = $name.'-100.jpg';
        }

        if($request->hasFile('store_logo') && $request->file('store_logo')->isValid()){
            $name = $this->generateName().'-store_logo.jpg';
            $path = '../public/img/'.$name;
            Image::make($request->file('store_logo')->getRealPath())->encode('jpg', 80)->save($path);

            $data['store_logo'] = $name;
        }

        $data['object_id'] = $this->generateId();
        $data['store_logo_bg'] = $request->input('store_logo_bg');
        $data['name'] = $request->input('name');
        $data['description'] = str_replace(PHP_EOL, ' ', $request->input('description'));
        $data['store'] = $request->input('store');
        $data['address'] = $request->input('address');
        $data['site'] = $request->input('website');
        $data['phone'] = $request->input('phone');
        $data['status'] = 0;
        $data['active'] = 1;
        $data['valid_for'] = $valid_for;
        $data['country'] = $request->input('country');
        $data['country_code'] = $request->input('country_code');
        $data['batch'] = $batch->object_id;
        $data['token'] = $token->object_id;
        $data['manager_country'] = $token->country;
        $data['let_admin_choose_image'] = 0;
        $data['location_latitude'] = floatval($_POST['latitude']);
        $data['location_longitude'] = floatval($_POST['longitude']);
        $data['ends_at'] = date("c", time() + 365 /* one year, updates on special approving */ * 24 * 60 * 60);

        Specials::create($data);

        if (isset($_POST['urgent']) && $_POST['urgent'] && $_POST['urgent'] != 'false') {

            $message = "A special marked as urgent was added to the system:<br/><br/>
            {$_POST['name']} (" .$data['object_id'] . ")<br/>
            {$_POST['store']}<br/><br/><br/>
            http://client.stinjee.com/private#!/special/" .$data['object_id'];
            //TODO: add email sending
            //sendMessage('support@stinjee.com', 'Urgent Special For Approval', $message);
        }

        return response()->json(array('id' => $data['object_id'], 'status' => 'success'));
    }



    public function getBatchSpecials(Request $request, $id, $type = null)
    {

        $id = trim($id);
        $batch = Batch::findOrNew($id);
        if (!$batch->object_id) {
            echo 'batch not found';
            exit();
        }

        $tokens = Token::query()->where('batch', '=', $id)->get();
        $limit = ($request->input('limit')) ? $request->input('limit') : 20;
        $offset = ($request->input('offset')) ? $request->input('offset') : 0;

        if(!$tokens->count()){
            $data = json_encode(array('success' => false, 'error' => 'tokens lookup failed'));
            header('Content-type: application/json');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Content-Length:' . mb_strlen($data));
            echo $data;
            exit;
        }

        $specialsAll = Specials::query()->where('batch', '=', $id)->orderBy('created_at', 'desc')->skip($offset)->take($limit)->get();
        $specialsActive = Specials::query()->where('batch', '=', $id)->where('status', '=', 1)->where('ends_at', '>', date("Y-m-d H:i:s"))->orderBy('created_at', 'desc')->skip($offset)->take($limit)->get();
        $specialsQueued = Specials::query()->where('batch', '=', $id)->where('status', '=', 0)->orderBy('created_at', 'desc')->skip($offset)->take($limit)->get();
        $specialsDeclined = Specials::query()->where('batch', '=', $id)->where('status', '=', 2)->orderBy('created_at', 'desc')->skip($offset)->take($limit)->get();
        $specialsExpired = Specials::query()->where('batch', '=', $id)->where('ends_at', '<', date("Y-m-d H:i:s"))->orderBy('created_at', 'desc')->skip($offset)->take($limit)->get();

        $specialsNotDeclined = Specials::query()->where('batch', '=', $id)->where('status', '!=', 2)->get();

        $specials_array = array(
            'all' => $this->returnSpecial($specialsAll),
            'queued' => $this->returnSpecial($specialsQueued),
            'active' => $this->returnSpecial($specialsActive),
            'declined' => $this->returnSpecial($specialsDeclined),
            'expired' => $this->returnSpecial($specialsExpired),
        );

        $data = json_encode(array('success' => true, 'id' => $id, 'specials' => $specials_array, 'tokensNum' => $tokens->count(), 'specialsNum' => $specialsNotDeclined->count()));
        header('Content-type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-Length:' . mb_strlen($data));
        echo $data;
        exit;
        //array('success' => true, 'id' => $id, 'specials' => $specials_array, 'tokensNum' => $tokens->count(), 'specialsNum' => $specialsAll->count())
    }


    public function getBatchSpecialsByType(Request $request, $id, $type = null)
    {
        $batch = Batch::findOrNew($id);
        if (!$batch->object_id) {
            return response()->json(array('success' => false, 'error' => 'Batch not found'));
        }

        $limit = ($request->input('limit')) ? $request->input('limit') : 20;
        $offset = ($request->input('offset')) ? $request->input('offset') : 0;

        $specials = array();
        if($type == 'all') $specials = Specials::query()->where('batch', '=', $id)->orderBy('created_at', 'desc')->skip($offset)->take($limit)->get();
        if($type == 'active') $specials = Specials::query()->where('batch', '=', $id)->where('status', '=', 1)->where('ends_at', '>', date("Y-m-d H:i:s"))->orderBy('created_at', 'desc')->skip($offset)->take($limit)->get();
        if($type == 'queued') $specials =  Specials::query()->where('batch', '=', $id)->where('status', '=', 0)->orderBy('created_at', 'desc')->skip($offset)->take($limit)->get();
        if($type == 'declined') $specials = Specials::query()->where('batch', '=', $id)->where('status', '=', 2)->orderBy('created_at', 'desc')->skip($offset)->take($limit)->get();
        if($type == 'expired') $specials = Specials::query()->where('batch', '=', $id)->where('ends_at', '<', date("Y-m-d H:i:s"))->orderBy('created_at', 'desc')->skip($offset)->take($limit)->get();


        $data = json_encode($this->returnSpecial($specials));
        header('Content-type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-Length:' . mb_strlen($data));
        echo $data;
        exit;
        //array('success' => true, 'id' => $id, 'specials' => $specials_array, 'tokensNum' => $tokens->count(), 'specialsNum' => $specialsAll->count())
    }
}