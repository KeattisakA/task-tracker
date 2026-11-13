<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $project = Project::with(['tasks'])
            ->when($request->search, fn($q, $search) => $q->where ('name', 'like',"%{$search}%")->paginate(20));
        return ProjectResource::collection($project);
    }

    public function store(StoreProjectRequest $request)
    {
        $project = Project::create($request->validated());
        return response()->json($project, 201);
    }

    public function show(Project $project)
    {
        $project->load(['tasks']);
        return new ProjectResource($project);
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->validated());
        return response()->json($project);
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return response()->json(['message' => 'Project deleted successfully']); 
    }
}