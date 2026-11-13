<?php
namespace App\Http\Controllers;

use App\Services\CaspaAIService;
use App\Services\CaspaUserContextService;
use App\Services\CaspaConversationService;
use Illuminate\Http\Request;

class CaspaTeacherAssistantController extends Controller
{
    public function index(CaspaUserContextService $context,CaspaConversationService $conversations)
    {
        $profile=$context->assertScreenAccess('teacher-assistant');$history=$conversations->history('teacher-assistant');
        return view('caspa-teacher-assistant.index',compact('profile','history'));
    }
    public function ask(Request $request,CaspaAIService $ai,CaspaUserContextService $context,CaspaConversationService $conversations)
    {
        $data=$request->validate(['message'=>'required|string|max:8000','conversation_id'=>'nullable|integer']);
        $profile=$context->assertScreenAccess('teacher-assistant');
        $conversation=!empty($data['conversation_id'])?$conversations->ownedConversation((int)$data['conversation_id'],'teacher-assistant'):null;
        abort_if(!empty($data['conversation_id'])&&!$conversation,404);
        $id=$conversation->id??$conversations->create('teacher-assistant',mb_substr($data['message'],0,70));
        $conversations->addMessage($id,'user',$data['message']);
        $prompt="You are CASPA Teacher Assistant. Help teachers prepare lesson plans, worksheets, question banks, rubrics and classroom activities. Be practical and age-appropriate. Do not invent school-specific facts. Request: ".$data['message'];
        $response=$ai->respond($prompt,'teacher',['caspa_screen'=>'teacher-assistant','authenticated_context'=>$profile]);
        $answer=$response['text']??$response['response']??'I could not generate a response. Please try again.';
        $conversations->addMessage($id,'assistant',$answer,['model'=>$response['model']??null]);
        return response()->json(['conversation_id'=>$id,'answer'=>$answer]);
    }
    public function conversation(int $id,CaspaConversationService $conversations,CaspaUserContextService $context)
    {
        $context->assertScreenAccess('teacher-assistant');$conversation=$conversations->ownedConversation($id,'teacher-assistant');abort_unless($conversation,404);
        return view('caspa-teacher-assistant.conversation',['conversation'=>$conversation,'messages'=>$conversations->messages($id)]);
    }
}
