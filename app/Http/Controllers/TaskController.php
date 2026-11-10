<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TaskController extends Controller
{

    private function getTasks()
    {
        // ถ้าใน Session ยังไม่มี ให้เริ่มด้วย Default 4 รายการนี้
        if (!session()->has('tasks')) {
            session(['tasks' => [
                ['id' => 1 , 'title' => 'ธนโชติ', 'desc' => 'ของปลอม fake'],
                ['id' => 2 , 'title' => 'ศุภกรณ์', 'desc' => 'เหลือง'],
                ['id' => 3 , 'title' => 'task 3', 'desc' => 'แดง'],
                ['id' => 4 , 'title' => 'task 4', 'desc' => 'blue'],
            ]]);
        }
        return session('tasks');
    }

    private function saveTasks($tasks)
    {
        session(['tasks' => $tasks]);
    }
    /**
     * Display a listing of the resource.
     */
     public function index()
    {
        return response()->json($this->getTasks());
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
        // 1. Validate ข้อมูลที่ส่งมา (Optional แต่แนะนำ)
        $request->validate([
            'title' => 'required|string',
            'desc'  => 'required|string',
        ]);

        // 2. ดึงข้อมูลเดิมออกมา
        $tasks = $this->getTasks();

        // 3. สร้าง ID ใหม่ (หา ID ล่าสุด + 1)
        $maxId = 0;
        foreach ($tasks as $task) {
            if ($task['id'] > $maxId) {
                $maxId = $task['id'];
            }
        }
        $newId = $maxId + 1;

        // 4. ประกอบร่างข้อมูลใหม่
        $newTask = [
            'id'    => $newId,
            'title' => $request->title,
            'desc'  => $request->desc,
        ];

        // 5. นำไปต่อท้าย Array เดิม
        $tasks[] = $newTask;

        // 6. บันทึกกลับเข้าไป
        $this->saveTasks($tasks);

        // 7. ตอบกลับเป็น JSON
        return response()->json([
            'message' => 'Task created successfully',
            'data'    => $newTask
        ], 201); // 201 คือ Status Code ของการสร้างสำเร็จ
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tasks = collect($this->getTasks())->firstWhere('id', (int)$id);
        if ($tasks) {
            return response()->json($tasks);
        } else {
            return response()->json(['message' => 'Task Not Found '], 404);
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
        $tasks = $this->getTasks();
        $found = false;
        $newTasks = [];

        // วนลูปหาข้อมูล ถ้า ID ไม่ตรงกับที่ส่งมาให้เก็บไว้
        foreach ($tasks as $task) {
            if ($task['id'] == (int)$id) {
                $found = true; // เจอข้อมูลที่จะลบ
            } else {
                $newTasks[] = $task; // ข้อมูลอื่นเก็บไว้
            }
        }

        // ถ้าหาไม่เจอ
        if (!$found) {
            return response()->json(['message' => 'Task not found pls re-check id'], 404);
        }

        // ถ้าเจอให้บันทึก Array ใหม่ที่ถูกลบออกไปแล้ว
        $this->saveTasks($newTasks);

        return response()->json(['message' => 'Task deleted successfully']);
    }
}