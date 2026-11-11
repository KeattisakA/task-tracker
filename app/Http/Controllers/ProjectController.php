<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
     public function index()
    {
        $projects = Project::with('tasks')->get();
        return response()->json($projects);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);
        $project = Project::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ]);
        return response()->json($project, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $project = Project::with('tasks')->find($id);
        if (!$project){
            return response()->json(['messege' => 'project not found'], 404);
        }
        return response()->json($project);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string'
        ]);
        $project = Project::find($id);
        if (!$project) {
            return response()->json(['messege' => 'project not found'], 404);
        }

        $project->name = $request->input('name');
        $project->description = $request->input('description');
        $project->save();
        return response()->json($project);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $project = Project::Find($id);
        if (!$project) {
            return response()->json(['messege' => 'project not found'], 404);
        }
        $project->delete();
        return response()->json(['messege' => 'delete successfully']);
    }
}