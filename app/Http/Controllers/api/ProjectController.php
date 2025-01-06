<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function store(Request $request){
        try{
            $request->validate([
                'name' => 'required',
                'features' => 'required'
            ]);
            $features = new Project();
            $features->name = $request->name;
            $features->features = $request->features;
            $features->save();
            return response()->json([
                'message' => 'Project created successfully',
                'data' => $features
            ], 200);
        } catch (\Exception $e){
            return response()->json([
                'message' => 'Failed to create project',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function index(){
        try{
            $projects = Project::all();
            return response()->json([
                'message' => 'Projects fetched successfully',
                'data' => $projects
            ], 200);
        } catch (\Exception $e){
            return response()->json([
                'message' => 'Failed to fetch projects',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function show($id){
        try{
            $project = Project::find($id);
            return response()->json([
                'message' => 'Project fetched successfully',
                'data' => $project
            ], 200);
        } catch (\Exception $e){
            return response()->json([
                'message' => 'Failed to fetch project',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy($id){
        try{
            $project = Project::find($id);
            $project->delete();
            return response()->json([
                'message' => 'Project deleted successfully'
            ], 200);
        } catch (\Exception $e){
            return response()->json([
                'message' => 'Failed to delete project',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
