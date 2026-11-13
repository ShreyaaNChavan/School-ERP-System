<?php
namespace App\Http\Controllers;

use App\Services\CaspaAIService;
use App\Services\CaspaUserContextService;
use App\Services\CaspaConversationService;
use Illuminate\Http\Request;

class CaspaStudentLearningController extends Controller
{
    public function index(CaspaUserContextService $context,CaspaConversationService $conversations)
    {
        $profile=$context->assertScreenAccess('student-learning'); $history=$conversations->history('student-learning');
        return view('caspa-student-learning.index',compact('profile','history'));
    }
    public function ask(Request $request,CaspaAIService $ai,CaspaUserContextService $context,CaspaConversationService $conversations)
    {
        $data=$request->validate(['message'=>'required|string|max:8000','conversation_id'=>'nullable|integer']);
        $profile=$context->assertScreenAccess('student-learning');
        $conversation=!empty($data['conversation_id'])?$conversations->ownedConversation((int)$data['conversation_id'],'student-learning'):null;
        abort_if(!empty($data['conversation_id'])&&!$conversation,404);
        $id=$conversation->id??$conversations->create('student-learning',mb_substr($data['message'],0,70));
        $conversations->addMessage($id,'user',$data['message']);
        $prompt="You are CASPA Student Learning, a patient educational tutor. Explain concepts clearly, use examples, ask short check questions when helpful. Do not claim to know student records unless present in authenticated context. User request: ".$data['message'];
        $response=$ai->respond($prompt,'student',['caspa_screen'=>'student-learning','authenticated_context'=>$profile]);
        $answer=$response['text']??$response['response']??'I could not generate a response. Please try again.';
        $conversations->addMessage($id,'assistant',$answer,['model'=>$response['model']??null]);
        return response()->json(['conversation_id'=>$id,'answer'=>$answer]);
    }
    public function conversation(int $id,CaspaConversationService $conversations,CaspaUserContextService $context)
    {
        $context->assertScreenAccess('student-learning');$conversation=$conversations->ownedConversation($id,'student-learning');abort_unless($conversation,404);
        return view('caspa-student-learning.conversation',['conversation'=>$conversation,'messages'=>$conversations->messages($id)]);
    }
}
