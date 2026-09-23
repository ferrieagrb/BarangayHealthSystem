<?php

namespace App\Http\Controllers;

use App\Models\Purok;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminSettings extends Controller
{
    public function index()
    {
        return view('admin.admin_settings');
    }
    public function demographicsIndex()
    {
        $puroks=purok::all();
        return view('admin.demographics',compact('puroks'));
    }
    public function storePurok(Request $request)
    {
        $request->validate(['name'=>'required|string|max:255|unique:puroks,name']);
        Purok::create(['name'=>$request->name]);

        return back()->with('success','Purok added successfully!');
    }
    public function destroyPurok($id)
    {
        Purok::findOrFail($id)->delete();

        return back()->with('success','Purok deleted successfully!');
    }
}
