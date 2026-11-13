<?php
namespace App\Http\Controllers;

use App\Services\CaspaAIService;
use App\Services\CaspaUserContextService;
use App\Services\CaspaConversationService;
use Illuminate\Http\Request;

class CaspaChatController extends Controller
{
    public function index(CaspaUserContextService $context,CaspaConversationService $conversations)
    {
        $profile=$context->assertScreenAccess('chat');
        $history=$conversations->history('chat');
        return view('caspa-chat.index',compact('profile','history'));
    }
    public function send(Request $request,CaspaAIService $ai,CaspaUserContextService $context,CaspaConversationService $conversations)
    {
        $data=$request->validate(['message'=>'required|string|max:8000','conversation_id'=>'nullable|integer']);
        $profile=$context->assertScreenAccess('chat');
        $conversation=!empty($data['conversation_id'])?$conversations->ownedConversation((int)$data['conversation_id'],'chat'):null;
        abort_if(!empty($data['conversation_id'])&&!$conversation,404);
        $id=$conversation->id??$conversations->create('chat',mb_substr($data['message'],0,70));
        $conversations->addMessage($id,'user',$data['message']);
        $response=$ai->respond($data['message'],'general',['caspa_screen'=>'chat','authenticated_context'=>$profile,'safety'=>'Use only supplied context. Never infer or disclose records not present in this context.']);
        $answer=$response['text']??$response['response']??'I could not generate a response. Please try again.';
        $conversations->addMessage($id,'assistant',$answer,['model'=>$response['model']??null]);
        return response()->json(['conversation_id'=>$id,'answer'=>$answer]);
    }
    public function conversation(int $id,CaspaConversationService $conversations,CaspaUserContextService $context)
    {
        $context->assertScreenAccess('chat'); $conversation=$conversations->ownedConversation($id,'chat'); abort_unless($conversation,404);
        return view('caspa-chat.conversation',['conversation'=>$conversation,'messages'=>$conversations->messages($id)]);
    }
}
