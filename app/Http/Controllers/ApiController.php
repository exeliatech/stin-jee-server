<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Validator;
use App\Batch;
use App\Device;
use App\Http\Controllers\Controller;
use App\Specials;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class ApiController extends Controller{

    public function getSpecialsByLonglat(Request $request){
        $validator = Validator::make($request->json()->all(), [

            'latitude' => 'required',
            'longitude' => 'required',
            'type' => 'in:'.implode(',', \App\Types::GeyKeys())
        ]);

        $type = $request->json('type');

        if($validator->fails()){
            return response()->json(array('error' => $validator->errors()->first(), 'status' => 'error'));
        }
        $specials_array = array();

        // update or create device in database
        if ($request->json('device_id') || $request->json('device_type') === 2){
            $devices = Device::query()->where('device_id', '=' , $request->json('device_id'))->get();

            if($devices->count()){
                foreach($devices as $device){
                    if($request->json('device_token')) $device->device_token = $request->json('device_token');
                    $device->last_visit_date = date("Y-m-d H:i:s");
                    $device->location_latitude = $request->json('latitude');
                    $device->location_longitude = $request->json('longitude');

                    $device->save();
                }
            } else{
                $data = $request->json()->all();
                $data['last_visit_date'] = date("Y-m-d H:i:s");
                $data['object_id'] = $this->generateId();
                $device = Device::create($data);
            }
        }

        //select data

        $parse_limit = ($request->json('limit')) ?  $request->json('limit') : 20;
        $offset = ($request->json('offset')) ? $request->json('offset') : 0;

        $batches = Batch::query()->where('active', '=', 1)->get();

        if($batches->count()){
            $batches_ids = array();
            foreach($batches as $batch){
                $batches_ids[] =  $batch->object_id;
            }

            $specialsQuery = DB::table('specials')
                ->select(DB::raw('*, (6373 * acos(
                                                    cos(radians('.$request->json('latitude').')) * cos(radians(`location_latitude`)) *
                                                    cos(radians(`location_longitude`) - radians('.$request->json('longitude').')) +
                                                    sin(radians('.$request->json('latitude').')) * sin(radians(`location_latitude`)))
                                                 ) `distance`'))
                ->having('distance', '<', 100);

                if ($type) {
                    $specialsQuery->where('type', '=', \App\Types::fromString($type));
                }
                $specialsQuery
                ->where('ends_at', '>', date("Y-m-d H:i:s"))
                ->where('active', '=', 1)->where('status', '=', 1)
                ->whereIn('batch', $batches_ids)
                ->orderBy('distance', 'asc')
                ->skip($offset)
                ->take($parse_limit);

            $specials = $specialsQuery->get();


            //$specials = Specials::query()->whereIn('batch', $batches_ids)->where('ends_at', '>', date("Y-m-d H:i:s"))->where('active', '=', 1)->where('status', '=', 1)->skip($offset)->take($parse_limit)->get();

            if(count($specials)){
                foreach($specials as $special){
                    if (empty($special->image600) || strpos($special->image600,'http://') !== false) {
                        $domain = '';
                    } else {
                        $domain = url('/').'/img/';
                    }
                    $specials_array[] = array(
                        'id' => $special->object_id,
                        'name' => $special->name,
                        'longitude' => $special->location_longitude,
                        'latitude' => $special->location_latitude,
                        'type' => \App\Types::toString($special->type),
                        'image600' => $domain.$special->image600,
                        'image320' => $domain.$special->image320,
                        'image100' => $domain.$special->image100,
                    );
                }
            }
        }

        return response()->json($specials_array);
    }


    public function getSpecialsInfo(Request $request){
        if($request->json('specials_id')){
            $specials = Specials::query()->where('object_id', '=', $request->json('specials_id'))->take(1)->get();
            if($specials->count()) {
                $specials = $specials[0];
                $specials->views_count = $specials->views_count + 1;
                $specials->save();

                $updated_at = $specials->updated_at;
                if (empty($specials->image600) || strpos($specials->image600,'http://') !== false) {
                    $domain = '';
                } else {
                    $domain = url('/').'/img/';
                }
                if (empty($specials->store_logo) || strpos($specials->store_logo,'http://') !== false) {
                    $sdomain = '';
                } else {
                    $sdomain = url('/').'/img/';
                }
                $specials_array = array(
                    'id' => $specials->object_id,
                    'name' => $specials->name,
                    'site' => $specials->site,
                    'description' => $specials->description,
                    'image' => $domain.$specials->image,
                    'image600' => $domain.$specials->image600,
                    'image320' => $domain.$specials->image320,
                    'image100' => $domain.$specials->image100,
                    'status' => $specials->status,
                    'storeName' => $specials->store,
                    'address' => $specials->addres,
                    'phone' => $specials->phone,
                    'type' => \App\Types::toString($specials->type),
                    'source' => \App\Sources::toString($specials->source),
                    'action' => \App\Actions::toString($specials->action),
                    'country' => $specials->country,
                    'country_code' => $specials->country_code,
                    'store_logo' => $sdomain.$specials->store_logo,
                    'store_logo_bg' => $specials->store_logo_bg,
                    'site' => $specials->site,
                    'validFor' => $specials->validFor,
                    'letAdminChooseImage' => ($specials->let_admin_choose_image) ? true : false,
                    'active' => ($specials->active) ? true : false,
                    'longitude' => $specials->location_longitude,
                    'latitude' => $specials->location_latitude,
                    'endsAt' => $specials->ends_at,
                    'updatedAt' => date('Y-m-d H:i:s', strtotime($specials->updated_at)),
                );
                return response()->json($specials_array);
            }
        }

        return response()->json(array('success'=> false, 'message'=> 'specials not found'));
    }


    // for push notification
    public function subscribe(Request $request)
    {

        // update or create device in database
        if ($request->json('device_id') || $request->json('device_type') === 2){
            $devices = Device::query()->where('device_id', '=' ,$request->json('device_id'))->get();

            if($devices->count()){
                foreach($devices as $device){
                    if($request->json('device_token')) $device->device_token = $request->json('device_token');
                    $device->last_visit_date = date("Y-m-d H:i:s");
                    if($request->json('latitude')){
                        $device->location_latitude = $request->json('latitude');
                        $device->location_longitude = $request->json('longitude');
                    }

                    $device->save();
                }

                return response()->json(array('success'=> true));
            } else{
                $data = $request->json()->all();
                if($request->json('latitude')){
                    $data['location_latitude'] = $request->json("latitude");
                    $data['location_longitude'] = $request->json("longitude");
                }
                $data['last_visit_date'] = date("Y-m-d H:i:s");
                $data['object_id'] = $this->generateId();
                $device = Device::create($data);

                return response()->json(array('success'=> true));
            }
        } else {
            return response()->json(array('success'=> false, 'message'=> 'invalid token or device type'));
        }
   }


}