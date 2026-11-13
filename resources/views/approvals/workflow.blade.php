@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page ac-page">
@include('approvals._style')
<div class="ac-head"><div><h1>Workflow Designer</h1><div class="ac-sub">Build approval paths with connected stages and decision routing</div></div><div class="ac-actions"><a class="ac-btn" href="{{ route('dashboard') }}">Back to Dashboard</a></div></div>
@if(session('success'))<div class="ac-panel" style="color:#166534">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ac-panel" style="color:#b42318">{{ $errors->first() }}</div>@endif
@include('approvals._nav')

<div class="ac-panel"><h3>Visual Workflow Builder</h3>
<p class="ac-muted">Arrange approval stages, edit node labels and save the workflow definition as a draft. Activation and automatic routing are not enabled in this first version.</p>
<div class="ac-flow" id="flowCanvas">
<div class="ac-node done" data-node="start"><strong>Request Submitted</strong><small>Start</small></div><div class="ac-arrow done"></div>
<div class="ac-node" data-node="department"><strong>Department Head</strong><small>Approval stage</small></div><div class="ac-arrow"></div>
<div class="ac-node active" data-node="principal"><strong>Principal</strong><small>Approval stage</small></div><div class="ac-arrow"></div>
<div class="ac-node wait" data-node="finance"><strong>Finance</strong><small>Approval stage</small></div><div class="ac-arrow"></div>
<div class="ac-node wait" data-node="end"><strong>Completed</strong><small>End</small></div>
</div>
<form method="POST" action="{{ route('approvals.workflow.save') }}">@csrf
<div class="ac-form"><div class="ac-field"><label>Workflow Name</label><input name="name" value="{{ old('name','Standard School Approval') }}" required></div><div class="ac-field"><label>Node labels (comma separated)</label><input id="nodeLabels" value="Request Submitted,Department Head,Principal,Finance,Completed"></div><input type="hidden" name="definition" id="definition"></div>
<button type="submit" class="ac-btn primary" style="margin-top:14px">Save Workflow Draft</button>
</form>
</div>
<script>
(function(){
const canvas=document.getElementById('flowCanvas'), labels=document.getElementById('nodeLabels'), hidden=document.getElementById('definition');
function sync(){
 const names=labels.value.split(',').map(x=>x.trim()).filter(Boolean);
 const nodes=Array.from(canvas.querySelectorAll('.ac-node'));
 nodes.forEach((n,i)=>{if(names[i]) n.querySelector('strong').textContent=names[i];});
 const nodeData=nodes.map((n,i)=>({id:n.dataset.node,label:n.querySelector('strong').textContent,type:i===0?'start':(i===nodes.length-1?'end':'approval')}));
 const edges=nodeData.slice(0,-1).map((n,i)=>({from:n.id,to:nodeData[i+1].id}));
 hidden.value=JSON.stringify({nodes:nodeData,edges:edges});
}
labels.addEventListener('input',sync);sync();
})();
</script>

</div>
@endsection
