<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tags;

class TagsController extends Controller
{
    function getTags(Request $request){
        $tags = Tags::getTags();

        return response()->json($tags);
    }

    function addTag(Request $request)
{
        $tag = $request->input();
        $response = Tags::addTag($tag);
        return response()->json($response);
}
    function deleteTag(Request $request, $id){
        return response()->json(Tags::deleteTag($id));
    }

   
}
