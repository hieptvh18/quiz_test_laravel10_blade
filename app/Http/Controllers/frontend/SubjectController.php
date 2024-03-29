<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;
use App\Models\Quizs;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    //detail

    public function detail($id){
        // get data
        // nếu tồn tại sp thì ms get ko thì rd
        if(DB::table('subjects')->where('id',$id)->exists()){
            $myQuiz = Quiz::select('quizs.*','subjects.author_id as author_id')
                    ->join('subjects','subjects.id','quizs.subject_id')
                    ->where('subject_id',$id)
                    ->where('status',1)->get();

            $quizTitle = DB::table('subjects')
                            ->select('name')
                            ->where('id',$id)
                            ->first();

        }else{
            // ko tồn tại
           return redirect(route('client.home'))->with('msg','Không tồn tại bài quiz');
        }


        return view('frontend.subjects.list-quiz',compact('myQuiz','quizTitle'));
    }

    // 
}
