<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Articles;
use Carbon\Carbon;
use App\User;
use Auth;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $auth = User::where('id', Auth::id())->with('access_levels.info')->first();

        $isAdmin = $auth->access_levels && $auth->access_levels->first()->info && $auth->access_levels->first()->info->code === 'admin' ? true : false;

        return view('articles.index', compact('isAdmin'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('articles.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'subtitle' => 'required',
            'content' => 'required'
        ]);

        $article = new Articles;
        $article->title = $request->title;
        $article->subtitle = $request->subtitle;
        $article->content = $request->content;
        $article->published_at = isset($request->publish) && $request->publish ? Carbon::now() : null;
        $article->save();

        return response()->json($article);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $auth = User::where('id', Auth::id())->with('access_levels.info')->first();

        $isAdmin = $auth->access_levels && $auth->access_levels->first()->info && $auth->access_levels->first()->info->code === 'admin' ? true : false;
        
        if($id === 'all') {
            $articles = $this->fetchAll($isAdmin);

            return response()->json($articles);
        }

        $article = Articles::find($id);

        return view('articles.show', compact('article'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $article = Articles::find($id);
        
        return view('articles.edit')->with(['article' => $article]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'subtitle' => 'required',
            'content' => 'required'
        ]);

        $article = Articles::find($id);
        $article->title = $request->title;
        $article->subtitle = $request->subtitle;
        $article->content = $request->content;
        $article->published_at = isset($request->publish) && $request->publish ? Carbon::now() : null;
        $article->save();

        return response()->json($article);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $article = Articles::find($id);

        $article->delete();

        return response()->json($article);
    }

    public function fetchAll($admin) {
        $articles = [];

        if($admin) {
            $articles = Articles::all();
        } else {
            $articles = Articles::whereNotNull('published_at')->get();
        }

        return $articles;
    }

    public function view(Request $request) {
        return $request->all();
    }
}
