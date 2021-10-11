<?php

namespace App\Http\Controllers;
use App\Models\Customer;
use App\Models\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function list()
    {
       // $request->admin_id = JWTAuth::user()->id;
       // dd(JWTAuth::user()->id);
        $customers = Customer::where(['user_id' => JWTAuth::user()->id])->with('user')->get();
        return response()->json(['customers' => $customers], 200);
    }

    public function create(Request $request)
    {
        if (!$request->hasFile('image')) {
            return response()->json(['upload_file_not_found'=>$request->image], 400);
        }
        $data = $request->only('name', 'email', 'profile_pict');
        //dd($request->all());
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',

        ]);

        //dd($request->hasFile('image'));

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        
        //Request is valid, create new Customer
        $request->user_id = JWTAuth::user()->id;
        //dd($request->user_id);
        $customer = Customer::create([
        	'name' => $request->name,
        	'email' => $request->email,
        	'user_id' => $request->user_id,
        ]);

        $image = $request->image;


        if ($image) {

            $imageName = time() . '.' . $request->image->extension();

            $image->move(public_path('images'), $imageName);
            //store image file into directory and db
            $customer->profile_pict = $imageName;
            $customer->save();
        }
        //customer created, return success response
        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
            'data' => $customer
        ], Response::HTTP_OK);
    }


    public function show($id)
    {
        $customer = Customer::findOrFail($id);
        return response()->json(['customer' => $customer], 200);
    }

    public function search(Request $request)
    {
        $customer = Customer::where(['user_id' => JWTAuth::user()->id])
        ->where(function ($query) use($request) {
            $query->where('name', 'ilike', '%' . $request->keyWord . '%')
               ->orWhere('email', 'ilike', '%' . $request->keyWord . '%');
          })->get();
        return response()->json(['customer' => $customer], 200);
    }


    public function update($id, Request $request)
    {
        $customer = Customer::findOrFail($id);
        $customer->update($request->all());

        $image = $request->image;


        if ($image) {

            $imageName = time() . '.' . $request->image->extension();

            $image->move(public_path('images'), $imageName);
            //$image = $request->image->store(public_path('images'));

            //$image->move($path, $file->getClientOriginalName());


            //store image file into directory and db
            $customer->profile_pict = $imageName;
            $customer->save();
        }
        
        return response()->json(['message' => 'customer updated successfully .','customer' => $customer], 200);
    }

    public function delete($id)
    {
        Customer::findOrFail($id)->delete();
        
        return response('customer Deleted Successfully', 200);
    }

    public function removeAll()
    {
        Customer::where('user_id',JWTAuth::user()->id)->delete();

        return response('all Customers has Deleted Successfully', 200);
    }

    public function test()
    {
        $users = User::with('customers')->get();
        foreach ($users as $user) {
   
           return $user->customers;
         }
        //$customers = Customer::where('module_id', $id)->get();
        return response()->json(['users' => $users], 200);
    }

}
