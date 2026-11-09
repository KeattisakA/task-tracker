<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TaskController extends Controller
{

    private $tasks = [
        ['id' => 1 , 'title' => 'task 1', 'desc' => ' desc for task 1'],
        ['id' => 2 , 'title' => 'task 2', 'desc' => ' desc for task 2'],
        ['id' => 3 , 'title' => 'task 3', 'desc' => ' desc for task 3'],
    ];
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json($this->tasks);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tasks = collect($this->tasks)->firstWhere('id', (int)$id);
        if ($tasks) {
            return response()->json($tasks);
        }else{
            return response()->json(['messege' => 'task not found'], 404);
        }
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}