<?php
namespace App\Http\Controllers\Teacher;
use App\Http\Controllers\Controller;
use App\Models\AssessmentScore;
use App\Models\AssessmentComponent;
use App\Models\ScoreRevision;
use App\Models\TeacherDescription;
use Illuminate\Http\Request;
class AssessmentController extends Controller
{
    public function index(){ return view('teacher.assessments.index'); }
    public function store(Request $r){
        $d=$r->validate(['student_id'=>'required','subject_id'=>'required','component_id'=>'required','class_id'=>'required','academic_year_id'=>'required','semester_id'=>'required','score'=>'required|numeric|min:0|max:100']);
        $exists=AssessmentScore::where('student_id',$d['student_id'])->where('subject_id',$d['subject_id'])->where('component_id',$d['component_id'])->where('academic_year_id',$d['academic_year_id'])->where('semester_id',$d['semester_id'])->first();
        if($exists && in_array($exists->status,['terkirim','dikunci'])) return back()->withErrors(['score'=>'locked']);
        $d['teacher_id']=1; $d['status']='draft';
        if($exists){ $exists->update(['score'=>$d['score']]); } else { AssessmentScore::create($d); }
        return back()->with('success','OK');
    }
    public function submit(Request $r){ AssessmentScore::where('status','draft')->update(['status'=>'terkirim']); return back()->with('success','submitted'); }
    public function requestRevision(Request $r){
        $d=$r->validate(['assessment_score_id'=>'required','reason'=>'required','new_score'=>'required|numeric']);
        $sc=AssessmentScore::findOrFail($d['assessment_score_id']);
        ScoreRevision::create(['assessment_score_id'=>$sc->id,'requested_by'=>1,'reason'=>$d['reason'],'old_score'=>$sc->score,'new_score'=>$d['new_score'],'status'=>'diajukan']);
        $sc->update(['status'=>'perlu_revisi']);
        return back()->with('success','revision requested');
    }
    public function approveRevision(Request $r){
        $d=$r->validate(['revision_id'=>'required','decision'=>'required|in:disetujui,ditolak']);
        $rev=ScoreRevision::findOrFail($d['revision_id']);
        $rev->update(['status'=>$d['decision'],'decided_by'=>1,'decided_at'=>now()]);
        $sc=AssessmentScore::findOrFail($rev->assessment_score_id);
        if($d['decision']==='disetujui'){ $sc->update(['score'=>$rev->new_score,'status'=>'terkirim']); } else { $sc->update(['status'=>'terkirim']); }
        return back()->with('success','decision saved');
    }
    public function recap(Request $r){
        $scores=AssessmentScore::with(['student','component'])->get();
        return view('teacher.assessments.recap',compact('scores'));
    }
    public function storeComponent(Request $r){
        $d=$r->validate(['name'=>'required','type'=>'required','weight'=>'required|numeric|min:0|max:100']);
        $total=(float)AssessmentComponent::sum('weight')+(float)$d['weight'];
        if($total>100) return back()->withErrors(['weight'=>'total weight exceeds 100']);
        AssessmentComponent::create($d+['is_active'=>true]);
        return back()->with('success','component created');
    }
    public function calculate(Request $r){
        $scores=AssessmentScore::with('component')->get()->groupBy(fn($s)=>$s->student_id.'-'.$s->subject_id);
        $results=[];
        foreach($scores as $k=>$group){
            $sum=0; $wsum=0; $has=false;
            foreach($group as $s){
                if($s->score===null) continue;
                $w=(float)($s->component->weight ?? 0);
                $sum += (float)$s->score * $w;
                $wsum += $w;
                $has=true;
            }
            if(!$has){ $results[$k]='not yet calculated'; continue; }
            $final = $wsum>0 ? round($sum/$wsum,2) : 0;
            $results[$k]=$final;
        }
        return view('teacher.assessments.calculate',compact('results'));
    }
    public function storeDescription(Request $r){
        $d=$r->validate(['student_id'=>'required','subject_id'=>'required','academic_year_id'=>'required','semester_id'=>'required','score'=>'required|numeric']);
        $desc = $d['score']>=75 ? 'Mencapai KKTP' : 'Perlu peningkatan';
        $d['description']=$desc; $d['teacher_id']=1;
        TeacherDescription::updateOrCreate(['student_id'=>$d['student_id'],'subject_id'=>$d['subject_id'],'academic_year_id'=>$d['academic_year_id'],'semester_id'=>$d['semester_id']],$d);
        return back()->with('success','description saved');
    }
}
