<?php
namespace App\Http\Controllers;

use App\Services\CaspaAIService;
use App\Services\CaspaUserContextService;
use App\Services\CaspaConversationService;
use Illuminate\Http\Request;

class CaspaPrincipalIntelligenceController extends Controller
{
    public function index(CaspaUserContextService $context,CaspaConversationService $conversations)
    {
        $profile=$context->assertScreenAccess('principal-intelligence');$history=$conversations->history('principal-intelligence');
        return view('caspa-principal-intelligence.index',compact('profile','history'));
    }
    public function ask(Request $request,CaspaAIService $ai,CaspaUserContextService $context,CaspaConversationService $conversations)
    {
        $data=$request->validate(['message'=>'required|string|max:8000','conversation_id'=>'nullable|integer']);
        $profile=$context->assertScreenAccess('principal-intelligence');
        $conversation=!empty($data['conversation_id'])?$conversations->ownedConversation((int)$data['conversation_id'],'principal-intelligence'):null;
        abort_if(!empty($data['conversation_id'])&&!$conversation,404);
        $id=$conversation->id??$conversations->create('principal-intelligence',mb_substr($data['message'],0,70));
        $conversations->addMessage($id,'user',$data['message']);
        $prompt="You are CASPA Principal Intelligence. Support school leadership with cautious, evidence-based analysis, operational summaries and improvement suggestions. Use only supplied aggregate school context; do not identify students or invent metrics. Request: ".$data['message'];
        $response=$ai->respond($prompt,'principal',['caspa_screen'=>'principal-intelligence','authenticated_context'=>$profile]);
        $answer=$response['text']??$response['response']??'I could not generate a response. Please try again.';
        $conversations->addMessage($id,'assistant',$answer,['model'=>$response['model']??null]);
        return response()->json(['conversation_id'=>$id,'answer'=>$answer]);
    }
    public function conversation(int $id,CaspaConversationService $conversations,CaspaUserContextService $context)
    {
        $context->assertScreenAccess('principal-intelligence');$conversation=$conversations->ownedConversation($id,'principal-intelligence');abort_unless($conversation,404);
        return view('caspa-principal-intelligence.conversation',['conversation'=>$conversation,'messages'=>$conversations->messages($id)]);
    }
}
