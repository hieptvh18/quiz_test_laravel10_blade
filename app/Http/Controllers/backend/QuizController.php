<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Subject;
use App\Models\Quiz;
use App\Models\Answer;
use App\Models\Question;
use App\Models\StudentQuiz;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{

    // manage
    public function resultExam($id){
        // get data
        $quizName = Quiz::where('id',$id)->first();
        $quizs = StudentQuiz::select('users.name as fullname','users.email','student_quizs.score','student_quizs.start_time as start_time_exam','student_quizs.end_time as end_time_exam','quizs.name')
                ->join('quizs','student_quizs.quiz_id','quizs.id')
                ->join('users','users.id','student_quizs.student_id')
                ->join('student_quiz_detail','student_quiz_detail.student_quiz_id','student_quizs.id')
                ->where('student_quizs.quiz_id',$id)->distinct()->get();
        return view('backend.exam.list-result',compact('quizName','quizs'));
    }

    // list
    public function list(Request $rq){
        // get data
        $idUser = $rq->session()->get('teacher');
        $listQuiz = Quiz::select('subjects.name as sbj_name','quizs.*')
                            ->join('subjects','subjects.id','=','quizs.subject_id')
                            ->where('quizs.author_id',$idUser->id)
                            ->get();

        return view('backend.quizs.list-quiz',compact('listQuiz'));
    }


    //add quiz
    public function create(Request $rq){
        // get data
        try{
            $listSubject = Subject::all();
            if($rq->isMethod('post')){
                $rq->validate([
                    'name'=>'required|max:100|unique:quizs',
                    'subject_id'=>'required',
                    'duration_minutes'=>'required',
                    'start_time'=>'required',
                    'end_time'=>'required',
                    'author_id'=>"nullable|integer|exists:users"
                ]); 
    
                $quizModel = new Quiz();
                $quizModel->fill($rq->all());
                $quizModel->author_id = session('teacher')->id;
                $save = $quizModel->save();
    
                if($save){
                    return redirect(route('admin.quiz.edit',['id'=>$quizModel->id]))->with('msg','Thêm thành công 1 quiz mới!');
                }
                return back()->with('fail','Thêm thất bại, vui lòng thử lại!');
            }
        }catch(\Throwable $th){
            dd($th->getMessage());
        }

        return view('backend.quizs.create',compact('listSubject'));
    }

    // edit
    public function edit(Request $rq,$id)
    {
        // get dtaaa
        $myQuiz = Quiz::find($id);
        $quizId  = $id;
        $listSubject = Subject::all();
        $listQues = Question::select('questions.*')->where('quiz_id',$id)->get();

        if($rq->isMethod('post')){

            // dd($rq->input());
            $rq->validate([
                'name'=>'required|max:100',
                'duration_minutes'=>'required',
                'start_time'=>'required',
                'end_time'=>'required',
            ]); 

            $quizModel = Quiz::find($id);
            $quizModel->fill($rq->all());
            $save = $quizModel->save(); 

            if($save){
                return back()->with('msg','Sửa thành công 1 quiz mới!');
            }
            return back()->with('fail','Sửa thất bại, vui lòng thử lại!');
        }

        return view('backend.quizs.edit',compact('myQuiz','listSubject','listQues','quizId'));
    }

    // remove
    public function remove($id)
    {
        if(Quiz::find($id)){

            // xóa question and answer của quiz
            $myQues = Quiz::select('questions.*')
                                ->join('questions','questions.quiz_id','=','quizs.id')
                                ->where('quizs.id',$id)
                                ->get();
            $myAns = Quiz::select('answers.*')
                                ->join('questions','questions.quiz_id','=','quizs.id')
                                ->join('answers','questions.id','=','answers.id')
                                ->where('quizs.id',$id)
                                ->get();
            foreach($myQues as $item){
                Question::destroy($item->id);
            }
            foreach($myAns as $item){
                Answer::destroy($item->id);
            }

            Quiz::destroy($id);

            return back()->with('msg','Xóa thành công 1 bộ quiz');

        }else{
            return back()->with('fail','Quiz không tồn tại');
        }
    }

 
   
}
