<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Return view('admin.layout.news',[
            'title' => 'My Post In News',
            'posts' => News::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Return view('admin.layout.createnews',[
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => ['required','max:255'],
            'link' => ['required','unique:news'],
            'description' => ['required']
        ]);

        $validatedData['penulist'] = auth()->user()->name;

        News::create($validatedData);
        return redirect('/admin/news');
    }

    /**
     * Display the specified resource.
     */
    public function show(News $news)
    {
        // return View ('admin.layout.englishshow',[
        //     'english' => $english
        // ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(News $news)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, News $news)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(News $news)
    {
        //
    }
}
